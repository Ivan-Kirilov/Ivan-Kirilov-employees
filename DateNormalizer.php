<?php

class DateNormalizer
{
    private const DATE_FORMATS = [
            'Ymd',     // 20261019
            'Y-m-d',   // 2026-10-19
            'Y/m/d',   // 2026/10/19
            'Y.m.d',   // 2026.10.19

            'd/m/Y',   // 19/10/2026
            'd-m-Y',   // 19-10-2026
            'd.m.Y',   // 19.10.2026
            'd/m/y',   // 19/10/26
            'd-m-y',   // 19-10-26
            'd.m.y',   // 19.10.26

            // do not use American formats
            // 'm/d/Y',   // 10/19/2026
            // 'm-d-Y',   // 10-19-2026
            // 'm/d/y',   // 10/19/26
            // 'm-d-y',   // 10-19-26

            'M j Y',   // Oct 19 2026
            'M j, Y',  // Oct 19, 2026
            'j M Y',   // 19 Oct 2026
            'j M, Y',  // 19 Oct, 2026

            'F j Y',   // October 19 2026
            'F j, Y',  // October 19, 2026
            'j F Y',   // 19 October 2026
            'j F, Y',  // 19 October, 2026
    ];

    private const TIME_ZONE = 'Europe/Sofia';


    public function normalizeDate(string $value, bool $allowToday = false): DateTimeImmutable
    {
        $timezone = new DateTimeZone(self::TIME_ZONE);
        $value = trim($value);

        //  Empty, NULL
        if ($value === '' || strtoupper($value) === 'NULL') {
            if (!$allowToday) {
                throw new InvalidArgumentException('Start date cannot be NULL.');
            }
            return new DateTimeImmutable('today', $timezone);
        }

        // Unix 
        if (ctype_digit($value) && in_array(strlen($value), [9, 10], true)) {
            return (new DateTimeImmutable('@' . $value))
                ->setTimezone($timezone)
                ->setTime(0, 0);
        }

        // Supported formats
        foreach (self::DATE_FORMATS as $format) {
            $date = DateTimeImmutable::createFromFormat(
                '!' . $format,
                $value,
                $timezone
            );
            if ($date === false) continue;
            $errors = DateTimeImmutable::getLastErrors();
            if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                continue;
            }
            return $date->setTime(0, 0);
        }

        // Unsupported / Invalid Date
        throw new InvalidArgumentException("Invalid date: {$value}");
    }

}

