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

        if ($data) {
            foreach ($data as $key => $d) {
                $url = "https://ipstack.com/ipstack_api.php?ip=" . $d['Import']['ip'];
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $result = json_decode(curl_exec($curl));
                curl_close($curl);
                $data[$key]['Import']['ip_info'] = [
                    'continent' => $result->continent_name,
                    'country' => $result->country_name,
                    'region' => $result->region_name,
                    'city' => $result->city_name,
                    'hostname' => $result->hostname,
                    'latitude' => $result->latitude,
                    'longitude' => $result->longitude
                ];
            }
        }

        return [
            'total' => $total,
            'data' => $data
        ];
    }
}