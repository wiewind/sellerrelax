<?php

/**
 * Created by PhpStorm.
 * User: benyingz
 * Date: 01.10.2019
 * Time: 14:58
 */
class TestShell extends  AppShell
{
    public $uses = array('Import');
    public $tasks = array();
    public function main () {
        echo "Hallo!!!";
        $this->out("It's Shell");
        $this->Import->save([
            'id' => 1,
            'errors' => '[1]'
        ]);
        GlbF::printArray($this->Import->findById(1));
    }

}