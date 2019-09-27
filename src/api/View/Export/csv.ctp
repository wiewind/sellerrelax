<?php
    $firstLine = true;
    if (!isset($separator) || $separator === "") $separator = ";";
    if (!isset($withQuotes) || $withQuotes === "") $withQuotes = "1";
    if (isset($header)) {
        echo implode($header, $separator);
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
                        echo $separator;
                    }
                    echo getCsvFieldValue($d1, $separator, $withQuotes);
                    $i++;
                }
            } else {
                echo getCsvFieldValue($d, $separator, $withQuotes);
            }
        }
    }

    function getCsvFieldValue ($data, $separator, $withQuotes=1) {
        $data = str_replace("\n", " ", str_replace('"', "'", str_replace($separator, " ", $data)));
        if ($withQuotes) {
            return '"' . $data . '"';
        }
        return $data;
    }
?>