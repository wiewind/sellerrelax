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
        'Item',
        'ItemsVariation',
        'ItemsVariationsBarcode',
        'ItemVariationProperty',
        'ItemCrossSelling',
        'ItemShippingProfile',
        'ItemProperty',
        'ItemPropertyType',
        'ItemPropertyGroup',
        'ItemPropertyMarketComponent',
        'ItemPropertySelection',
        'BarcodeType',
        'Availability',
        'ImportItemProperty'
    ];

    var $restAdress = [
        'items' => 'rest/items?with=itemProperties',
        'variations' => 'rest/items/variations?with=variationProperties',
        'item_property_groups' => 'rest/items/property_groups',
        'item_property_types' => 'rest/items/properties?with=marketComponents,selections'
    ];

    public function listItems () {
        $this->checkLogin();

        $params = $this->request->data;
        $total = $this->Item->find('count');

        $sortObj = (isset($params['sort'])) ? json_decode($params['sort']) : false;

        $sortColum = ($sortObj) ? $sortObj[0]->property : 'count_orders';
        $sortDirection = ($sortObj) ? $sortObj[0]->direction : (($sortColum === 'count_orders') ? 'DESC' : 'ASC');

        $searchDays = isset($params['searchDays']) ? $params['searchDays'] : 7;

        $sql = "select Item.*, ItemOrderCount.currency as Item__currency, " .
            "ifnull(ItemOrderCount.count_orders, 0) as Item__count_orders, " .
            "ifnull(ItemOrderCount.sum_quantity, 0) as Item__sum_quantity, " .
            "ifnull(ItemOrderCount.sum_price_net, 0) as Item__sum_price_net " .
            "from items Item ".
            "left join ( ".
                "select OrderItem.item_id, 'EUR' as currency, count(OrderItem.order_id) as count_orders, sum(OrderItem.quantity) as sum_quantity, sum(OrderItem.price_net * OrderItem.exchange_rate) as sum_price_net " .
                "from order_items OrderItem ".
                "where OrderItem.type_id = 1 ".
                "and OrderItem.order_id in ( ".
                    "select `Order`.extern_id from orders `Order` where `Order`.type_id = 1 and `Order`.deleted = 0 and `Order`.enty_date >= DATE_SUB(CURDATE(), INTERVAL ".$searchDays." DAY) ".
                ") " .
                "group by OrderItem.item_id ".
            ") ItemOrderCount on ItemOrderCount.item_id = Item.extern_id ";
        if (isset($params['searchText']) && strlen(trim($params['searchText'])) > 0) {
            $sql .= "where Item.name like '%" . trim($params['searchText']) . "%' ";
        }
        $sql .= "order by {$sortColum} {$sortDirection} " .
            "limit " . ($params['limit'] * ($params['page'] - 1)) . ", " . $params['limit'];

        $this->Item->virtualFields['currency'] = 'EUR';
        $this->Item->virtualFields['count_orders'] = 'ItemOrderCount.count_orders';
        $this->Item->virtualFields['sum_quantity'] = 'ItemOrderCount.sum_quantity';
        $this->Item->virtualFields['sum_price_net'] = 'ItemOrderCount.sum_price_net';
        $data = $this->Item->query($sql);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    public function itemProperty2Plenty () {
        for ($i=0; $i<5; $i++) {
            $res = $this->itemProperty2PlentyOneTime();
            $sum = array_sum($res);
            //when nothing in ImportItemProperty, or all imports are failed, return
            if ($sum == 0 || $sum == $res[1]) {
                return;
            }
        }
    }

    public function itemProperty2PlentyOneTime () {
        $countImportToPlenty = 50;
        $importData = $this->ImportItemProperty->find('all', [
            'conditions' => [
                'status' => 1
            ],
            'order' => 'item_id, created DESC',
            'limit' => $countImportToPlenty
        ]);

        $items = [];
        $propertyIds = [];
        foreach ($importData as $data) {
            $data = $data['ImportItemProperty'];
            $items[$data['item_id']][$data['property_id']] = $data['value'];

            if (!in_array($data['property_id'], $propertyIds)) {
                $propertyIds[] = $data['property_id'];
            }
        }

        $url = $this->restAdress['variations'];
        $data = $this->Rest->callAPI('GET', $url, ['itemId' => implode(',', array_keys($items)) ]);
        $variations = json_decode($data)->entries;
        $varProps = [];
        foreach ($variations as $variation) {
            $variationId = $variation->id;
            $itemId = $variation->itemId;
            if ($variation->variationProperties) {
                foreach ($variation->variationProperties as $prop) {
                    $varProps[$itemId][$variationId][$prop->propertyId] = [
                        'valueId' => $prop->id
                    ];
                }
            } else {
                $varProps[$itemId][$variationId] = [];
            }
        }

        //check, if items is not deleted
        $losItemIds = [];
        if (count($items) > count($varProps)) {
            $importItemIds = array_keys($items);
            $plentyItemIds = array_keys($varProps);
            foreach ($importItemIds as $itemId) {
                if (!in_array($itemId, $plentyItemIds)) {
                    $losItemIds[] = $itemId;
                    $this->ImportItemProperty->updateAll(
                        [
                            'status' => 5,
                            'modified' => 'now()'
                        ],
                        [
                            'item_id' => $itemId,
                            'status' => 1
                        ]
                    );
                }
            }
        }

        //check pot or put
        $postData = [];
        $putData = [];
        foreach ($varProps as $itemId => $varData) {
            foreach ($varData as $variationId => $propData) {
                $propIds = array_keys($propData);
                foreach ($items[$itemId] as $propId => $value) {
                    if (isset($propData[$propId])) {
                        // add in put
                        $putData[] = [
                            "itemId" => $itemId,
                            "variationId" => $variationId,
                            "propertyId" => $propId,
                            "valueTexts" => [
                                [
                                    'valueId' => $propData[$propId]['valueId'],
                                    'lang' => 'de',
                                    'value' => $value
                                ]
                            ]
                        ];
                    } else {
                        // add in post
                        $postData[] = [
                            "itemId" => $itemId,
                            "variationId" => $variationId,
                            "propertyId" => $propId,
                            "valueTexts" => [
                                [
                                    'lang' => 'de',
                                    'value' => $value
                                ]
                            ]
                        ];
                    }
                }
            }
        }

        $url = 'rest/items/variations/variation_properties';
        $successCount = 0;
        $failedCount = 0;
        if ($postData) {
            $result = $this->Rest->callAPI('post', $url, $postData);
            list($s,$f) = $this->__afterSaveItemProperties($result, $postData);
            $successCount += $s;
            $failedCount += $f;
        }
        if ($putData) {
            $result = $this->Rest->callAPI('put', $url, $putData);
            list($s,$f) = $this->__afterSaveItemProperties($result, $putData);
            $successCount += $s;
            $failedCount += $f;
        }

        return [
            'ountSuccess' => $successCount,
            'countFailed' => $failedCount,
            'countDeletedItems' => count($losItemIds)
        ];

    }

    private function __afterSaveItemProperties ($result, $imData) {
        $data = json_decode($result);
        $sucess = 0;
        $failed = 0;

        GlbF::printArray($imData);
        GlbF::printArray($data);

        if (isset($data->success)) {
            foreach($data->success as $key => $value) {
                $itemId = $imData[$key-1]['itemId'];
                $propertyId = $imData[$key-1]['propertyId'];
                $this->ImportItemProperty->updateAll(
                    [
                        'status' => 2,
                        'imported' => 'now()',
                        'modified' => 'now()'
                    ],
                    [
                        'item_id' => $itemId,
                        'property_id' => $propertyId,
                        'status' => 1
                    ]
                );
            }
            $sucess = count($data->success);
        }

        if (isset($data->failed)) {
            foreach($data->failed as $key => $value) {
                $itemId = $imData[$key-1]['itemId'];
                $propertyId = $imData[$key-1]['propertyId'];
                $this->ImportItemProperty->updateAll(
                    [
                        'status' => 3,
                        'modified' => 'now()'
                    ],
                    [
                        'item_id' => $itemId,
                        'property_id' => $propertyId,
                        'status' => 1
                    ]
                );
            }
            $failed = count($data->failed);
        }

        if (isset($data->error)) {
            $failed = count($imData) - $sucess - $failed;

            $Email = new CakeEmail();
            $Email->from(Configure::read('system.admin.frommail'));
            $Email->to(Configure::read('system.admin.tomail'));
            $Email->cc(Configure::read('system.dev.email'));

            $Email->subject("Fehler bei Import Item Property!");
            $Email->emailFormat('html');
            $Email->template('resterror');

            if (isset($data->error->message)) {
                $err = $data->error->message;
            } else {
                $err = "unknown error";
            }

            $Email->viewVars(array(
                'url' => itemProperty2Plenty,
                'err' =>$err,
                'params' => $imData
            ));
            $Email->send();
        }

        return [$sucess, $failed];
    }


    // set onece itemProperty
    public function setItemProperties ($itemId, $propertyId, $value) {
        $property = $this->ItemPropertyType->findByExternId($propertyId);
        if (!$property) {
            ErrorCode::throwException(sprintf(__("The PropertyId '%s' is not exist."), $propertyId), ErrorCode::ErrorCodeBadRequest);
        }

        $valueType = $property['ItemPropertyType']['value_type'];

        $url = $this->restAdress['variations'];
        $data = $this->Rest->callAPI('GET', $url, ['itemId' => $itemId]);
        $variations = json_decode($data)->entries;



        $lang = 'de';
        foreach ($variations as $variation) {
            $variationId = $variation->id;
            $hasProperty = false;
            $hasText = false;
            foreach ($variation->variationProperties as $prop) {
                if ($prop->propertyId == $propertyId) {
                    $valueId = $prop->id;

                    if ($valueType == 'text') {
                        foreach ($prop->names as $text) {
                            if ($text->lang == $lang) {
                                $this->__setPropertyText($itemId, $variationId, $propertyId, $valueId, $lang, $value);
                            }
                            $hasText = true;
                        }
                        if (!$hasText) {
                            $this->__addPropertyText($itemId, $variationId, $propertyId, $valueId, $lang, $value);
                        }
                    } else {
                        $url = 'rest/items/'.$itemId.'/variations/'.$variationId.'/variation_properties/'.$propertyId;
                        $inputData = [
                            'variationId' => $variationId,
                            'propertyId' => $propertyId,
                        ];
                        switch ($valueType) {
                            case 'int':
                                $inputData['valueInt'] = $value;
                                break;
                            case 'float':
                                $inputData['valueFloat'] = $value;
                                break;
                            case 'file':
                                $inputData['valueFile'] = $value;
                                break;
                        }
                        $data = $this->Rest->callAPI('put', $url, $inputData);
                    }

                    $hasProperty = true;
                }
            }

            if (!$hasProperty) {
                $url = 'rest/items/'.$itemId.'/variations/'.$variationId.'/variation_properties/';
                if ($valueType == 'text') {
                    $data = $this->Rest->callAPI('post', $url, [
                        'variationId' => $variationId,
                        'propertyId' => $propertyId
                    ]);
                    $this->__addPropertyText($itemId, $variationId, $propertyId, $valueId, $lang, $value);
                } else {
                    $url = 'rest/items/'.$itemId.'/variations/'.$variationId.'/variation_properties/'.$propertyId;
                    $inputData = [
                        'variationId' => $variationId,
                        'propertyId' => $propertyId,
                    ];
                    switch ($valueType) {
                        case 'int':
                            $inputData['valueInt'] = $value;
                            break;
                        case 'float':
                            $inputData['valueFloat'] = $value;
                            break;
                        case 'file':
                            $inputData['valueFile'] = $value;
                            break;
                    }
                    $data = $this->Rest->callAPI('post', $url, $inputData);
                }
            }
        }
    }

    private function __addPropertyText ($itemId, $variationId, $propertyId, $valueId, $lang, $value) {
        $data = $this->Rest->callAPI('post', 'rest/items/'.$itemId.'/variations/'.$variationId.'/variation_properties/'.$propertyId.'/texts', [
            'valueId' => $valueId,
            'lang' => $lang,
            'value' => $value
        ]);
    }

    private function __setPropertyText ($itemId, $variationId, $propertyId, $valueId, $lang, $value) {
        $data = $this->Rest->callAPI('put', 'rest/items/'.$itemId.'/variations/'.$variationId.'/variation_properties/'.$propertyId.'/texts/'.$lang, [
            'valueId' => $valueId,
            'lang' => $lang,
            'value' => $value
        ]);
    }
}