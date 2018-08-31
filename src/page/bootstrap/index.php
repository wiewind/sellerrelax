<?php


//if (strpos($_SERVER['HTTP_HOST'], 'local') === false && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS']!=='on')) {
//    header('Location: https://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
//}

require_once '../../api/Config/config_default.php';

?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <script type="text/javascript">
        var Cake = Cake || <?= json_encode($config['system']) ?>;
    </script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Rest</title>

    <link href="/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/normalize.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script src="/lib/jquery/jquery-3.1.1.min.js"></script>
    <script src="/lib/bootstrap/js/bootstrap.min.js"></script>
    <script src="js/bs.js"></script>

</head>
<body>
<h1 align="center">Plenty Rest 测试</h1>
<form role="form" class="testform">
    <div class="form-group">
        <label for="methode">Methode</label>
        <label class="radio-inline">
            <input type="radio" id="methode_get" name="methode" value="GET" checked > Get
        </label>
        <label class="radio-inline">
            <input type="radio" id="methode_post" value="POST" name="methode" disabled > Post
        </label>
        <label class="radio-inline">
            <input type="radio" id="methode_put" value="PUT" name="methode" > Put
        </label>
        <label class="radio-inline">
            <input type="radio" id="methode_delete" value="DELETE" name="methode" disabled > Delete
        </label>
    </div>
    <div class="form-group">
        <label for="fnname">Rest函数 <small class="col-sm-12">例如：rest/orders</small></label>
        <input type="text" class="form-control" id="fnname" name="fnname" placeholder="Rest函数">
    </div>
    <div class="form-group">
        <label for="params">参数 <small class="col-sm-12">每行一个参数，格式：updatedAtFrom => 2018-07-13T00:00:00+02:00:00</small></label>
        <textarea class="form-control" id="params" name="params" placeholder="请输入参数" rows="6"></textarea>
    </div>

    <div class="form-inline">
        <label>每页数量</label>
        <input type="text" class="form-control" style="margin-left: 5px; margin-right: 20px; width: 60px; text-align: right;" id="itemsPerPage" name="itemsPerPage" value="10"><br>

        <label>第</label>
        <input type="text" class="form-control" style="margin: 0 5px; width: 60px; text-align: right;" id="page" name="page" value="1">
        <label>页</label>
    </div>

    <div class="form-group" style="margin-top: 20px;">
        <button type="button" class="btn btn-default" onclick="onSubmit()">提交</button>
    </div>

    <div class="resdiv" id="resdiv">
        此处显示测试结果！
    </div>
</form>

</body>
</html>