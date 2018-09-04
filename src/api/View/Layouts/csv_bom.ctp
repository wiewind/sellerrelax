<?php
$this->response->header("Cache-Control: private, max-age=1");
$this->response->type('text/csv');
$this->response->header("Content-Disposition: attachment; filename=\"export-".date("Ymd_His").".csv\"");
echo "\xEF\xBB\xBF".$this->fetch('content');
?>