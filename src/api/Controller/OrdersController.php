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
        'OrderItemProperty',
        'OrderProperty',
        'ItemShippingProfile',
        'AlterOrderStatusLog'
    ];

    var $components = ['MySession', 'MyCookie', 'Rest'];

    var $status = [
        'valid' => 5.05,
        'invalid' => 4.9
    ];

    public function test ($orderId) {
        $this->autoRender = false;

        $data = $this->Order->find('all', [
            'fields' => [
                'Order.extern_id',
                'Order.type_id',
                'Order.status_id',
                'Order.created',
                'Order.updated',
                'OrderItem.item_id',
                'OrderItem.item_variation_id',
                'DeliveryAddress.address1',
                'DeliveryAddress.address2',
                'OrderShippingProfile.value',
                'OrderItem.quantity',
                'OrderItemWeight.value'
            ],
            'joins' => array(
                array(
                    'table' => Inflector::tableize('OrderItem'),
                    'alias' => 'OrderItem',
                    'conditions' => array(
                        'Order.extern_id = OrderItem.order_id',
                        'OrderItem.item_id > 0',
                        'OrderItem.type_id' => [1, 2, 3]
                    ),
                    'type' => 'LEFT'
                ),
                array(
                    'table' => Inflector::tableize('ItemsVariation'),
                    'alias' => 'ItemsVariation',
                    'conditions' => array(
                        'OrderItem.item_variation_id = ItemsVariation.extern_id'
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
                ),
                array(
                    'table' => Inflector::tableize('OrderItemProperty'),
                    'alias' => 'OrderItemWeight',
                    'conditions' => array(
                        'OrderItem.extern_id = OrderItemWeight.order_item_id',
                        'OrderItemWeight.type_id' => 11
                    ),
                    'type' => 'LEFT'
                ),
                array(
                    'table' => Inflector::tableize('Address'),
                    'alias' => 'DeliveryAddress',
                    'conditions' => array(
                        'Order.delivery_address_id = DeliveryAddress.extern_id'
                    ),
                    'type' => 'LEFT'
                )
            ),
            'conditions' => [
                'Order.extern_id' => $orderId
            ]
        ]);
        $orders = [];
        $res = [];
        if ($data) {
            foreach ($data as $d) {
                $orders[$d['Order']['extern_id']]['extern_id'] = $d['Order']['extern_id'];
                $orders[$d['Order']['extern_id']]['type_id'] = $d['Order']['type_id'];
                $orders[$d['Order']['extern_id']]['status_id'] = $d['Order']['status_id'];
                $orders[$d['Order']['extern_id']]['created'] = $d['Order']['created'];
                $orders[$d['Order']['extern_id']]['updated'] = $d['Order']['updated'];
                $orders[$d['Order']['extern_id']]['orderShippingProfile']=$d['OrderShippingProfile']['value'];
                $orders[$d['Order']['extern_id']]['DeliveryAddress1']=$d['DeliveryAddress']['address1'];
                $orders[$d['Order']['extern_id']]['DeliveryAddress2']=$d['DeliveryAddress']['address2'];
                $weight = ($d['OrderItemWeight']['value']) ? $d['OrderItemWeight']['value'] : 0;
                $quantity = $d['OrderItem']['quantity'];
                $orders[$d['Order']['extern_id']]['VariationWeight'][$d['OrderItem']['item_variation_id']]= $weight * $quantity;
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
       // GlbF::printArray($this->Order->getDatasource()->getLog());
        GlbF::printArray($orders);
    }

    public function checkStatus5 ($statusIdForCheck = 5) {
        $this->autoRender = false;

        $data = $this->Order->find('all', [
            'fields' => [
                'Order.extern_id',
                'Order.type_id',
                'Order.status_id',
                'Order.created',
                'Order.updated',
                'OrderItem.item_id',
                'OrderItem.item_variation_id',
                'DeliveryAddress.address1',
                'DeliveryAddress.address2',
                'OrderShippingProfile.value',
                'OrderItem.quantity',
                'OrderItemWeight.value'
            ],
            'joins' => array(
                array(
                    'table' => Inflector::tableize('OrderItem'),
                    'alias' => 'OrderItem',
                    'conditions' => array(
                        'Order.extern_id = OrderItem.order_id',
                        'OrderItem.item_id > 0',
                        'OrderItem.type_id' => [1, 2, 3]
                    ),
                    'type' => 'LEFT'
                ),
                array(
                    'table' => Inflector::tableize('ItemsVariation'),
                    'alias' => 'ItemsVariation',
                    'conditions' => array(
                        'OrderItem.item_variation_id = ItemsVariation.extern_id'
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
                ),
                array(
                    'table' => Inflector::tableize('OrderItemProperty'),
                    'alias' => 'OrderItemWeight',
                    'conditions' => array(
                        'OrderItem.extern_id = OrderItemWeight.order_item_id',
                        'OrderItemWeight.type_id' => 11
                    ),
                    'type' => 'LEFT'
                ),
                array(
                    'table' => Inflector::tableize('Address'),
                    'alias' => 'DeliveryAddress',
                    'conditions' => array(
                        'Order.delivery_address_id = DeliveryAddress.extern_id'
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
        $res = [];
        if ($data) {
            foreach ($data as $d) {
                $orders[$d['Order']['extern_id']]['extern_id'] = $d['Order']['extern_id'];
                $orders[$d['Order']['extern_id']]['type_id'] = $d['Order']['type_id'];
                $orders[$d['Order']['extern_id']]['status_id'] = $d['Order']['status_id'];
                $orders[$d['Order']['extern_id']]['created'] = $d['Order']['created'];
                $orders[$d['Order']['extern_id']]['updated'] = $d['Order']['updated'];
                $orders[$d['Order']['extern_id']]['orderShippingProfile']=$d['OrderShippingProfile']['value'];
                $orders[$d['Order']['extern_id']]['DeliveryAddress1']=$d['DeliveryAddress']['address1'];
                $orders[$d['Order']['extern_id']]['DeliveryAddress2']=$d['DeliveryAddress']['address2'];
                $weight = ($d['OrderItemWeight']['value']) ? $d['OrderItemWeight']['value'] : 0;
                $quantity = $d['OrderItem']['quantity'];
                $orders[$d['Order']['extern_id']]['VariationWeight'][$d['OrderItem']['item_variation_id']]= $weight * $quantity;
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
                $newStatus = $this->__checkOrderShippingProfiel($oData);
                if ($newStatus === $this->status['valid']) {
                    $newStatus = $this->__checkAddress($oData);
                }
                if ($newStatus === $this->status['valid']) {
                    $newStatus = $this->__checkWeight($oData);
                }
                if ($newStatus === $this->status['valid']) {
                    $newStatus = $this->__alterStatus($oData['extern_id'], $oData['status_id'], $this->status['valid'], 'valid');
                }
                $res[$order_id] = $newStatus;
            }
        }
        return $res;
    }

    private function __checkOrderShippingProfiel ($oData) {
        foreach ($oData['itemShippingProfile'] as $itemShippingProfiles) {
            if (!in_array($oData['orderShippingProfile'], $itemShippingProfiles)) {
                $newStatus = $this->__alterStatus($oData['extern_id'], $oData['status_id'], $this->status['invalid'], 'invalid shippingProfile');
                return $newStatus;
            }
        }
        return $this->status['valid'];
    }

    private function __checkAddress ($oData) {
        if (strlen($oData['DeliveryAddress2']) == 0 ) {
            $newStatus = $this->__alterStatus($oData['extern_id'], $oData['status_id'], $this->status['invalid'], 'invalid address');
            return $newStatus;
        }

        //if ($this->__isPostAddress($oData) && $oData['orderShippingProfile'])

        return $this->status['valid'];
    }

    private function __isPostAddress ($oData) {
        $address1 = strtolower($oData['DeliveryAddress1']);
        $templates = ['postfach', 'packstation', 'post fach', 'Postfiliale', 'Postnummer'];

        foreach ($templates as $temp) {
            if (strpos($address1, $temp) !== false) {
                return true;
            }
        }
        return false;
    }

    private function __checkWeight ($oData) {
        $sum = array_sum($oData['VariationWeight']);
        if ($sum > 31000) {
            $newStatus = $this->__alterStatus($oData['extern_id'], $oData['status_id'], $this->status['invalid'], 'invalid weight');
            return $newStatus;
        }
        return $this->status['valid'];
    }

    private function __alterStatus ($order_id, $old_status, $new_status, $reason="") {
        $data = $this->Rest->callAPI('put', 'rest/orders/' . $order_id, [
            'statusId' => $new_status
        ]);
        $data = json_decode($data);
        if ($data->statusId != $new_status) {
            $order = $this->Order->findByExternId($order_id);
            $this->Order->save([
                'id' => $order['Order']['id'],
                'status_id' => $data->statusId,
                'imported' => date('Y-m-d H:i:s')
            ]);
            $new_status = $data->statusId;
            $reason = "unkown status";
        }

        $user = ($this->username) ? $this->username : 'sys';
        $this->AlterOrderStatusLog->save([
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'reason' => ($reason) ? $reason : null,
            'changed' => date('Y-m-d H:i:s'),
            'changed_by' => $user
        ]);
        return $new_status;
    }
}