# Pair of Employees Who Have Worked Together
## Overview
A PHP application that identifies the pair of employees who have worked together on common projects for the longest total period.

The application accepts a CSV file containing employee IDs, project IDs, and employment periods. It calculates overlapping calendar days and identifies the pair with the highest total number of days worked together.

## Technologies
- PHP 8+
- HTML/CSS
- No external frameworks or database required

## Features
- CSV file upload through a web interface.
- Validation of input records and date formats.
- Support for multiple date formats, including Unix timestamps.
- Support for `NULL` in `DateTo`, interpreted as the current date.
- Merging overlapping employment periods for the same employee within the same project.
- Calculation of common working days between employee pairs.
- Aggregation of overlapping days across multiple projects.
- Display of the winning pair and a detailed results table.

## CSV Input Format
The CSV file must contain the following columns:
| EmpID | ProjectID | DateFrom | DateTo |
|---|---|---|---|
| 143 | 12 | 2013-11-01 | 2014-01-05 |
| 218 | 12 | 2013-12-01 | NULL |
| 143 | 10 | 2012-05-10 | 2013-05-20 |
`DateFrom` is required. An empty or `NULL` value in `DateTo` means the employee is still assigned to the project.

## How It Works
1. Read and validate the CSV records.
2. Normalize all dates into a consistent format.
3. Group employment periods by project and employee.
4. Merge overlapping periods belonging to the same employee on the same project to prevent duplicate day counting.
5. Compare unique employee pairs within each project.
6. Calculate overlapping calendar days, including both start and end dates.
7. Sum the overlapping days across shared projects.
8. Identify the pair with the highest total and display its project-level breakdown.

## Installation and Usage
1. Ensure PHP 8+ is installed.
2. Download or clone the repository.
3. Open a terminal in the project directory.
4. Start the built-in PHP web server:
```bash
php -S localhost:9000
```
5. Open `http://localhost:9000` in your browser.
6. Upload a CSV file and view the results.

## Result
The application displays the employee pair with the longest total collaboration period, together with a detailed table containing:
| Employee ID #1 | Employee ID #2 | Project ID | Days Worked |
|---|---|---|---|

## Notes
- Employment dates are treated as inclusive calendar-day intervals.
- Duplicate and overlapping periods are handled without double-counting days.
- Employee pairs are compared independently of their input order.
- Invalid CSV records and unsupported date formats are rejected.
- Date normalization uses the `Europe/Sofia` timezone.

## Author
Ivan Kirilov
