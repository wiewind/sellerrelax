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
            'fields' => [
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
            'fields' => [
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

    function getSalesAmount3Y () {
        $params = $this->request->data;

        if ($params['priceType'] == 'gross') {
            $sumField = 'gross_total';
        } else {
            $sumField = 'net_total';
        }

        $years = explode(':', $params['years']);

        $conditions = [
            'org_order_id' => 0,
            'type_id' => 1,
            'deleted' => 0
        ];

        foreach ($years as $year) {
            if ($year) {
                $conditions['or'][] = [
                    'enty_date >= ' => $year . '-01-01 00:00:00',
                    'enty_date <= ' => $year . '-12-31 12:59:59'
                ];
            }
        }

        $options = [
            'fields' => [
                'date_format(enty_date, "%Y-%m") as date',
                'ROUND(sum(' . $sumField . ' / (IF(exchange_rate>0, exchange_rate, 1))), 2) as sum'
            ],
            'conditions' => $conditions,
            'group' => 'date_format(enty_date, "%Y-%m")',
            'order' => 'date_format(enty_date, "%Y-%m")'
        ];

        $data = $this->Order->find('all', $options);

        $res = [];
        for ($i=0; $i<12; $i++) {
            $res[$i]['mon_num'] = $i;
            $res[$i]['date'] = __(date('F', strtotime('2010-'.($i + 1).'-01')));
            for ($j=1; $j<=3; $j++) {
                $res[$i]['sum' . $j] = 0;
            }
        }

        if ($data) {
            foreach ($data as $d) {
                list($year, $mon) = explode('-', $d[0]['date']);
                foreach ($years as $i => $allowYear) {
                    if ($year == $allowYear) {
                        $res[$mon - 1]['sum' . ($i + 1)] = $d[0]['sum'];
                    }
                }
            }
        }
        return $res;
    }
}