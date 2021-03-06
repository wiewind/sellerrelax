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
        'AlterOrderStatusLog',
        'Address',
        'AddressOption'
    ];

    var $components = ['MySession', 'MyCookie', 'Rest'];

    var $status = [
        'valid' => 5.05,
        'invalid' => 4.9
    ];

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
                'Order.delivery_address_id',
                'DeliveryAddress.address1',
                'DeliveryAddress.address2',
                'DeliveryAddress.address3',
                'DeliveryAddress.address4',
                'DeliveryAddress.name1',
                'DeliveryAddress.name2',
                'DeliveryAddress.name3',
                'DeliveryAddress.name4',
                'DeliveryPostnumber.value',
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
                ),
                array(
                    'table' => Inflector::tableize('AddressOption'),
                    'alias' => 'DeliveryPostnumber',
                    'conditions' => array(
                        'Order.delivery_address_id = DeliveryPostnumber.address_id',
                        'DeliveryPostnumber.type_id' => 6
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
                $orders[$d['Order']['extern_id']]['DeliveryAddress3']=$d['DeliveryAddress']['address3'];
                $orders[$d['Order']['extern_id']]['DeliveryAddress4']=$d['DeliveryAddress']['address4'];
                $orders[$d['Order']['extern_id']]['DeliveryName1']=$d['DeliveryAddress']['name1'];
                $orders[$d['Order']['extern_id']]['DeliveryName2']=$d['DeliveryAddress']['name2'];
                $orders[$d['Order']['extern_id']]['DeliveryName3']=$d['DeliveryAddress']['name3'];
                $orders[$d['Order']['extern_id']]['DeliveryName4']=$d['DeliveryAddress']['name4'];
                $orders[$d['Order']['extern_id']]['DeliveryPostnumber']=$d['DeliveryPostnumber']['value'];
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
                    $newStatus = $this->__checkPostAddress($oData);
                }
                if ($newStatus === $this->status['valid']) {
                    $newStatus = $this->__checkDHL($oData);
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

        return $this->status['valid'];
    }

    private function __checkPostAddress ($oData) {
        $checkArray = ['Packstation', 'Postfiliale'];
        if (in_array($oData['DeliveryAddress1'], $checkArray) && (strlen($oData['DeliveryPostnumber']) == 0)) {
            $newStatus = $this->__alterStatus($oData['extern_id'], $oData['status_id'], $this->status['invalid'], 'invalid post number');
            return $newStatus;
        }
        return $this->status['valid'];
    }

    private function __checkDHL ($oData) {
        if (in_array($oData['orderShippingProfile'], [6, 16, 17])) {
            return $this->status['valid'];
        }
        $checkArray = [
            'packstation', 'pack station',
            'postfiliale', 'post filiale',
            'postfach', 'post fach',
            'postnummer', 'post nummer'
        ];
        $adnames = [
            strtolower($oData['DeliveryAddress1']),
            strtolower($oData['DeliveryAddress2']),
            strtolower($oData['DeliveryAddress3']),
            strtolower($oData['DeliveryAddress4']),
            strtolower($oData['DeliveryName1']),
            strtolower($oData['DeliveryName2']),
            strtolower($oData['DeliveryName3']),
            strtolower($oData['DeliveryName4']),
        ];
        foreach ($checkArray as $muster) {
            foreach ($adnames as $an) {
                if (strpos($an, $muster) !== false) {
                    $newStatus = $this->__alterStatus($oData['extern_id'], $oData['status_id'], $this->status['invalid'], 'DHL required');
                    return $newStatus;
                }

            }
        }
        return $this->status['valid'];
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