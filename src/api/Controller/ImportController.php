<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 06.09.2018
 * Time: 11:59
 */
class ImportController extends AppController
{
    var $uses = [
        'Import'
    ];

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
                'type',
                'import_beginn',
                'url',
                'ip',
                'ip_location'
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

    function getIpLocation () {
        $id = $this->request->data['id'];
        $import = $this->Import->findById($id);
        $url = "https://ipstack.com/ipstack_api.php?ip=" . $import['Import']['ip'];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($curl));
        curl_close($curl);

        $info = [
            'continent' => $result->continent_name,
            'country' => $result->country_name,
            'region' => $result->region_name,
            'city' => $result->city_name,
            'hostname' => $result->hostname,
            'latitude' => $result->latitude,
            'longitude' => $result->longitude
        ];

        $this->Import->save([
            'id' => $id,
            'ip_location' => json_encode($info)
        ]);

        return $info;
    }

    function getImports () {
        $this->checkLogin();
        $params = $this->request->data;

        if ($params['type'] !== 'all') {
            $conditions = ['type' => $params['type']];
        }
        if (isset($params['from']) && $params['from']) {
            $conditions['update_from >= '] = $params['from'] . ' 00:00:00';
        }
        if (isset($params['to']) && $params['to']) {
            $conditions['update_to <= '] = $params['to'] . ' 23:59:59';
        }
        if (isset($params['hasMenge']) && $params['hasMenge'] === 'true') {
            $conditions['menge > '] = 0;
        }

        $total = $this->Import->find('count', [
            'conditions' => $conditions
        ]);

        $data = $this->Import->find('all', [
            'conditions' => $conditions,
            'order' => ['Import.import_beginn DESC'],
            'page' => $params['page'],
            'limit' => $params['limit']
        ]);

        return [
            'total' => $total,
            'data' => $data
        ];
    }

    function listTypes () {
        $this->checkLogin();
        $data = $this->Import->find('all', [
            'fields' => 'type',
            'group' => 'type',
            'sort' => 'type'
        ]);
        $res = [
            [
                'type' => 'all',
                'display' => __('All')
            ]
        ];
        $total = 1;
        if ($data) {
            $total = count($data) + 1;
            foreach ($data as $d) {
                $res[] = [
                    'type' => $d['Import']['type'],
                    'display' => $d['Import']['type']
                ];
            }
        }
        return [
            'data' => $res,
            'total' => $total
        ];
    }
}