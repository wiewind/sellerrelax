<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 27.06.2018
 * Time: 13:43
 */
class ZoController extends AppController
{
    var $uses = ['OrderProperty'];

    function test () {
        $this->autoRender = false;
        $data = $this->OrderProperty->find('all');
        GlbF::printArray($data);

    }
}