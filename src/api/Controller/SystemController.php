<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 04.05.2018
 * Time: 13:56
 */
class SystemController extends AppController
{
    public $components = array(
        'Login',
        'MySession',
        'MyCookie'
    );

    public $uses = array(
//        'Version',
//        'UserEvent',
        'Language'
    );

    public function initApp () {
        // NOTICE: To be safe, we don`t cache here regarding IE9 issue
        $this->disableCache();

        if ($this->logged) {
            $username = $this->MySession->read('user.username');
        }

        $this->__setLanguage();

        if ($this->MySession->checkAll()) {
            $res['session'] = $this->MySession->readAll();
        }
        $localeSript = sprintf("../resources/js/locale/ext-locale-%s.js", $this->MySession->read('appLanguage.ext_localname'));
        $this->set('localeSript', $localeSript);

        $res['errorCode'] = $this->__setErrorCode();
        $this->set('session', $res['session']);
        $this->set('errorCode', $res['errorCode']);

        $this->layout = 'ajax';
    }

    private function __setLanguage () {
        $this->MySession->deleteConfig('languages');
        $this->MySession->delete('appLanguage');

        $app_language = Configure::read('Config.language');
        $app_language_id = 1;

        $langs = $this->Language->find('all', [
            'order' => 'id'
        ]);
        $languages = [];
        foreach($langs as $l) {
            $languages[$l['Language']['id']] = $l['Language'];
            if ($l['Language']['cake_code'] === $app_language) {
                $app_language_id = $l['Language']['id'];
            }
        }

        $this->MySession->writeConfig('languages', $languages);
        $this->MySession->write('appLanguage', $languages[$app_language_id]);
    }


    private function __setErrorCode () {
        $errorCodeClass = new ReflectionClass('ErrorCode');
        $codes = $errorCodeClass->getConstants();
        $msgs[0] = ErrorCode::getExceptionMessage(0);
        foreach ($codes as $code) {
            $msgs[$code] = ErrorCode::getExceptionMessage($code);
        }
        return array_merge($codes, ['messages' => $msgs]);
    }

    public function login () {
        $this->layout = 'login';
        $this->Login->logout();
    }

    public function doLogin () {
        $username = (isset($this->request->data)) ? $this->request->data['username'] : '';
        $password = (isset($this->request->data)) ? $this->request->data['password'] : '';
        $this->Login->checkLoginData($username, $password);
    }

    public function doLogout () {
        $this->Login->logout();
    }

    public function keeplive () {
        $this->checkLogin();
    }

    public function mail () {
        $Email = new CakeEmail('gmail');
        $Email->from('zoubenying@hotmail.com');
        $Email->to('zoubenying@gmail.com');
        $Email->subject('test');
        $Email->send("Hallo");
    }
}