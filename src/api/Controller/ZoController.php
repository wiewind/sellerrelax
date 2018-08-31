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
        $this->autoRender = false;
        echo Barcode::getHtmlImg('081231723897', Barcode::TYPE_CODE_128);
    }
}