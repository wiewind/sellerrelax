<?php
$this->response->header("Cache-Control: private, max-age=1");
$this->response->type('text/csv');
if (!isset($filename)) $filename = "export";
$this->response->header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");

echo $this->fetch('content');
?>