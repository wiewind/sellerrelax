<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 29.01.2019
 * Time: 11:19
 */
class OrdersController extends AppController
{
    var $uses = [
        'Order',
        'Item',
        'OrderItem',
        'OrderProperty',
        'ItemShippingProfile',
        'AlterOrderStatusLog'
    ];

    var $components = ['MySession', 'MyCookie', 'Rest'];

    function checkShippingProfile () {
        $this->autoRender = false;
        $data = $this->Order->find('all', [
            'joins' => array(
                array(
                    'table' => Inflector::tableize('OrderItem'),
                    'alias' => 'OrderItem',
                    'conditions' => array(
                        'Order.extern_id = OrderItem.order_id',
                        'OrderItem.item_id > 0'
                    ),
                    'type' => 'LEFT'
                ),
                array(
                    'table' => Inflector::tableize('OrderProperty'),
                    'alias' => 'OrderShippingProfile',
                    'conditions' => array(
                        'Order.extern_id = OrderShippingProfile.order_id',
                        'OrderShippingProfile.type_id' => 2
                    ),
                    'type' => 'LEFT'
                )
            ),
            'fields' => [
                'Order.extern_id',
                'Order.type_id',
                'Order.status_id',
                'Order.created',
                'Order.updated',
                'OrderItem.item_id',
                'OrderShippingProfile.value',
                //'ItemShippingProfile.profile_id'
            ],
            'conditions' => [
                'Order.type_id' => [1, 2, 5, 6],
                'Order.status_id' => 5
            ]
        ]);

        //echo $this->Order->getLastQuery();

        $orders = [];
        if ($data) {
            foreach ($data as $d) {
                $orders[$d['Order']['extern_id']]['type_id'] = $d['Order']['type_id'];
                $orders[$d['Order']['extern_id']]['status_id'] = $d['Order']['status_id'];
                $orders[$d['Order']['extern_id']]['created'] = $d['Order']['created'];
                $orders[$d['Order']['extern_id']]['updated'] = $d['Order']['updated'];
                $orders[$d['Order']['extern_id']]['orderShippingProfile']=$d['OrderShippingProfile']['value'];
                $item_id = $d['OrderItem']['item_id'];
                $data1 = $this->ItemShippingProfile->find('all', [
                    'fields' => 'profile_id',
                    'conditions' => [
                        'item_id' => $item_id
                    ]
                ]);

                $itemProfiles = [];
                if ($data1) {
                    $itemProfiles = Set::extract('/ItemShippingProfile/profile_id', $data1);
                }
                $orders[$d['Order']['extern_id']]['itemShippingProfile'][$item_id] = $itemProfiles;
            }
        }

        //GlbF::printArray($orders);
        echo "<h3>下表列出了所有status=5，orderType = 1,2,5,6的订单。</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>-</th><th>Order ID</th><th>Type</th><th>Status</th><th>Created</th><th>Updated</th>".
            "<th>Order Shipping Profile<div style='font-size: small'>OrderProperty type=2</div></th>".
            "<th>Item ID</th><th>Item Shipping Profile</th>".
            "<th>allowd Shipping Profile</th></tr>";
        $num = 0;
        foreach ($orders as $order_id => $order) {
            echo "<tr>";
            $itemCount = count($order['itemShippingProfile']);
            $rowspan = ($itemCount > 0) ? " rowspan='$itemCount'" : 1;
            echo "<td{$rowspan}>".(++$num)."</td>";
            echo "<td{$rowspan}>{$order_id}</td>";
            echo "<td{$rowspan}>{$order['type_id']}</td>";
            echo "<td{$rowspan}>{$order['status_id']}</td>";
            echo "<td{$rowspan}>{$order['created']}</td>";
            echo "<td{$rowspan}>{$order['updated']}</td>";
            echo "<td{$rowspan}>{$order['orderShippingProfile']}</td>";
            if ($itemCount > 0) {
                $profiles=[];
                foreach ($order['itemShippingProfile'] as $item_id => $itemProfiles) {
                    foreach ($itemProfiles as $itProfile) {
                        if (array_key_exists($itProfile, $profiles)) {
                            $profiles[$itProfile]++;
                        } else {
                            $profiles[$itProfile] = 1;
                        }
                    }
                }
                $allowdProfiles = [];
                foreach ($profiles as $profile=>$count) {
                    if ($count == $itemCount) {
                        $allowdProfiles[] = $profile;
                    }
                }
                sort($allowdProfiles);


                $numIt = 0;
                foreach ($order['itemShippingProfile'] as $item_id => $itemProfiles) {
                    $profiles = implode(", ", $itemProfiles);
                    if ($numIt == 0) {
                        echo "<td>$item_id</td><td>$profiles</td>";
                        echo "<td{$rowspan}>".implode(", ", $allowdProfiles)."</td></tr>";
                    } else {
                        echo "<tr><td>$item_id</td><td>$profiles</td></tr>";
                    }
                    $numIt++;
                }
            } else {
                echo "<td></td><td></td><td></td></tr>";
            }
        }

        echo "</table>";
    }

    public function checkOrderShippingProfile ($statusIdForCheck = "3.2") {
        $this->autoRender = false;

        $data = $this->Order->find('all', [
            'fields' => [
                'Order.extern_id',
                'Order.type_id',
                'Order.status_id',
                'Order.created',
                'Order.updated',
                'OrderItem.item_id',
                'OrderShippingProfile.value',
                //'ItemShippingProfile.profile_id'
            ],
            'joins' => array(
                array(
                    'table' => Inflector::tableize('OrderItem'),
                    'alias' => 'OrderItem',
                    'conditions' => array(
                        'Order.extern_id = OrderItem.order_id',
                        'OrderItem.item_id > 0'
                    ),
                    'type' => 'LEFT'
                ),
                array(
                    'table' => Inflector::tableize('OrderProperty'),
                    'alias' => 'OrderShippingProfile',
                    'conditions' => array(
                        'Order.extern_id = OrderShippingProfile.order_id',
                        'OrderShippingProfile.type_id' => 2
                    ),
                    'type' => 'LEFT'
                )
            ),
            'conditions' => [
                'Order.type_id' => [1, 2, 5, 6],
                'Order.status_id' => $statusIdForCheck
            ]
        ]);

        $orders = [];
        if ($data) {
            foreach ($data as $d) {
                $orders[$d['Order']['extern_id']]['extern_id'] = $d['Order']['extern_id'];
                $orders[$d['Order']['extern_id']]['type_id'] = $d['Order']['type_id'];
                $orders[$d['Order']['extern_id']]['status_id'] = $d['Order']['status_id'];
                $orders[$d['Order']['extern_id']]['created'] = $d['Order']['created'];
                $orders[$d['Order']['extern_id']]['updated'] = $d['Order']['updated'];
                $orders[$d['Order']['extern_id']]['orderShippingProfile']=$d['OrderShippingProfile']['value'];
                $item_id = $d['OrderItem']['item_id'];
                $data1 = $this->ItemShippingProfile->find('all', [
                    'fields' => 'profile_id',
                    'conditions' => [
                        'item_id' => $item_id
                    ]
                ]);

                $itemProfiles = [];
                if ($data1) {
                    $itemProfiles = Set::extract('/ItemShippingProfile/profile_id', $data1);
                }
                $orders[$d['Order']['extern_id']]['itemShippingProfile'][$item_id] = $itemProfiles;
            }

            foreach ($orders as $order_id => $oData) {
                $this->__checkOrderShippingProfiel($oData);
            }
        }
    }

    private function __checkOrderShippingProfiel ($oData) {
        foreach ($oData['itemShippingProfile'] as $itemShippingProfiles) {
            if (!in_array($oData['orderShippingProfile'], $itemShippingProfiles)) {
                $this->__alterStatus($oData['extern_id'], $oData['status_id'], '4.9');
                return;
            }
        }
    }

    private function __alterStatus ($order_id, $old_status, $new_status) {
        $user = ($this->username) ? $this->username : 'sys';
        $this->AlterOrderStatusLog->save([
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'changed' => date('Y-m-d H:i:s'),
            'changed_by' => $user
        ]);
        $data = $this->Rest->callAPI('put', 'rest/orders/' . $order_id, [
            'statusId' => 4.9
        ]);
        echo $data;
    }
}