<?php

class CsvReader
{
    private DateNormalizer $dateNormalizer;

    public function __construct(DateNormalizer $dateNormalizer)
    {
        $this->dateNormalizer = $dateNormalizer;
    }

    // [projectId][employeeId]['from' => DateTimeImmutable, 'to' => DateTimeImmutable]
    public function read(string $filename): array
    {
        $handle = fopen($filename, 'r');
        if ($handle === false) {
            throw new RuntimeException('Error opening file: ' . $filename);
        }
        $projects = [];
        try {
            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if ($row === [null]) {
                    continue;
                }
                $row = array_map('trim', $row); // remove empty symbols before and after 
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);  // remove BOM

                if (     count($row) !== 4 
                      || !$this->isValidIdentifier($row[0]) 
                      || !$this->isValidIdentifier($row[1])
                    ) continue;

                try {
                    $start = $this->dateNormalizer->normalizeDate($row[2]);
                    $end = $this->dateNormalizer->normalizeDate($row[3], true);
                } catch (InvalidArgumentException $exception) {
                    continue;
                }
                if ($start > $end) {
                    continue;
                }
                $employeeId = (int)$row[0];
                $projectId = (int)$row[1];
                $projects[$projectId][$employeeId][] = ['from' => $start, 'to' => $end];
            }
        } finally {
            fclose($handle);
        }
        if (!$projects) {
            throw new InvalidArgumentException('NO valid data in the CSV file!');
        }

        ksort($projects);
        $newProjects = [];
        foreach ($projects as $projectId => $employees) {
            ksort($employees);
            foreach ($employees as $employeeId => $periods) {
                $newProjects[$projectId][$employeeId] =
                    $this->mergePeriods($periods);
            }
        }

        return $newProjects;;
    }

    private function isValidIdentifier(string $value): bool
    {
            if (!ctype_digit($value)) return false;            

            $digits = ltrim($value, '0');
            $limit = (string) PHP_INT_MAX; // 9223372036854775807

            if (strlen($digits) < strlen($limit)) return true;
            if (strlen($digits) > strlen($limit)) return false;
            if (strcmp($digits, $limit) <= 0) return true;
            return false;
    }

    private function mergePeriods(array $periods): array
    {
            usort($periods, function ($first, $second) {
                return $first['from'] <=> $second['from'];
            });
            $merged = [];
            foreach ($periods as $period) {
                if (empty($merged)) {
                    $merged[] = $period;
                    continue;
                }
                $last = array_key_last($merged);
                if ($period['from'] > $merged[$last]['to']) {
                    $merged[] = $period;
                    continue;
                }
                $merged[$last]['to'] = max(
                    $merged[$last]['to'],
                    $period['to']
                );
            }
            return $merged;
    }

    
}

