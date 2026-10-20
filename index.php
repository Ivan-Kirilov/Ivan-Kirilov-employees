<?php

require_once __DIR__ . '/CsvReader.php';
require_once __DIR__ . '/DateNormalizer.php';
require_once __DIR__ . '/WorktimeCalculator.php';

$result = null;
$filename = '';
$error = '';
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

if ($submitted) {
    try {
        $file = $_FILES['csv'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Choose a CSV file!');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Invalid file.');
        }
        $filename = basename($file['name']);
        
        $reader = new CsvReader(new DateNormalizer());
        $projects = $reader->read($file['tmp_name']);

        $calculator = new WorktimeCalculator();
        $result = $calculator->findLongestPair($projects);

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Worktime Analyzer</title>
    <style>
        body { 
                max-width: 900px; 
                margin: 50px auto; 
                padding: 0 20px; 
                color: #000000; 
        }
        form { 
               padding: 20px; 
               background: #eeeeee; 
               border-radius: 6px; 
        }
        
        table { 
                width: 100%; 
                margin-top: 20px; 
        }
        th, td { 
             border: 1px solid #ccc; 
             padding: 10px; 
        }
        td { 
             text-align:right; 
        }
        th { 
             background: #eeeeee;              
        }
        .error { 
             color: #ff0000; 
        }
        .ta {
            text-align: right;
        } ;
        .tc {
            text-align: center;
        } ;

    </style>
</head>
<body>
    <h1>Pair of employees who have worked together.</h1>
    <form method="post" enctype="multipart/form-data">
        <input type="file" id="csv" name="csv" accept=".csv,text/csv" required onchange="if (this.files.length > 0) this.form.requestSubmit();">
        <noscript><button type="submit">Calculate Result</button></noscript>
    </form>

    <?php if ($error !== ''): ?>
        <p class="error" role="alert"><?= e($error) ?></p>
    <?php elseif ($result !== null): ?>
        <table>
            <caption>All common projects of the pair (from <?= e($filename) ?>)</caption>
            <thead>
              <tr>
                <th scope="col" class="tc">Employee ID #1</th>
                <th scope="col" class="tc">Employee ID #2</th>
                <th scope="col" class="tc">Project ID</th>
                <th scope="col" class="tc">Days worked</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($result['projects'] as $projectId => $days): ?>
                <tr>
                    <td><?= $result['emp1'] ?></td>
                    <td><?= $result['emp2'] ?></td>
                    <td><?= $projectId ?></td>
                    <td><?= $days ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total days worked together</th>
                    <th class="ta"><?= $result['totalDays'] ?></th>
                </tr>
            </tfoot>
        </table>
    <?php elseif ($submitted): ?>
        <p>No employees have worked together on any projects.</p>
    <?php endif; ?>
</body>
</html>
