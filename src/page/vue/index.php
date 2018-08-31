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

    <meta name="author" content="<?= $config['system']['author'] ?>"/>
    <meta name="description" content="<?= $config['system']['name'] ?>"/>
    <meta name="keywords" content="<?= $config['system']['name'] ?>"/>
    <meta name="publisher" content="<?= $config['system']['author'] ?>"/>
    <meta name="copyright" content="<?= $config['system']['author'] ?>"/>
    <meta http-equiv="expires" content="0"/>

    <title><?= $config['system']['name'] ?></title>
    <!--    <link rel="shortcut icon" href="images/logo_22_16.png" >-->
    <link rel="stylesheet" type="text/css" href="css/main.css?_dc=<?= filemtime('css/main.css') ?>" />
    <script type="text/javascript" src="/lib/jquery/jquery-3.1.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>

</head>
<body>
<div id="app">
    <ol>
        <li v-for="fn in fns">
            <input type="radio" name="fnname" v-bind:value="fn.text" v-model="picked" /> {{ fn.text }}
        </li>
    </ol>
    <button v-on:click="submit">OK</button>
    <div>{{ result }}</div>
</div>

<script src="js/test.js"></script>
</body>
</html>