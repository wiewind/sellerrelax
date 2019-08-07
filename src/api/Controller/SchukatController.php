<?php

/**
 * Created by PhpStorm.
 * User: benyingz
 * Date: 30.07.2019
 * Time: 15:06
 */
class SchukatController extends AppController
{
    var $zipUrl = 'https://www.schukat.com/schukat/schukat_cms_de.nsf/78e3877cd05b8905c1256d3d003c1083/7a494cd3a6009a3bc125754a0036811b/$FILE/SE_ART4.zip';
    var $schukatPath = ROOT.'/__import__/stocks';

    public function download () {
        $urlLogin = "https://www.schukat.com/names.nsf?Login";

        $post = array(
            'Username' => 'delychigmbh',
            'Password' => 'ym4kZWUfw8gB',
            'redirectTo' => $this->zipUrl
        );

        $cookie = $this->schukatPath.'/tmpcookie.txt';
        $this->login_post($urlLogin, $cookie, $post);

        $savefile = $this->schukatPath . '/schukat.zip';
        $this->get_content($this->zipUrl, $cookie, $savefile);

        $zip = new ZipArchive;
        if ($zip->open($savefile) === TRUE) {
            $zip->extractTo($this->schukatPath);
            $zip->close();
        } else {
            $this->__throwError('Unzip Error: ' . $savefile, ErrorCode::ErrorCodeServerInternal);
        }

        @unlink($cookie);
        @unlink($savefile);
    }

    function login_post($url, $cookie, $post){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_exec($ch);
        curl_close($ch);
    }

    function get_content($url, $cookie, $savefile){
        //Open file handler.
        $fp = fopen($savefile, 'w+');

        //If $fp is FALSE, something went wrong.
        if($fp === false){
            $this->__throwError('Could not open: ' . $savefile, ErrorCode::ErrorCodeServerInternal);
        }

        $ch = curl_init(); //初始化curl模块
        curl_setopt($ch, CURLOPT_URL, $url); //登录提交的地址

        //Pass our file handle to cURL.
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        $rs = curl_exec($ch);

        //If there was an error, throw an Exception
        if(curl_errno($ch)){
            $this->__throwError(curl_error($ch), curl_errno($ch));
        }

        //Get the HTTP status code.
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if($statusCode != 200){
            $this->__throwError(__('Zipfile can not be download!'), $statusCode);
        }
        return true;

    }

    private function __throwError ($msg, $code) {
        $Email = new CakeEmail();
        $Email->from(Configure::read('system.admin.frommail'));
        $Email->to(Configure::read('system.admin.tomail'));
        $Email->cc(Configure::read('system.dev.email'));

        $Email->subject("Fehler bei Import Item Property!");
        $Email->emailFormat('html');
        $Email->template('resterror');

        $Email->viewVars(array(
            'url' => 'schukat/download',
            'err' =>$msg,
            'params' => [
                'zipUrl' => $this->zipUrl
            ]
        ));
        $Email->send();

        ErrorCode::throwException($msg, $code);
    }
}