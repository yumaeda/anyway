<?php
/*
 * Get CSV rows
 *
 * @param string $csv_path
 */
function getCsvRows(string $csv_path) : Generator
{
    $handle = fopen($csv_path, 'rb');

    if (!$handle) {
        throw new Exception();
    }

    while (!feof($handle)) {
        yield fgetcsv($handle);
    }

    fclose($handle);
}


$target_csv = './wines.csv';
$column_names = [];

foreach (getCsvRows($target_csv) as $row) {
    if (count($column_names) === 0) {
        $column_names = $row;
    }
    else {
        //echo $row[0] . ':' . $row[1];
    }
}

print_r($column_names);

try {
    echo copy($target_csv, 'backup.csv');
}
catch (Exception $ex) {
    // Do nothing.
}
