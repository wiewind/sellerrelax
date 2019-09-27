<?php
$this->response->header("Cache-Control: private, max-age=1");
$this->response->type('text/csv');
if (!isset($filename)) $filename = "export";
$this->response->header("Content-Disposition: attachment; filename=\"{$filename}_".date("Ymd_His").".csv\"");

echo $this->fetch('content');
?>