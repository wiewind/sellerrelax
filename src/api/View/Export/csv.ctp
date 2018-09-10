<?php
$firstLine = true;
if (isset($header)) {
    echo implode($header, ';');
    $firstLine = false;
}
if (isset($data)) {
    foreach ($data as $d) {
        if ($firstLine) {
            $firstLine = false;
        } else {
            echo "\n";
        }
        if (is_array($d)) {
            $count_data_per_row = count($d);
            $i = 0;
            foreach ($d as $d1) {
                if ($i > 0) {
                    echo ";";
                }
                echo getCsvFieldValue($d1);
                $i++;
            }
        } else {
            echo getCsvFieldValue($d);
        }
    }
}

function getCsvFieldValue ($data) {
    $data = str_replace('"', "'", str_replace(';', ",", $data));
    return '"' . str_replace("\n", " ", $data) . '"';
}