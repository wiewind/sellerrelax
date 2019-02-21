<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 20.02.2019
 * Time: 15:25
 */
class OutstocksController extends AppController
{
    var $uses = [
        'Stock',
        'Item',
        'ItemsVariation',
        'ItemsVariationsBarcode',
        'BarcodeType',
        'Warehouse',
        'StockHistory'
    ];

    public function ShowHotSales () {
        $this->autoRender = false;
        $data = $this->request->data;

        $where = [];
        if ($data['from']) {
            $where[] = "fdate >= '{$data['from']}'";
        }
        if ($data['to']) {
            $where[] = "fdate <= '{$data['to']}'";
        }
        if ($data['warehouse_id'] > 0) {
            $where[] = "warehouse_id = " . $data['warehouse_id'];
        }
        $where = ($where) ? "where " . implode(" and ", $where) : "";

//        echo $where . '<br>';

        $tb = "select id as stock_id, number, ean, warehouse_id, item_id, variation_id, quantity, changed_quantity from stocks $where ".
            "union " .
            "select stock_id, number, ean, warehouse_id, item_id, variation_id, quantity, changed_quantity from stock_histories $where  ";

        $tb = "select tb.stock_id, tb.number, tb.ean, tb.warehouse_id, tb.item_id, tb.variation_id, " .
            "sum(if(tb.changed_quantity < 0, abs(tb.changed_quantity), 0)) as sales, " .
            "sum(if(tb.changed_quantity > 0, tb.changed_quantity, 0)) as purchase, " .
            "warehouses.name as warehouse_name " .
            "from ($tb) tb " .
            "join warehouses on tb.warehouse_id = warehouses.id " .
            "group by tb.stock_id ";

        $sql = "select * from ($tb) HotSales order by {$data['sort']} desc limit {$data['limit']}";
        $data = $this->Stock->query($sql);

        return $data;
    }

    public function showWarehouses () {
        $data = $this->Warehouse->find('all', [
            'fields' => [
                'id',
                'name',
                'protokoll',
                'fdate'
            ],
            'order' => 'name'
        ]);
        $res = [
            [
                'Warehouse' => [
                    'id' => 0,
                    'name' => __('All')
                ]
            ]
        ];
        if ($data) {
            foreach ($data as $w) {
                $res[] = $w;
            }
        }
        return $res;
    }
}