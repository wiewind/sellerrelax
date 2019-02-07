<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 07.02.2019
 * Time: 13:42
 */
class SysController extends RestAppController
{
    var $restAdress = [
        'orderStatus' => 'rest/orders/statuses'
    ];
    public function importOrderStatuses ($statusId=0) {
        $this->autoRender = false;
        $this->checkLogin();
        CakeLog::write('import', "Import OrderStatuses beginn...");
        $url = $this->restAdress['orderStatus'];
        $params = [];
        $params['itemsPerPage'] = 1000;

        if ($statusId>0) {
            $params['statusIdFrom'] = $statusId;
            $params['statusIdTo'] = $statusId;
        }
        $data = $this->callJsonRest($url, $params, 'GET');

        $entries = $data->entries;
        foreach ($entries as $st) {
            $sid = $st->statusId;

            $d = [
                'status_id' => $sid,
                'color' => $st->color,
                'is_erasable' => $st->isErasable,
                'is_frontend_visible' => $st->isFrontendVisible,
                'created' => GlbF::iso2Date($st->createdAt),
                'modified' => GlbF::iso2Date($st->updatedAt)
            ];
            foreach ($st->names as $lang => $name) {
                switch ($lang) {
                    case 'de':
                        $d['name_de'] = $name;
                        break;
                    case 'en':
                        $d['name_en'] = $name;
                        break;
                }
            }

            $statusInDB = $this->OrderStatus->find('first', [
                'fields' => 'id',
                'conditions' => [
                    'status_id' => $sid
                ]
            ]);
            if ($statusInDB) {
                $d['id'] = $statusInDB['OrderStatus']['id'];
            } else {
                $this->OrderStatus->create();
            }

            $this->OrderStatus->save($d);
        }
        $menge = ($data->lastOnPage) ? $data->lastOnPage - $data->firstOnPage + 1 : 0;
        CakeLog::write('import', "Import OrderStatuses end with $menge record(s)!");
    }
}