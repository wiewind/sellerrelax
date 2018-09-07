<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="X-UA-Compatible" content="chrome=1"/>
    <meta name="google" content="notranslate" />
    <?php
    echo $this->Html->charset();
    echo $this->Html->meta('icon', Configure::read('system.url') . Configure::read('system.image.path') . '/logo/logo1.png');
    ?>

    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/resources/css/login.css?ref=<?= md5(time()) ?>">

    <script type="text/javascript" src="/lib/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/lib/jquery/jquery-3.1.1.min.js"></script>
    <title><?= GlbF::getWebName() ?></title>
</head>
<body>
<div class="login-header"><img src="<?= configure::read('system.image.logo') ?>" alt="LOGO"/></div>
<div class="login-content"><?= $this->fetch('content') ?></div>
<div class="login-foot"><?= GlbF::getWebName() ?> &copy; <?= GlbF::getAuthor() ?> <?= date('Y') ?></div>
</body>
</html>