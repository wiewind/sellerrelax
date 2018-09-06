<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 05.09.2018
 * Time: 10:31
 */
class ExportSettingsController extends AppController
{
    function getSkuArticles () {
        $this->checkLogin();
        $params = $this->request->data;
        $total = $this->ExportSetting->find('count', [
            'conditions' => [
                'type' => 'sku_a'
            ]
        ]);

        $data = $this->ExportSetting->find('all', [
            'conditions' => [
                'type' => 'sku_a'
            ],
            'order' => 'value',
            'page' => $params['page'],
            'limit' => $params['limit']
        ]);

        return [
            'total' => $total,
            'data' => $data
        ];
    }

    function getFbaCustomers () {
        $this->checkLogin();
        $params = $this->request->data;
        $total = $this->ExportSetting->find('count', [
            'conditions' => [
                'type' => 'fba_c'
            ]
        ]);

        $data = $this->ExportSetting->find('all', [
            'conditions' => [
                'type' => 'fba_c'
            ],
            'order' => 'value',
            'page' => $params['page'],
            'limit' => $params['limit']
        ]);

        return [
            'total' => $total,
            'data' => $data
        ];
    }

    function importSkuArticles () {
        $this->checkLogin();
        $file = $this->request->params['form']['fileToUpload'];
        $removeOldData = intval($this->request->data['removeOldData']);

        if ($removeOldData) {
            $this->ExportSetting->deleteAll([
                'type' => 'sku_a'
            ]);
        }

        $rec_num = 0;

        if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                if ($num === 1 && $data[0]) {
                    $d = $this->ExportSetting->find('first', [
                        'conditions' => [
                            'type' => 'sku_a',
                            'value' => $data[0]
                        ]
                    ]);
                    if (!$d) {
                        $this->ExportSetting->create();
                        $this->ExportSetting->save([
                            'type' => 'sku_a',
                            'value' => $data[0]
                        ]);
                        $rec_num++;
                    }
                }
            }
            fclose($handle);
        }

        return $rec_num;
    }

    function getExportSettingValue () {
        $this->checkLogin();
        $id = $this->request->data['id'];
        $type = $this->request->data['type'];
        $data = $this->ExportSetting->find('first', [
            'fields' => 'value',
            'conditions' => [
                'id' => $id,
                'type' => $type
            ]
        ]);
        if ($data) {
            $data = $data['ExportSetting'];
        }
        return $data;
    }

    function save () {
        $this->checkLogin();
        $data = $this->request->data;

        if ($data['id'] === 0) {
            unset($data['id']);
            $this->ExportSetting->create();
        }
        $this->ExportSetting->save($data);
    }

    function delete () {
        $this->checkLogin();
        $this->ExportSetting->delete($this->request->data['id']);
    }
}