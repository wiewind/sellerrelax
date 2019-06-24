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
            'order' => 'variation_id, created DESC',
            'limit' => $countImportToPlenty
        ]);

        $variations = [];
        $propertyIds = [];
        foreach ($importData as $data) {
            $data = $data['ImportItemProperty'];
            $variations[$data['variation_id']][$data['property_id']][$data['lang']] = [
                'itemId' => $data['item_id'],
                'variationId' => $data['variation_id'],
                'propertyId' => $data['property_id'],
                'lang' => $data['lang'],
                'value' => $data['value']
             ];

            if (!in_array($data['property_id'], $propertyIds)) {
                $propertyIds[] = $data['property_id'];
            }
        }
//        GlbF::printArray($variations);

        $url = $this->restAdress['variations'];
        $data = $this->Rest->callAPI('GET', $url, ['id' => implode(',', array_keys($variations)) ]);
        $restVariations = json_decode($data)->entries;
        $varProps = [];
        foreach ($restVariations as $variation) {
            $variationId = $variation->id;
            $itemId = $variation->itemId;
            if ($variation->variationProperties) {
                foreach ($variation->variationProperties as $prop) {
//                    $varProps[$variationId][$itemId][$prop->propertyId] = [
//                        'valueId' => $prop->id
//                    ];
                    $propertyId = $prop->propertyId;
                    $valueId = $prop->id;
                    $tmp = [
                        'itemId' => $itemId,
                        'variationId' => $variationId,
                        'propertyId' => $propertyId,
                        'valueId' => $valueId,
                        'values' => []
                    ];
                    if ($prop->names) {
                        foreach ($prop->names as $val) {
                            $tmp['values'][$val->lang] = $val->value;
                        }
                    }
                    $varProps[$variationId][$propertyId] = $tmp;
                }
            } else {
                $varProps[$variationId] = [];
            }
        }
//        GlbF::printArray($varProps);

        //check, if items is not deleted
        $losVariationIds = [];
        if (count($variations) > count($varProps)) {
            $importVariationIds = array_keys($variations);
            $plentyVariationIds = array_keys($varProps);
            foreach ($importVariationIds as $variationId) {
                if (!in_array($variationId, $plentyVariationIds)) {
                    $losVariationIds[] = $variationId;
                    $this->ImportItemProperty->updateAll(
                        [
                            'status' => 5,
                            'modified' => 'now()'
                        ],
                        [
                            'variation_id' => $variationId,
                            'status' => 1
                        ]
                    );
                }
            }
        }

        //check post or put
        $postData = [];
        $putData = [];
        foreach ($varProps as $variationId => $varData) {
            $hasPropIds = array_keys($varData);
            foreach ($variations[$variationId] as $properyId => $imVarProp) {
                if (in_array($propertyId, $hasPropIds)) {
                    $oldPropData = $varData[$propertyId];
                    $valueId = $oldPropData['valueId'];
                    $hasLangs = array_keys($oldPropData['values']);

                    $imData = [
                        "variationId" => $variationId,
                        "propertyId" => $propertyId,
                        "valueTexts" => []
                    ];
                    foreach ($imVarProp as $lang => $propData) {
                        $imData['itemId'] = $propData['itemId'];
                        $value = $propData['value'] == "" ? "-" : $propData['value'];
                        $imData['valueTexts'][] = [
                            'valueId' => $valueId,
                            'lang' => $lang,
                            'value' => $value
                        ];
                    }
                    $putData[] = $imData;
                } else {
                    $imData = [
                        "variationId" => $variationId,
                        "propertyId" => $propertyId,
                        "valueTexts" => []
                    ];
                    foreach ($imVarProp as $lang => $propData) {
                        $imData['itemId'] = $propData['itemId'];
                        $value = $propData['value'] == "" ? "-" : $propData['value'];
                        $imData['valueTexts'][] = [
                            'lang' => $lang,
                            'value' => $value
                        ];
                    }
                    $postData[] = $imData;
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
            'countDeletedItems' => count($losVariationIds)
        ];

    }

    private function __afterSaveItemProperties ($result, $imData) {
        $data = json_decode($result);
        $sucess = 0;
        $failed = 0;

//        GlbF::printArray($imData);
//        GlbF::printArray($data);

        if (isset($data->success)) {
            foreach($data->success as $key => $value) {
                $itemId = $imData[$key-1]['itemId'];
                $varationId = $imData[$key-1]['variationId'];
                $propertyId = $imData[$key-1]['propertyId'];
                $valueTexts = $imData[$key-1]['valueTexts'];
                foreach ($valueTexts as $textData) {
                    $lang = $textData;
                    $this->ImportItemProperty->updateAll(
                        [
                            'status' => 2,
                            'imported' => 'now()',
                            'modified' => 'now()'
                        ],
                        [
                            'item_id' => $itemId,
                            'variation_id' => $varationId,
                            'property_id' => $propertyId,
                            'lang' => $lang,
                            'status' => 1
                        ]
                    );
                }
            }
            $sucess = count($data->success);
        }

        if (isset($data->failed)) {
            foreach($data->failed as $key => $value) {
                $itemId = $imData[$key-1]['itemId'];
                $varationId = $imData[$key-1]['variationId'];
                $propertyId = $imData[$key-1]['propertyId'];
                $valueTexts = $imData[$key-1]['valueTexts'];
                foreach ($valueTexts as $textData) {
                    $lang = $textData;
                    $this->ImportItemProperty->updateAll(
                        [
                            'status' => 3,
                            'modified' => 'now()'
                        ],
                        [
                            'item_id' => $itemId,
                            'variation_id' => $varationId,
                            'property_id' => $propertyId,
                            'lang' => $lang,
                            'status' => 1
                        ]
                    );
                }
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

            $d = print_r($imData, 1);

            $Email->viewVars(array(
                'url' => itemProperty2Plenty,
                'err' =>$err . '<br />' . $d,
                'params' => $imData
            ));
            $Email->send();
        }

        return [$sucess, $failed];
    }
}