<?php

/**
 * Created by PhpStorm.
 * User: benyingz
 * Date: 10.07.2019
 * Time: 11:03
 */
class ImportVariationPropertiesController extends AppController
{
    var $uses = [
        'ImportItemProperty'
    ];

    var $restAdress = [
        'variations' => 'rest/items/variations?with=variationProperties',
    ];

    function getImportItemPropertiesList () {
        $this->checkLogin();
        $params = $this->request->data;
        $conditions = [];

        if ($params['status'] > 0) {
            $conditions['status'] = $params['status'];
        }

        if ($params['itemId'] > 0) {
            $conditions['item_id'] = $params['itemId'];
        }

        if (isset($params['from']) && $params['from']) {
            $conditions['created >= '] = $params['from'] . ' 00:00:00';
        }
        if (isset($params['to']) && $params['to']) {
            $conditions['created <= '] = $params['to'] . ' 23:59:59';
        }

        $total = $this->ImportItemProperty->find('count', [
            'conditions' => $conditions
        ]);

        $data = $this->ImportItemProperty->find('all', [
            'conditions' => $conditions,
            'order' => ['ImportItemProperty.created DESC, ImportItemProperty.item_id, ImportItemProperty.property_id'],
            'page' => $params['page'],
            'limit' => $params['limit']
        ]);

        return [
            'data' => $data,
            'total' => $total
        ];
    }


    public function importItemPropertiesCsv() {
        $this->checkLogin();
        $file = $this->request->params['form']['fileToUpload'];
        $row = 0;
        $now = date('Y-m-d H:i:s');
        if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
            $propertyIds = [];
            while (($data = fgetcsv($handle, 20480, "~")) !== FALSE) {
                $num = count($data);
                if ($num > 5) {
                    $row++;
                    if ($row === 1) {
                        for ($i=5; $i<$num; $i++) {
                            $propertyIds[] = substr($data[$i], strpos($data[$i], '%')+1);
                        }
                    } else {
                        $itemId = $data[2];
                        $variationId = $data[3];
                        $lang = $data[4];
                        for ($i=5; $i<$num; $i++) {
                            $propertyId = $propertyIds[$i-5];
                            $value = $data[$i];
                            $this->ImportItemProperty->updateAll(
                                [
                                    'status' => 4,
                                    'modified' => '"'.$now.'"'
                                ],
                                [
                                    'item_id' => $itemId,
                                    'variation_id' => $variationId,
                                    'property_id' => $propertyId,
                                    'lang' => $lang,
                                    'status' => 1
                                ]
                            );
                            $this->ImportItemProperty->create();
                            $this->ImportItemProperty->save([
                                'item_id' => $itemId,
                                'variation_id' => $variationId,
                                'property_id' => $propertyId,
                                'lang' => $lang,
                                'value' => $value,
                                'status' => 1,
                                'created' => $now,
                                'modified' => $now
                            ]);
                        }
                    }
                }
            }
            fclose($handle);
        }
        return $row-1;
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
        $successCount = 0;
        $failedCount = 0;
        $losVariationIds = [];

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
                'value' => $data['value'],
                'importId' => $data['id']
            ];

            if (!in_array($data['property_id'], $propertyIds)) {
                $propertyIds[] = $data['property_id'];
            }
        }
//        GlbF::printArray($variations);

        if ($variations) {
            $url = $this->restAdress['variations'];
            $data = $this->Rest->callAPI('GET', $url, ['id' => implode(',', array_keys($variations)) ]);
            $restVariations = json_decode($data)->entries;
            $varProps = [];
            foreach ($restVariations as $variation) {
                $variationId = $variation->id;
                $itemId = $variation->itemId;
                if ($variation->variationProperties) {
                    foreach ($variation->variationProperties as $prop) {
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

            //check, if items is not deleted
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

            //GlbF::printArray($varProps);

            //check post or put
            $postData = [];
            $putData = [];
            foreach ($varProps as $variationId => $varData) {
                $hasPropIds = array_keys($varData);
                foreach ($variations[$variationId] as $propertyId => $imVarProp) {
                    if (in_array($propertyId, $hasPropIds)) {
                        $oldPropData = $varData[$propertyId];
                        $valueId = $oldPropData['valueId'];
                        $imData = [
                            "variationId" => $variationId,
                            "propertyId" => $propertyId
                        ];
                        foreach ($imVarProp as $lang => $propData) {
                            $itemId = $propData['itemId'];
                            $imData['itemId'] = $itemId;
                            $imData['importId'] = $propData['importId'];
                            $value = $propData['value'];
                            if (strtolower($value) == "leer") {
                                $value = "-";
                            }
                            if ($value !== "") {
                                $imData['valueTexts'][] = [
                                    'valueId' => $valueId,
                                    'lang' => $lang,
                                    'value' => $value
                                ];
//                            } else {
//                                if (isset($oldPropData['values'][$lang]) && $oldPropData['values'][$lang] != "") {
//                                    $delUrl = "rest/items/{$itemId}/variations/{$variationId}/variation_properties/{$valueId}/texts/$lang";
//                                    $result = $this->Rest->callAPI('delete', $delUrl);
//                                    //echo $result;
//                                    GlbF::printArray(json_decode($result));
//                                }
                            }
                        }
                        $putData[] = $imData;
                    } else {
                        $imData = [
                            "variationId" => $variationId,
                            "propertyId" => $propertyId
                        ];
                        foreach ($imVarProp as $lang => $propData) {
                            $imData['itemId'] = $propData['itemId'];
                            $imData['importId'] = $propData['importId'];
                            $value = $propData['value'];
                            if (strtolower($value) == "leer") {
                                $value = "-";
                            }
                            if ($value !== "") {
                                $imData['valueTexts'][] = [
                                    'lang' => $lang,
                                    'value' => $value
                                ];
                            }
                        }
                        $postData[] = $imData;
                    }
                }
            }

            $url = 'rest/items/variations/variation_properties';
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
                $this->ImportItemProperty->updateAll(
                    [
                        'status' => 2,
                        'imported' => 'now()',
                        'modified' => 'now()'
                    ],
                    [
                        'id' => $imData[$key-1]['importId']
                    ]
                );
            }
            $sucess = count($data->success);
        }

        if (isset($data->failed)) {
            foreach($data->failed as $key => $value) {
                $this->ImportItemProperty->updateAll(
                    [
                        'status' => 3,
                        'modified' => 'now()'
                    ],
                    [
                        'id' => $imData[$key-1]['importId']
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