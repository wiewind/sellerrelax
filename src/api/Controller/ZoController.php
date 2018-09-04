<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 27.06.2018
 * Time: 13:43
 */
class ZoController extends AppController
{
    var $uses = ['TestJob'];

    function test () {
        $this->autoRender = false;
        $importData = [
            'url' => $_SERVER['SCRIPT_URI'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ];

//        GlbF::printArray($_SERVER);

        $this->TestJob->create();
        $this->TestJob->save($importData);
    }
}