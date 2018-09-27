<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 26.09.2018
 * Time: 13:20
 */
class ChartController extends AppController
{
    var $uses = [
        'Order',
        'OrderItem',
        'Items',
        'ItemVariation'
    ];
    function getOrderCount () {
        $params = $this->request->data;
        if ($params['period'] == 'day') {
            $limit = 60;
            $dateFormat = 'date_format(enty_date, "%Y-%m-%d")';
        } else if ($params['period'] == 'month') {
            $limit = 24;
            $dateFormat = 'date_format(enty_date, "%Y-%m")';
        } else {
            // $params['period'] == 'year'
            $limit = -1;
            $dateFormat = 'date_format(enty_date, "%Y")';
        }

        $options = [
            fields => [
                $dateFormat . ' as date',
                'count(id) as sum'
            ],
            'conditions' => [
                'org_order_id' => 0,
                'type_id' => 1,
                'deleted' => 0
            ],
            'group' => $dateFormat,
            'order' => $dateFormat . ' DESC'
        ];
        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        $data = $this->Order->find('all', $options);
        $res = [];
        if ($data) {
            $dataLength = count($data);
            for ($i=$dataLength - 1; $i>=0; $i--) {
                $res[] = $data[$i][0];
            }
        }
        return $res;
    }

    function getSalesAmount () {
        $params = $this->request->data;
        if ($params['period'] == 'day') {
            $limit = 60;
            $dateFormat = 'date_format(enty_date, "%Y-%m-%d")';
        } else if ($params['period'] == 'month') {
            $limit = 24;
            $dateFormat = 'date_format(enty_date, "%Y-%m")';
        } else {
            // $params['period'] == 'year'
            $limit = -1;
            $dateFormat = 'date_format(enty_date, "%Y")';
        }

        if ($params['priceType'] == 'gross') {
            $sumField = 'gross_total';
        } else {
            $sumField = 'net_total';
        }

        $options = [
            fields => [
                $dateFormat . ' as date',
                'ROUND(sum(' . $sumField . ' / (IF(exchange_rate>0, exchange_rate, 1))), 2) as sum'
            ],
            'conditions' => [
                'org_order_id' => 0,
                'type_id' => 1,
                'deleted' => 0
            ],
            'group' => $dateFormat,
            'order' => $dateFormat . ' DESC'
        ];
        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        $data = $this->Order->find('all', $options);
        $res = [];
        if ($data) {
            $dataLength = count($data);
            for ($i=$dataLength - 1; $i>=0; $i--) {
                $res[] = $data[$i][0];
            }
        }
        return $res;
    }
}