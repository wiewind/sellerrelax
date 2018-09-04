<?php
if (!isset($session['data']['user']['username'])) {
    echo "window.location.assign(Cake.api.path + '/login');";
}


echo 'if (Ext.isClassic) {var js = document.createElement("script"); js.type = "text/javascript"; js.src = "'.$localeSript.'"; document.body.appendChild(js);}' . "\r\n";

echo "var SSD = SSD || " . json_encode($session) . ";\r\n";
echo "var ErrorCode = ErrorCode || " . json_encode($errorCode) . ";";
?>