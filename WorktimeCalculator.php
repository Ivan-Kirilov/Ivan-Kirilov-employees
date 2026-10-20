<?php

class WorktimeCalculator
{
    public function findLongestPair(array $projects): ?array
    {
        $pairs = [];
        $bestPairKey = '';
        $bestPairDays = 0;
        foreach ($projects as $projectId => $employees) {
            $employeeIds = array_keys($employees);
            $count = count($employeeIds);
            for ($i = 0; $i < $count - 1; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $firstId = min($employeeIds[$i], $employeeIds[$j]);
                    $secondId = max($employeeIds[$i], $employeeIds[$j]);
                    $days = $this->countCommonDays($employees[$firstId], $employees[$secondId]);
                    if ($days === 0) {
                        continue;
                    }
                    $key = "{$firstId}-{$secondId}";
                    if (!isset($pairs[$key])) {
                        $pairs[$key] = [
                            'emp1' => $firstId, 'emp2' => $secondId,
                            'totalDays' => 0, 'projects' => [],
                        ];
                    }
                    $pairs[$key]['projects'][$projectId] = $days;
                    $pairs[$key]['totalDays'] += $days;

                    if( $pairs[$key]['totalDays'] > $bestPairDays) {
                        $bestPairDays = $pairs[$key]['totalDays'];
                        $bestPairKey = $key;
                    }
                }
            }
        }
        return $pairs[$bestPairKey];
    }

    private function countCommonDays(array $firstPeriods, array $secondPeriods): int
    {
        $i = 0;
        $j = 0;
        $days = 0;
        $firstCount = count($firstPeriods);
        $secondCount = count($secondPeriods);
        while ($i < $firstCount && $j < $secondCount) {
            $first = $firstPeriods[$i];
            $second = $secondPeriods[$j];
            $start = max($first['from'], $second['from']);
            $end = min($first['to'], $second['to']);
            if ($start <= $end) {
                $days += (int) $start->diff($end)->days + 1;
            }
            if ($first['to'] < $second['to']) {
                $i++;
            } elseif ($second['to'] < $first['to']) {
                $j++;
            } else {
                $i++;
                $j++;
            }
        }
        return $days;
    }
}
