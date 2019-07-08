<?php

/**
 * Created by PhpStorm.
 * User: benyingz
 * Date: 02.07.2019
 * Time: 11:48
 */
class ImportVariationSuppliersController extends AppController
{
    var $uses = [
        'Item',
        'Variation',
        'ImportVariationSupplier'
    ];



    var $restAdress = [
        'variations' => 'rest/items/variations?with=variationSuppliers'
    ];

    public function listAll () {
        $this->checkLogin();
        $params = $this->request->data;
        $conditions = [];

        if ($params['status'] > 0) {
            $conditions['status'] = $params['status'];
        }

        if ($params['itemId'] > 0) {
            $conditions['item_id'] = $params['itemId'];
        }

        if ($params['variationId'] > 0) {
            $conditions['variation_id'] = $params['variationId'];
        }

        if (isset($params['from']) && $params['from']) {
            $conditions['created >= '] = $params['from'] . ' 00:00:00';
        }
        if (isset($params['to']) && $params['to']) {
            $conditions['created <= '] = $params['to'] . ' 23:59:59';
        }

        $total = $this->ImportVariationSupplier->find('count', [
            'conditions' => $conditions
        ]);

        $data = $this->ImportVariationSupplier->find('all', [
            'conditions' => $conditions,
            'order' => ['ImportVariationSupplier.created DESC, ImportVariationSupplier.item_id, ImportVariationSupplier.variation_id, ImportVariationSupplier.supplier_id'],
            'page' => $params['page'],
            'limit' => $params['limit']
        ]);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    public function importCsv () {
        $this->checkLogin();
        $file = $this->request->params['form']['fileToUpload'];
        $row = 0;
        $now = date('Y-m-d H:i:s');
        if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
            $propertyIds = [];
            while (($data = fgetcsv($handle, 20480, ";")) !== FALSE) {
                $num = count($data);
                $row++;
                if ($row > 1) {
                    $variationId = $data[2];
                    $supplierId = $data[3];

                    $this->ImportVariationSupplier->updateAll(
                        [
                            'status' => 4
                        ],
                        [
                            'variation_id' => $variationId,
                            'supplier_id' => $supplierId,
                            'status' => 1
                        ]
                    );
                    $this->ImportVariationSupplier->create();
                    $this->ImportVariationSupplier->save([
                        'item_id' => $data[1],
                        'variation_id' => $variationId,
                        'supplier_id' => $supplierId,
                        'item_no' => $data[0],
                        'supplier_item_no' => $data[4],
                        'min_purchase' => $data[5],
                        'purchase_price' => $data[6],
                        'delivery_time' => $data[7],
                        'packaging_unit' => $data[8],
                        'free20' => $data[9],
                        'status' => 1,
                        'created' => $now
                    ]);
                }
            }
            fclose($handle);
        }
        return $row-1;
    }

    public function import2Plenty () {
        $vsData = $this->ImportVariationSupplier->find('all', [
            'conditions' => [
                'status' => 1
            ],
            'order' => 'created desc',
            'page' => 1,
            'limit' => 50
        ]);

        $importCount = count($vsData);

        if ($importCount == 0) {
            return $importCount;
        }

        $variations = [];
        foreach ($vsData as $vs) {
            $variations[$vs['ImportVariationSupplier']['variation_id']][$vs['ImportVariationSupplier']['supplier_id']][$vs['ImportVariationSupplier']['min_purchase']] = $vs['ImportVariationSupplier'];
        }

        // 1. check, if variation exist => yes: set id; no: nothing
        $url = $this->restAdress['variations'];
        $data = $this->Rest->callAPI('GET', $url, ['id' => implode(',', array_keys($variations)) ]);
        $restVariations = json_decode($data)->entries;

        foreach($restVariations as $var) {
            $variationId = $var->id;
            foreach ($var->variationSuppliers as $vs) {
                $supplierId = $vs->supplierId;
                $minimumPurchase = $vs->minimumPurchase;
                if (isset($variations[$variationId][$supplierId][$minimumPurchase])) {
                    $variations[$variationId][$supplierId][$minimumPurchase]['vsid'] = $vs->id;
                }
            }
        }

        //2. write in Plenty

        //2.1 update Free20
        $freeData = [];
        foreach ($vsData as $vs) {
            $vs = $vs['ImportVariationSupplier'];
            $freeData[] = [
                'id' => $vs['item_id'],
                'free20' => $vs['free20']
            ];
        }
        $result = $this->Rest->callAPI('put', 'rest/items', $freeData);


        //2.2 update variation suppliers
        foreach ($variations as $variationId => $vData) {
            foreach ($vData as $supplierId => $vsDataAll) {
                foreach ($vsDataAll as $minPurchase => $vsData) {
                    $url = '/rest/items/'.$vsData['item_id'].'/variations/'.$variationId.'/variation_suppliers';
                    $methode = 'post';
                    $importData = [
                        "variationId" => $variationId,
                        "supplierId" => $supplierId,
                        "purchasePrice" => $vsData['purchase_price'],
                        "minimumPurchase" => $vsData['min_purchase'],
                        "itemNumber" => $vsData['supplier_item_no'],
                        "lastPriceQuery" => null,
                        "deliveryTimeInDays" => $vsData['delivery_time'],
                        "discount" => 0,
                        "isDiscountable" => false,
                        "packagingUnit" => $vsData['packaging_unit']
                    ];
                    if (isset($vsData['vsid'])) {
                        $url .= '/'.$vsData['vsid'];
                        $methode = 'put';
                        $importData['id'] = $vsData['vsid'];
                    }
                    $result = $this->Rest->callAPI($methode, $url, $importData);
                    $this->__afterSaveItemProperties($result, $importData);
                }
            }
        }

        return $importCount;
    }

    private function __afterSaveItemProperties ($result, $imData) {
        $result = json_decode($result);

        if (isset($result->error)) {
            // error
            $this->ImportVariationSupplier->updateAll(
                [
                    'status' => 3
                ],
                [
                    'variation_id' => $imData['variationId'],
                    'supplier_id' => $imData['supplierId'],
                    'status' => 1
                ]
            );
        } else {
            // erledigt
            $this->ImportVariationSupplier->updateAll(
                [
                    'status' => 2,
                    'imported' => 'now()',
                ],
                [
                    'variation_id' => $imData['variationId'],
                    'supplier_id' => $imData['supplierId'],
                    'status' => 1
                ]
            );
        }
    }
}