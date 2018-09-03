<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 04.06.2018
 * Time: 15:51
 */
class ItemsController extends AppController
{
    var $uses = [
        'Import',
        'Order',
        'Item',
        'OrderItem',
        'RestToken',
        'Unit',
        'ItemsVariation',
        'ItemsVariationsBarcode',
        'BarcodeType'
    ];

    public function listItems () {
        $params = $this->request->data;
        $total = $this->Item->find('count');

        $sortObj = (isset($params['sort'])) ? json_decode($params['sort']) : false;

        $sortColum = ($sortObj) ? $sortObj[0]->property : 'count_orders';
        $sortDirection = ($sortObj) ? $sortObj[0]->direction : (($sortColum === 'count_orders') ? 'DESC' : 'ASC');

        $searchDays = isset($params['searchDays']) ? $params['searchDays'] : 7;

        $sql = "select Item.* , ItemOrderCount.currency as Item__currency, " .
            "ifnull(ItemOrderCount.count_orders, 0) as Item__count_orders, " .
            "ifnull(ItemOrderCount.sum_quantity, 0) as Item__sum_quantity, " .
            "ifnull(ItemOrderCount.sum_price_net, 0) as Item__sum_price_net " .
            "from items Item ".
            "left join ( ".
                "select OrderItem.item_id, OrderItem.currency, count(OrderItem.order_id) as count_orders, sum(OrderItem.quantity) as sum_quantity, sum(OrderItem.price_net) as sum_price_net " .
                "from order_items OrderItem ".
                "where OrderItem.type_id = 1 ".
                "and OrderItem.order_id in ( ".
                    "select `Order`.extern_id from orders `Order` where `Order`.type_id = 1 and `Order`.deleted = 0 and `Order`.enty_date >= '".date('Y-m-d 00:00:00', strtotime("-".$searchDays." day"))."' ".
                ") " .
                "group by OrderItem.item_id ".
            ") ItemOrderCount on ItemOrderCount.item_id = Item.extern_id ";
        if (isset($params['searchText']) && strlen(trim($params['searchText'])) > 0) {
            $sql .= "where Item.name like '%" . trim($params['searchText']) . "%' ";
        }
        $sql .= "order by {$sortColum} {$sortDirection} " .
            "limit " . ($params['limit'] * ($params['page'] - 1)) . ", " . $params['limit'];

        $this->Item->virtualFields['currency'] = 'ItemOrderCount.currency';
        $this->Item->virtualFields['count_orders'] = 'ItemOrderCount.count_orders';
        $this->Item->virtualFields['sum_quantity'] = 'ItemOrderCount.sum_quantity';
        $this->Item->virtualFields['sum_price_net'] = 'ItemOrderCount.sum_price_net';
        $data = $this->Item->query($sql);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    function test () {
        echo "alle";
        GlbF::printArray(Configure::read('system.jquery'));
        echo strtotime('now');
    }
}