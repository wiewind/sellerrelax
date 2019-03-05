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

    callImport();

    function callImport() {
        $.ajax({
            url: Cake.api.path + "/rest/accounts/json/importContacts",
            method: 'POST'
        }).done(function(data) {
            data = JSON.parse(data);

            $('#testDiv').html($('#testDiv').val() + data.data.page+"/"+data.data.last_page_no+": "+data.data.menge + "<br />");
            console.log(data.data.page+"/"+data.data.last_page_no+": "+data.data.menge);
            if (data.data.is_last_page) {
                alert("finished!!!!!!")
            } else {
                callImport();
            }
        });
    }
</script>
</body>