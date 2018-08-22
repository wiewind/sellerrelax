<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 27.06.2018
 * Time: 13:43
 */
class ZoController extends AppController
{
    var $uses = ['EmptyModel', 'Import', 'Order', 'Item', 'OrderStatus', 'propertyType', 'dateType'];
    function test () {
        GlbF::printArray($_SERVER);
        phpinfo();
        $this->autoRender = false;
    }
}