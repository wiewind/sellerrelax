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
        'Import',
        'ImportItemProperty'
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

    function getImportItemPropertiesList () {
        $this->checkLogin();
        $params = $this->request->data;
        $conditions = [];

        if ($params['status'] > 0) {
            $conditions['status'] = $params['status'];
        }

        if ($params['itemId'] > 0) {
            $conditions['item_id'] = $params['itemId'];
        }

        if (isset($params['from']) && $params['from']) {
            $conditions['created >= '] = $params['from'] . ' 00:00:00';
        }
        if (isset($params['to']) && $params['to']) {
            $conditions['created <= '] = $params['to'] . ' 23:59:59';
        }

        $total = $this->ImportItemProperty->find('count', [
            'conditions' => $conditions
        ]);

        $data = $this->ImportItemProperty->find('all', [
            'conditions' => $conditions,
            'order' => ['ImportItemProperty.created DESC, ImportItemProperty.item_id, ImportItemProperty.property_id'],
            'page' => $params['page'],
            'limit' => $params['limit']
        ]);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    public function importItemPropertiesCsv() {
        $this->checkLogin();
        $file = $this->request->params['form']['fileToUpload'];
        $row = 0;
        $now = date('Y-m-d H:i:s');
        if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
            $propertyIds = [];
            while (($data = fgetcsv($handle, 1024, "~")) !== FALSE) {
                $num = count($data);
                if ($num > 3) {
                    $row++;
                    if ($row === 1) {
                        for ($i=3; $i<$num; $i++) {
                            $propertyIds[] = substr($data[$i], strpos($data[$i], '%')+1);
                        }
                    } else {
                        $itemId = $data[2];
                        for ($i=3; $i<$num; $i++) {
                            $propertyId = $propertyIds[$i-3];
                            $value = $data[$i];
                            $this->ImportItemProperty->updateAll(
                                [
                                    'status' => 4,
                                    'modified' => '"'.$now.'"'
                                ],
                                [
                                    'item_id' => $itemId,
                                    'property_id' => $propertyId,
                                    'status' => 1
                                ]
                            );
                            $this->ImportItemProperty->create();
                            $this->ImportItemProperty->save([
                                'item_id' => $itemId,
                                'property_id' => $propertyId,
                                'value' => $value,
                                'status' => 1,
                                'created' => $now,
                                'modified' => $now
                            ]);
                        }
                    }
                }
            }
            fclose($handle);
        }
        return $row;
    }
}