<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 13.08.2018
 * Time: 10:17
 */
class ImportController extends AppController
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
    var $components = ['MySession', 'MyCookie', 'Rest.Rest'];

    var $version = '1.02';

    var $restAdress = [
        'orders' => 'rest/orders/?with[]=orderItems.variation&with[]=orderItems.variationBarcodes',
        'items' => 'rest/items',
        'variations' => 'rest/items/variations?with=variationBarcodes,variationSalesPrices',
        'units' => 'rest/items/units',
        'barcode_types' => 'rest/items/barcodes'
    ];

    function makeNewImport ($type = 'orders', $importId = 0) {

        if ($importId > 0) {
            $lastOrdersImport = $this->Import->findById($importId);
            return [
                'type' => $lastOrdersImport['Import']['type'],
                'from' => $lastOrdersImport['Import']['update_from'],
                'to' => $lastOrdersImport['Import']['update_to'],
                'page' => $lastOrdersImport['Import']['page'],
                'install' => $lastOrdersImport['Import']['update_from'] <= 0
            ];
        }

        $lastOrdersImport = $this->Import->find('first', [
            'conditions' => [
                'type' => $type
            ],
            'order' => 'id desc'
        ]);

        $update_from = '';
        $update_to = date('Y-m-d H:i:s');
        $page = 1;

        if ($lastOrdersImport) {
            if (!$lastOrdersImport['Import']['import_end']) {
                if (strtotime($lastOrdersImport['Import']['import_beginn']) >= strtotime('-3 minute')) {
                    return false;
                }
            }

            if (!$lastOrdersImport['Import']['is_last_page'] && !$lastOrdersImport['Import']['last_page_no']) {
                CakeLog::write('import',  "***********************  reimport ". $lastOrdersImport['Import']['page'] ."  **************************");
                $page = $lastOrdersImport['Import']['page'];
                $update_from = $lastOrdersImport['Import']['update_from'];
                $update_to = $lastOrdersImport['Import']['update_to'];
            } else if ($lastOrdersImport['Import']['last_page_no'] > $lastOrdersImport['Import']['page']) {
                $page = $lastOrdersImport['Import']['page'] + 1;
                $update_from = $lastOrdersImport['Import']['update_from'];
                $update_to = $lastOrdersImport['Import']['update_to'];
            } else {
                $update_from = $lastOrdersImport['Import']['update_to'];
            }
        }

        return [
            'type' => $type,
            'from' => $update_from,
            'to' => $update_to,
            'page' => $page,
            'install' => $update_from <= 0
        ];
    }

    function importOrdersOnce () {
        $this->autoRender = false;
        ini_set("memory_limit","1024M");
        $newImport = $this->makeNewImport('orders');

        if ($newImport === false) return;

        $newImport['itemsPerPage'] = Configure::read('system.rest.limitPerImport');

        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        if ($newImport['from']) {
            $from = GlbF::date2Iso($newImport['from']);
            if ($newImport['install']) {
                $params['createdAtFrom'] = $from;
            } else {
                $params['updatedAtFrom'] = $from;
            }
        }
        if ($newImport['to']) {
            $to = GlbF::date2Iso($newImport['to']);
            if ($newImport['install']) {
                $params['createdAtTo'] = $to;
            } else {
                $params['updatedAtTo'] = $to;
            }
        }

        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import orders beginn...");
        CakeLog::write('import', "between: " . $newImport['from'] . " - " . $newImport['to'] . "; " . "page: " . $newImport['page']);

        $importData = [
            'type' => 'orders',
            'update_from' => (isset($newImport['from'])) ? $newImport['from'] : '',
            'update_to' => $newImport['to'],
            'page' =>  $newImport['page'],
            'import_beginn' => $now,
            'version' => $this->version,
            'url' => $_SERVER['SCRIPT_URI'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        $this->Import->create();
        $this->Import->save($importData);
        $saveImportId = $this->Import->getLastInsertID();

        $data = $this->Rest->callAPI('GET', $this->restAdress['orders'], $params);


        $data = json_decode($data);

        $orders = $data->entries;

        $dataSource = $this->Order->getDataSource();
        $errorOrders = [];
        foreach ($orders as $order) {

            $dataSource->begin();
            try {
                $oData = [
                    'extern_id'     => $order->id + 0,
                    'plenty_id'     => ($order->plentyId) ? $order->plentyId : 0,
                    'location_id'   => ($order->locationId) ? $order->locationId : 0,
                    'owner_id'      => ($order->ownerId) ? $order->ownerId : 0,
                    'type_id'       => ($order->typeId) ? $order->typeId : 0,
                    'status_id'     => ($order->statusId) ? $order->statusId : 0,
                    'created'       => GlbF::iso2Date($order->createdAt),
                    'deleted'       => $order->deletedDate > 0,
                    'updated'       => GlbF::iso2Date($order->updatedAt),
                    'imported'      => $now
                ];
                foreach ($order->relations as $relation) {
                    if ($relation->relation === 'receiver' && $relation->referenceType === 'contact') {
                        $oData['customer_id'] = $relation->referenceId;
                        break;
                    }
                }
                if (isset($order->amounts[0])) {
                    $oData = array_merge($oData, [
                        'gross_total'       => ($order->amounts[0]->grossTotal) ? $order->amounts[0]->grossTotal : 0,
                        'invoice_total'     => ($order->amounts[0]->invoiceTotal) ? $order->amounts[0]->invoiceTotal : 0,
                        'net_total'         => ($order->amounts[0]->netTotal) ? $order->amounts[0]->netTotal : 0,
                        'vat_total'         => ($order->amounts[0]->vatTotal) ? $order->amounts[0]->vatTotal : 0,
                        'currency'          => ($order->amounts[0]->currency) ? $order->amounts[0]->currency : 'EUR',
                        'exchange_rate'      => ($order->amounts[0]->exchangeRate) ? $order->amounts[0]->exchangeRate : 1
                    ]);
                }
                if ($order->dates) {
                    foreach ($order->dates as $od) {
                        switch ($od->typeId) {
                            case 2:
                                $oData['enty_date'] = GlbF::iso2Date($od->date);
                                break;
                            case 3:
                                $oData['payment_date'] = GlbF::iso2Date($od->date);
                                break;
                            case 7:
                                $oData['payment_due_date'] = GlbF::iso2Date($od->date);
                                break;
                            case 5:
                                $oData['shipping_date'] = GlbF::iso2Date($od->date);
                                break;
                        }
                    }
                }
                if ($order->addressRelations) {
                    foreach ($order->addressRelations as $ar) {
                        switch ($ar->typeId) {
                            case 1:
                                $oData['billing_address_id'] = $ar->addressId;
                                break;
                            case 2:
                                $oData['delivery_address_id'] = $ar->addressId;
                                break;
                        }
                    }
                }

                $dbOrder = $this->Order->find('first', [
                    'fields' => 'id',
                    'conditions' => [
                        'extern_id' => $oData['extern_id']
                    ]
                ]);
                if ($dbOrder) {
                    $oData['id'] = $dbOrder['Order']['id'];
                } else {
                    $this->Order->create();
                }

                $this->Order->save($oData);

                $this->OrderItem->deleteAll(['order_id' => $order->id]);
                foreach ($order->orderItems as $orderItem) {
                    $oiData = [
                        'extern_id'             => $orderItem->id + 0,
                        'order_id'              => $orderItem->orderId + 0,
                        'item_variation_id'     => ($orderItem->itemVariationId) ? $orderItem->itemVariationId : 0,
                        'item_id'               => (isset($orderItem->variation)) ? $orderItem->variation->itemId : 0,
                        'type_id'               => ($orderItem->typeId) ? $orderItem->typeId : 0,
                        'quantity'              => ($orderItem->quantity) ? $orderItem->quantity : 0,
                        'var_rate'              => ($orderItem->vatRate) ? $orderItem->vatRate : 0,
                        'warehouse_id'          => ($orderItem->warehouseId) ? $orderItem->warehouseId : 0,
                        'referrer_id'           => $orderItem->referrerId,
                        'updated_at'            => GlbF::iso2Date($orderItem->updatedAt),
                        'imported'              => $now
                    ];
                    if (isset($orderItem->amounts[0])) {
                        $oiData = array_merge($oiData, [
                            'price_gross'           => ($orderItem->amounts[0]->priceGross) ? $orderItem->amounts[0]->priceGross : 0,
                            'price_net'             => ($orderItem->amounts[0]->priceNet) ? $orderItem->amounts[0]->priceNet : 0,
                            'price_original_gross'  => ($orderItem->amounts[0]->priceOriginalGross) ? $orderItem->amounts[0]->priceOriginalGross : 0,
                            'price_original_net'    => ($orderItem->amounts[0]->priceOriginalNet) ? $orderItem->amounts[0]->priceOriginalNet : 0,
                            'purchase_price'        => ($orderItem->amounts[0]->purchasePrice) ? $orderItem->amounts[0]->purchasePrice : 0,
                            'exchange_rate'         => ($orderItem->amounts[0]->exchangeRate) ? $orderItem->amounts[0]->exchangeRate : 1,
                            'currency'              => ($orderItem->amounts[0]->currency) ? $orderItem->amounts[0]->currency : 'EUR'
                        ]);
                    }

                    $this->OrderItem->create();
                    $this->OrderItem->save($oiData);
                }
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $errorOrders[$order->id] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }

        $now2 = date('Y-m-d H:i:s');
        $mengeOfPage = ($data->lastOnPage) ? $data->lastOnPage - $data->firstOnPage + 1 : 0;

        if ($errorOrders) {
            $logStr = "Import Errors: ";
            foreach ($errorOrders as $oid => $err) {
                $logStr .= "\t" . $oid . " (" . $err['code'] . ") " . $err['message'] . "\n";
            }
            CakeLog::write('import', $logStr);
        }

        $importData = [
            'id' => $saveImportId,
            'menge' => $mengeOfPage,
            'is_last_page' => $data->isLastPage,
            'last_page_no' => $data->lastPageNumber,
            'total' => $data->totalsCount,
            'errors' => json_encode($errorOrders),
            'import_end' => $now2
        ];
        $this->Import->save($importData);

        CakeLog::write('import', "Import orders end with $mengeOfPage record(s)!");

        return $importData;
    }

    function importItems () {
        $this->autoRender = false;
        ini_set("memory_limit","1024M");

        $newImport = $this->makeNewImport('items');
        if ($newImport['install'] && $newImport['page'] === 1) {
            $this->Item->query('TRUNCATE TABLE items;');
        }

        if ($newImport === false) return;

        $newImport['itemsPerPage'] = 100;

        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        if ($newImport['from']) {
            $from = GlbF::date2Iso($newImport['from']);
            if ($newImport['install']) {
                $params['createdAtFrom'] = $from;
            } else {
                $params['updatedAtFrom'] = $from;
            }
        }
        if ($newImport['to']) {
            $to = GlbF::date2Iso($newImport['to']);
            if ($newImport['install']) {
                $params['createdAtTo'] = $to;
            } else {
                $params['updatedAtTo'] = $to;
            }
        }

        $now = date('Y-m-d H:i:s');

        CakeLog::write('import', "Import items beginn...");
        CakeLog::write('import', "between: " . $newImport['from'] . " - " . $newImport['to'] . "; " . "page: " . $newImport['page']);

        $importData = [
            'type' => 'items',
            'update_from' => (isset($newImport['from'])) ? $newImport['from'] : '',
            'update_to' => $newImport['to'],
            'page' =>  $newImport['page'],
            'import_beginn' => $now,
            'version' => $this->version
        ];
        $this->Import->create();
        $this->Import->save($importData);
        $importData['id'] = $this->Import->getLastInsertID();

        $data = $this->Rest->callAPI('GET', $this->restAdress['items'], $params);

        $data = json_decode($data);

        $items = $data->entries;

        $errorOrders = [];
        foreach ($items as $item) {
            try {

                $this->__doImportItemData($item);

            } catch (Exception $e) {
                $errorOrders[$item->id] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }
        $mengeOfPage = ($data->lastOnPage) ? $data->lastOnPage - $data->firstOnPage + 1 : 0;

        if ($errorOrders) {
            $logStr = "Import Errors: ";
            foreach ($errorOrders as $oid => $err) {
                $logStr .= "\t" . $oid . " (" . $err['code'] . ") " . $err['message'] . "\n";
            }
            CakeLog::write('import', $logStr);
        }

        $importData = array_merge($importData, [
            'menge' => $mengeOfPage,
            'is_last_page' => $data->isLastPage,
            'last_page_no' => $data->lastPageNumber,
            'total' => $data->totalsCount,
            'errors' => json_encode($errorOrders),
            'import_end' => date('Y-m-d H:i:s')
        ]);
        $this->Import->save($importData);

        CakeLog::write('import', "Import items end with $mengeOfPage record(s)!");

        return $importData;
    }

    private function __doImportItemData ($item) {
        $data = [
            'extern_id' => $item->id + 0,
            'manufacturer_id' => $item->manufacturerId + 0,
            'main_variation_id' => $item->mainVariationId + 0,
            'imported' => date('Y-m-d H:i:s')
        ];
        $name = '';
        $desc = '';
        foreach ($item->texts as $txt) {
            if ($txt->lang === 'de') {
                $name = $txt->name1;
                $desc = $txt->description;
                break;
            } else if (!$name) {
                $name = $txt->name1;
                $desc = $txt->description;
            }
        }
        if ($name) {
            $data['name'] = $name;
            $data['description'] = $desc;
        }

        $d = $this->Item->find('first',  [
            'fields' => 'id',
            'conditions' => [
                'extern_id' => $data['extern_id']
            ]
        ]);
        if ($d) {
            $data['id'] = $d['Item']['id'];
        } else {
            $this->Item->create();
        }
        $this->Item->save($data);
    }

    function importVariations () {
        $this->autoRender = false;
        ini_set("memory_limit","1024M");

        $newImport = $this->makeNewImport('variations');
        if ($newImport['page'] === 1) {
            $this->Item->query('TRUNCATE TABLE items_variations;');
            $this->Item->query('TRUNCATE TABLE items_variations_barcodes;');
        }

        if ($newImport === false) return;

        $newImport['itemsPerPage'] = 100;

        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        if ($newImport['from']) {
            $from = GlbF::date2Iso($newImport['from']);
            if ($newImport['install']) {
                $params['createdAtFrom'] = $from;
            } else {
                $params['updatedAtFrom'] = $from;
            }
        }
        if ($newImport['to']) {
            $to = GlbF::date2Iso($newImport['to']);
            if ($newImport['install']) {
                $params['createdAtTo'] = $to;
            } else {
                $params['updatedAtTo'] = $to;
            }
        }

        $now = date('Y-m-d H:i:s');

        CakeLog::write('import', "Import variations beginn...");
        CakeLog::write('import', "between: " . $newImport['from'] . " - " . $newImport['to'] . "; " . "page: " . $newImport['page']);

        $importData = [
            'type' => 'variations',
            'update_from' => (isset($newImport['from'])) ? $newImport['from'] : '',
            'update_to' => $newImport['to'],
            'page' =>  $newImport['page'],
            'import_beginn' => $now,
            'version' => $this->version
        ];

        $this->Import->create();
        $this->Import->save($importData);
        $importData['id'] = $this->Import->getLastInsertID();

        $data = $this->Rest->callAPI('GET', $this->restAdress['variations'], $params);

        $data = json_decode($data);

        $items = $data->entries;

        $errorOrders = [];
        foreach ($items as $variation) {
            try {

                $this->__doImportVariationData($variation);

            } catch (Exception $e) {
                $errorOrders[$variation->id] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }
        $mengeOfPage = ($data->lastOnPage) ? $data->lastOnPage - $data->firstOnPage + 1 : 0;

        if ($errorOrders) {
            $logStr = "Import Errors: ";
            foreach ($errorOrders as $oid => $err) {
                $logStr .= "\t" . $oid . " (" . $err['code'] . ") " . $err['message'] . "\n";
            }
            CakeLog::write('import', $logStr);
        }

        $importData = array_merge($importData, [
            'menge' => $mengeOfPage,
            'is_last_page' => $data->isLastPage,
            'last_page_no' => $data->lastPageNumber,
            'total' => $data->totalsCount,
            'errors' => json_encode($errorOrders),
            'import_end' => date('Y-m-d H:i:s')
        ]);
        $this->Import->save($importData);

        CakeLog::write('import', "Import variations end with $mengeOfPage record(s)!");

        return $importData;
    }

    private function __doImportVariationData ($var) {
        $data = [
            'extern_id' => $var->id + 0,
            'item_id' => $var->itemId + 0,
            'warehouse_variation_id' => $var->warehouseVariationId + 0,
            'height' => $var->heightMM + 0,
            'length' => $var->lengthMM + 0,
            'width' => $var->widthMM + 0,
            'weight' => $var->weightG + 0,
            'weight_net' => $var->weightNetG + 0,
            'unit_combination_id' => $var->unitCombinationId + 0,
            'model' => $var->model,
            'number' => $var->number,
            'picking' => $var->picking,
            'updated_at' => GlbF::iso2Date($var->updatedAt),
            'imported' => date('Y-m-d H:i:s')
        ];
        $this->ItemsVariation->create();
        $this->ItemsVariation->save($data);
        $newVariationId = $this->ItemsVariation->getLastInsertID();

        if ($var->variationBarcodes)
        foreach ($var->variationBarcodes as $barcode) {
            $bcd = [
                'variation_id' => $barcode->variationId ? $barcode->variationId + 0 : $var->id + 0,
                'barcode_type_id' => $barcode->barcodeId + 0,
                'code' => $barcode->code,
            ];
            $this->ItemsVariationsBarcode->create();
            $this->ItemsVariationsBarcode->save($bcd);
        }

    }

    function importOrdersFull () {
        $importStep = 0;
        $sum = 0;
        do {
            $data = $this->importOrdersOnce();
            $sum += $data['menge'];
            $importStep++;
        } while ($importStep < 2 && !$data['is_last_page']);
        return "import summe: " . $sum;
    }

    function importItemsAll () {
        $sum = 0;
        do {
            $data = $this->importItems();
            $sum += $data['menge'];
            echo "Page {$data['page']} with {$data['menge']} items.";
            if ($data['is_last_page']) {
                echo '<p style="color: green">All items are finished!</p>';
            } else {
                echo " continue...<br>";
            }
        } while (!$data['is_last_page']);
        return "import summe: " . $sum;
    }

    function importVariationsAll () {
        $sum = 0;
        do {
            $data = $this->importVariations();
            $sum += $data['menge'];
            echo "Page {$data['page']} with {$data['menge']} variations.";
            if ($data['is_last_page']) {
                echo '<p style="color: green">All variations are finished!</p>';
            } else {
                echo " continue...<br>";
            }
        } while (!$data['is_last_page']);
        return "import summe: " . $sum;
    }

    function importUnits () {
        $this->autoRender = false;
        $this->Unit->query('TRUNCATE TABLE units;');
        CakeLog::write('import', "Import Units beginn...");
        $params = [
            'page' => 1,
            'itemsPerPage' => 1000
        ];
        $data = $this->Rest->callAPI('GET', $this->restAdress['units'], $params);
        $data = json_decode($data);
        $recs = $data->entries;
        $now = date('Y-m-d H:i:s');
        foreach ($recs as $u) {
            $d = [
                'extern_id' => $u->id,
                'is_decimal_places_allowed' => $u->isDecimalPlacesAllowed,
                'unit_of_measurement' => $u->unitOfMeasurement,
                'position' => $u->position,
                'imported' => $now
            ];
            foreach ($u->names as $name) {
                switch ($name->lang) {
                    case 'de':
                        $d['name_de'] = $name->name;
                        break;
                    case 'en':
                        $d['name_en'] = $name->name;
                        break;
                }
            }
            $this->Unit->create();
            $this->Unit->save($d);
        }
        $menge = ($data->lastOnPage) ? $data->lastOnPage - $data->firstOnPage + 1 : 0;
        CakeLog::write('import', "Import Units end with $menge record(s)!");
    }

    function importBarcodeTypes () {
        $this->autoRender = false;
        $this->Unit->query('TRUNCATE TABLE barcode_types;');
        CakeLog::write('import', "Import Barcode Types beginn...");
        $params = [
            'page' => 1,
            'itemsPerPage' => 1000
        ];
        $data = $this->Rest->callAPI('GET', $this->restAdress['barcode_types'], $params);
        $data = json_decode($data);
        $recs = $data->entries;
        $now = date('Y-m-d H:i:s');
        foreach ($recs as $u) {
            $d = [
                'id' => $u->id,
                'name' => $u->name,
                'type' => $u->type,
                'imported' => $now
            ];
            $this->BarcodeType->create();
            $this->BarcodeType->save($d);
        }
        $menge = ($data->lastOnPage) ? $data->lastOnPage - $data->firstOnPage + 1 : 0;
        CakeLog::write('import', "Import Barcode Types end with $menge record(s)!");
    }
}