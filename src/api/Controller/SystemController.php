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

    var $uses = ['EmptyModel', 'Import', 'Order', 'Item'];

    public function initApp () {
        // NOTICE: To be safe, we don`t cache here regarding IE9 issue
        $this->disableCache();

        if ($this->logged) {
            $username = $this->MySession->read('user.username');
            $userModel = ClassRegistry::init('User');
            $userModel->bindModel([
                'belongsTo' => [
                    'Customer' => [
                        'className' => 'Customer',
                        'foreignKey' => 'customer_id'
                    ]
                ]
            ]);
            $udata = $userModel->find('first', [
                'conditions' => [
                    'username' => $username,
                    'active' => 1
                ]
            ]);
            $this->Login->fillSession($udata);
        }

        // setVersion
        $version = $this->getVersion();
        $this->MySession->writeConfig('version', $version);

        $this->__setLanguage1();
        $this->__setModules();

        if ($this->MySession->checkAll()) {
            $res['session'] = $this->MySession->readAll();
        }

        $res['errorCode'] = $this->__setErrorCode();

        $localeSript = sprintf("../resources/js/locale/ext-locale-%s.js", $this->MySession->read('appLanguage.ext_localname'));
        $this->set('localeSript', $localeSript);
        $this->set('session', $res['session']);
        $this->set('errorCode', $res['errorCode']);

        $this->layout = 'ajax';
    }


    public function mail () {
        $Email = new CakeEmail('gmail');
        $Email->from('zoubenying@hotmail.com');
        $Email->to('zoubenying@gmail.com');
        $Email->subject('test');

        $Email->send("Hallo");
    }
}