<?php

require_once '../../api/Config/config_default.php';

?><!DOCTYPE html>
<html lang="zh-CN">
<head>

<script src="/lib/jquery/jquery-3.1.1.min.js"></script>
</head>
<body>

<div id="testDiv"></div>
<script type="text/javascript">
    var Cake = Cake || <?= json_encode($config['system']) ?>;

    $.ajax({
        url: Cake.api.path + "/barcode/generator/getImgHtml/123456789",
        method: 'POST',
//        data: ajaxOption
    }).done(function(data) {
        $('#testDiv').html(data);
    });
</script>
</body>