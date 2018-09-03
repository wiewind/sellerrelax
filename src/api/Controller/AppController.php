<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::import('Core', 'l10n');
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */

class AppController extends Controller {

    public $components = array(
        'RequestHandler',
        'MySession',
        "Session",
        'MyCookie',
        'Cookie',
        'Paginator'
    );

    var $helpers = array(
        'Html',
        'Form',
        'Js'
    );

    public $allow = array();

    public $logged = false;

    function checkLogin () {
        if (!$this->logged) {
            ErrorCode::throwExceptionCode(ErrorCode::ErrorCodeUserDenied);
        }
    }

    function beforeFilter () {

        $this->__setPageLanguage();

        //-------------------------

        parent::beforeFilter();
    }

    // -------- i18 -------------------
    private function __setPageLanguage () {
        $lang = 'zho';
        if ($this->MySession->check('appLanguage')) {
            $lang = $this->MySession->read('appLanguage.cake_code');
        }
        Configure::write('Config.language', $lang);
        $this->MySession->write('formatting', Configure::read('Glb.formatting.' . $lang));

    }

    function afterFilter() {


        parent::afterFilter();
    }

    function get_client_ip() {
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public function json () {
        $args = func_get_args();
        $fn_name = (count($args) > 0) ? $args[0] : false;
        array_splice($args, 0, 1);

        $result = array(
            'data' => array(),
            'message' => '',
            'success' => true,
            'code' => 200
        );
        if ($fn_name) {
            try {
                $r = call_user_func_array(array($this, $fn_name), $args);
                if (is_array($r) && isset($r['data'])) $result = array_merge($result, $r);
                else $result['data'] = $r;
            } catch (Exception $e) {
                $result['success'] = false;
                $result['message'] = $e->getMessage();
                $result['code'] = $e->getCode();
            }
        }

        $this->set('result', $result);
        $this->layout = 'ajax';
        $this->render ('/Json/output');
    }

    public function transjson () {
        $args = func_get_args();
        $fn_name = (count($args) > 0) ? $args[0] : false;
        array_splice($args, 0, 1);

        $result = array(
            'data' => array(),
            'message' => '',
            'success' => true,
            'code' => 200
        );
        if ($fn_name) {
            $dataSource = ClassRegistry::init('User')->getDataSource();
            $dataSource->begin();
            try {
                $r = call_user_func_array(array($this, $fn_name), $args);
                if (is_array($r) && isset($r['data'])) $result = array_merge($result, $r);
                else $result['data'] = $r;
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $result['success'] = false;
                $result['message'] = $e->getMessage();
                $result['code'] = $e->getCode();
            }
        }

        $this->set('result', $result);
        $this->layout = 'ajax';
        $this->render ('/Json/output');
    }

    protected function _translateTo ($msg, $languageId) {
        $webLanguage = Configure::read('Config.language');
        Configure::write('Config.language', $this->MySession->readConfig('languages.' . $languageId . '.cake_code'));
        $res = __($msg);
        Configure::write('Config.language', $webLanguage);
        return $res;
    }
}