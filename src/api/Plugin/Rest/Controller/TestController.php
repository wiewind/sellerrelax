<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 28.06.2018
 * Time: 11:51
 */

class TestController extends AppController
{
    var $uses = ['Import', 'Order', 'Item', 'RestToken'];
    var $components = ['MySession', 'MyCookie', 'Rest'];

    function beforeFilter () {
        $this->autoRender = false;
        parent::beforeFilter();
    }

    function index ($fn = "") {
        if ($fn === "") $fn = $this->request->data['fn'];
        $methode = (isset($this->request->data['method'])) ?  strtoupper($this->request->data['method']) : "GET";
        if (!$fn) {
            return "Error";
        }

        $params = (isset($this->request->data['params'])) ?  $this->request->data['params'] : [];

        if (is_array($params)) {
            if (isset($this->request->data['itemsPerPage']) && $this->request->data['itemsPerPage'] > 0) {
                $params['itemsPerPage'] = $this->request->data['itemsPerPage'];
            }

            $params['page'] = (isset($this->request->data['page'])) ?  strtoupper($this->request->data['page']) : 1;
        }

        if (GlbF::strStartsWith($fn, 'rest/login')) {
            $username = isset($params['username']) ? $params['username'] : '';
            $password = isset($params['password']) ? $params['password'] : '';
            return $this->Rest->login($username, $password);
        }

        return $this->Rest->callAPI($methode, $fn, $params);
    }
}