<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 06.09.2018
 * Time: 11:59
 */
class ImportController extends AppController
{
    function getRobots () {
        $this->checkLogin();
        $params = $this->request->data;

        $total = $this->Import->find('count', [
            'conditions' => [
                'ip <> ' => '134.119.253.18'
            ]
        ]);

        $data = $this->Import->find('all', [
            'fields' => [
                'id',
                'import_beginn',
                'url',
                'ip',
                'type'
            ],
            'conditions' => [
                'ip <> ' => '134.119.253.18'
            ],
            'order' => 'import_beginn desc',
            'page' => $params['page'],
            'limit' => $params['limit']
        ]);

        return [
            'total' => $total,
            'data' => $data
        ];
    }
}