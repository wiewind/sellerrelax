<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 13.08.2018
 * Time: 10:17
 */
class ImportController extends RestAppController
{
    var $restAdress = [
        'orders' => 'rest/orders?with[]=orderItems.variation&with[]=orderItems.variationBarcodes&with[]=addresses',
        'items' => 'rest/items?with=itemCrossSelling,itemShippingProfiles,itemProperties',
        'variations' => 'rest/items/variations?with=variationBarcodes,variationSalesPrices,variationProperties',
        'units' => 'rest/items/units',
        'barcode_types' => 'rest/items/barcodes',
        'availabilities' => 'rest/availabilities',
        'item_property_groups' => 'rest/items/property_groups',
        'item_property_types' => 'rest/items/properties?with=marketComponents,selections'
    ];

    function importOrdersOnce ($newImport=false) {
        $this->autoRender = false;
        $this->checkIP();
        ini_set("memory_limit","1024M");
        $newImport = $this->makeNewImport('orders', 0, $newImport);

        if ($newImport === false) return;

        $newImport['itemsPerPage'] = Configure::read('system.rest.limitPerImport');
        $newImport['itemsPerPage'] = 250;
        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        $withItems = true;
        if ($newImport['from']) {
            $from = GlbF::date2Iso($newImport['from']);
            if ($newImport['install']) {
                $params['createdAtFrom'] = $from;
            } else {
                $params['updatedAtFrom'] = $from;
            }
        } else {
            $withItems = false; // by init database do not update the items
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
        $url = $this->restAdress['orders'];

        $data = $this->callJsonRest($url, $params, 'GET', $saveImportId);

        $orders = $data->entries;

        $errorOrders = [];
        foreach ($orders as $order) {
            $dataSource = $this->Order->getDataSource();
            $dataSource->begin();
            try {
                $this->__doImportOrderData($order, $now, $withItems);
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

    private function __doImportOrderData ($order, $now="", $withItems = false) {
        if (!$now) $now = date('Y-m-d H:i:s');

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

        if ($order->addresses) {
            foreach ($order->addresses as $ar) {
                 $this->Address->doSaveAddress($ar, $now, 0);
            }
        }

        if ($order->orderReferences) {
            $oData['org_order_id'] = $order->orderReferences[0]->originOrderId;
            $oData['ref_order_id'] = $order->orderReferences[0]->referenceOrderId;
            $oData['ref_type'] = $order->orderReferences[0]->referenceType;
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

        // order_items
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
                'shipping_profile_id'   => $orderItem->shippingProfileId + 0,
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
                    'currency'              => ($orderItem->amounts[0]->currency) ? $orderItem->amounts[0]->currency : 'EUR',
                    'discount'              => ($orderItem->amounts[0]->discount) ? $orderItem->amounts[0]->discount : 0,
                ]);
            }

            $this->OrderItem->create();
            $this->OrderItem->save($oiData);

            if (isset($orderItem->properties)) {
                $this->OrderItemProperty->deleteAll(['order_item_id' => $orderItem->id]);
                foreach ($orderItem->properties as $orderItemProperty) {
                    $this->OrderItemProperty->create();
                    $this->OrderItemProperty->save([
                        'extern_id' => $orderItemProperty->id,
                        'order_item_id' => $orderItemProperty->orderItemId,
                        'type_id' => $orderItemProperty->typeId,
                        'value' => $orderItemProperty->value,
                        'created' => GlbF::iso2Date($orderItemProperty->createdAt),
                        'modified' => GlbF::iso2Date($orderItemProperty->updatedAt)
                    ]);
                }
            }

            if (isset($orderItem->references)) {
                $this->OrderItemReference->deleteAll(['order_item_id' => $orderItem->id]);
                foreach ($orderItem->references as $orderItemReference) {
                    $this->OrderItemReference->create();
                    $this->OrderItemReference->save([
                        'extern_id' => $orderItemReference->id,
                        'order_item_id' => $orderItemReference->orderItemId,
                        'reference_type' => $orderItemReference->referenceType,
                        'reference_order_item_id' => $orderItemReference->referenceOrderItemId,
                        'created' => GlbF::iso2Date($orderItemReference->createdAt),
                        'modified' => GlbF::iso2Date($orderItemReference->updatedAt)
                    ]);
                }
            }

            // check Item, when not found, import item!
            if ($withItems && isset($orderItem->variation)) {
                $iv = $this->ItemsVariation->find('first', [
                    'fields' => 'id',
                    'conditions' => [
                        'extern_id' => $orderItem->itemVariationId
                    ]
                ]);
                if (!$iv) {
                    $this->ImportTodo->create();
                    $this->ImportTodo->save([
                        'type' => 'item',
                        'extern_id' => $orderItem->variation->itemId,
                        'created' => $now
                    ]);
                }
            }
        }

        // order_properties
        $this->OrderProperty->deleteAll(['order_id' => $order->id]);
        foreach ($order->properties as $orderProperty) {
            $opData = [
                'order_id' => $orderProperty->orderId + 0,
                'type_id' => $orderProperty->typeId + 0,
                'value' => $orderProperty->value
            ];

            $this->OrderProperty->create();
            $this->OrderProperty->save($opData);
        }
    }

    function importOrdersFull () {
        $importStep = 0;
        $sum = 0;
        do {
            try {
                $data = $this->importOrdersOnce();
            } catch (Exception $e) {
                return $e->getCode() . ': ' . $e->getMessage();
            }
            $sum += $data['menge'];
            $importStep++;
        } while ($importStep < 2 && !$data['is_last_page'] && $data['total'] > 0);
        return "import summe: " . $sum;
    }

    function importOrderById ($order_id = 0) {
//        $this->checkLogin();  // für conjob, darf es kein logingeben

        ini_set("memory_limit","1024M");

        $resturl = str_replace('rest/orders', 'rest/orders/'.$order_id, $this->restAdress['orders']);

        $data = $this->callJsonRest($resturl);

        $this->__doImportOrderData($data);

        return true;
    }

    function importById () {
        $param = $this->request->data;

        $param['id'] = intval($param['id']);

        if ($param['type'] == 'order') {
            $this->importOrderById($param['id']);
        } else {
            $this->importItemById($param['id']);
        }
    }

    function clearOrderImports () {
        $this->checkLogin();
        $this->Item->query('TRUNCATE TABLE orders;');
        $this->Item->query('TRUNCATE TABLE order_items;');
        $this->Item->query('TRUNCATE TABLE order_properties;');
        $this->Item->query('delete from imports where type = "orders";');
    }

    function importItems ($newImport=false) {
        $this->autoRender = false;
        $this->checkIP();
        ini_set("memory_limit","1024M");

        $newImport = $this->makeNewImport('items', 0, $newImport);

        if ($newImport === false) return;

        $newImport['itemsPerPage'] = 100;

        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        if ($newImport['from']) {
            $params['updatedBetween'] = strtotime($newImport['from']);
            if ($newImport['to']) {
                $params['updatedBetween'] .= ',' . strtotime($newImport['to']);
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
            'version' => $this->version,
            'url' => $_SERVER['SCRIPT_URI'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        $this->Import->create();
        $this->Import->save($importData);
        $importData['id'] = $this->Import->getLastInsertID();
        $url = $this->restAdress['items'];

        $data = $this->callJsonRest($url, $params, 'GET', $importData['id']);

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
            'add_cms_page' => $item->add_cms_page,
            'age_restriction' => $item->ageRestriction,
            'amazon_fba_platform' => $item->amazonFbaPlatform,
            'amazon_fedas' => $item->amazonFedas,
            'amazon_product_type' => $item->amazonProductType,
            'condition' => $item->condition,
            'condition_api' => $item->conditionApi,
            'coupon_restriction' => $item->couponRestriction,
            'customs_tariff_number' => $item->customsTariffNumber,
            'ebay_category' => $item->ebayCategory,
            'ebay_category2' => $item->ebayCategory2,
            'ebay_presetid' => $item->ebayPresetId,
            'ebay_store_category' => $item->ebayStoreCategory,
            'ebay_store_category2' => $item->ebayStoreCategory2,
            'feedback' => $item->feedback,
            'flag1' => $item->flagOne,
            'flag2' => $item->flagTwo,
            'free1' => $item->free1,
            'free2' => $item->free2,
            'free3' => $item->free3,
            'free4' => $item->free4,
            'free5' => $item->free5,
            'free6' => $item->free6,
            'free7' => $item->free7,
            'free8' => $item->free8,
            'free9' => $item->free9,
            'free10' => $item->free10,
            'free11' => $item->free11,
            'free12' => $item->free12,
            'free13' => $item->free13,
            'free14' => $item->free14,
            'free15' => $item->free15,
            'free16' => $item->free16,
            'free17' => $item->free17,
            'free18' => $item->free18,
            'free19' => $item->free19,
            'free20' => $item->free20,
            'is_serial_number' => $item->isSerialNumber,
            'is_shippable_by_amazon' => $item->isShippableByAmazon,
            'is_shipping_package' => $item->isShippingPackage,
            'is_subscribable' => $item->isSubscribable,
            'max_order_quantity' => $item->maximumOrderQuantity,
            'owner_id' => $item->ownerId,
            'position' => $item->position,
            'producing_country_id' => $item->producingCountryId,
            'rakuten_category_id' => $item->rakutenCategoryId,
            'revenue_account' => $item->revenueAccount,
            'stock_type' => $item->stockType,
            'store_special' => $item->storeSpecial,

            'manufacturer_id' => $item->manufacturerId + 0,
            'main_variation_id' => $item->mainVariationId + 0,
            'created' => GlbF::iso2Date($item->createdAt),
            'modified' => GlbF::iso2Date($item->updatedAt),
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

        if (isset($item->itemShippingProfiles)) {
            $this->ItemShippingProfile->deleteAll(['item_id' => $data['extern_id']]);
            foreach ($item->itemShippingProfiles as $shippingProfile) {
                $this->ItemShippingProfile->create();
                $this->ItemShippingProfile->save([
                    'extern_id' => $shippingProfile->id,
                    'item_id' => $shippingProfile->itemId,
                    'profile_id' => $shippingProfile->profileId,
                    'modified' => $shippingProfile->updated_at
                ]);
            }
        }

        if (isset($item->itemCrossSelling)) {
            $this->ItemCrossSelling->deleteAll(['item_id' => $data['extern_id']]);
            foreach ($item->itemCrossSelling as $crossSelling) {
                $this->ItemCrossSelling->create();
                $this->ItemCrossSelling->save([
                    'item_id' => $crossSelling->itemId,
                    'cross_item_id' => $crossSelling->crossItemId,
                    'is_dynamic' => $crossSelling->isDynamic,
                    'relationship' => $crossSelling->relationship,
                    'modified' => $crossSelling->last_update_timestamp
                ]);
            }
        }

        if (isset($item->itemProperties)) {
            $this->ItemProperty->deleteAll(['item_id' => $data['extern_id']]);
            foreach ($item->itemProperties as $pps) {
                $this->ItemProperty->create();
                $savePps = [
                    'extern_id' => $pps->id,
                    'item_id' => $pps->itemId,
                    'property_id' => $pps->propertyId,
                    'surcharge' => $pps->surcharge,
                    'property_selection_id' => $pps->propertySelectionId ? $pps->propertySelectionId : null,
                    'value_file' => $pps->valueFile ? $pps->valueFile : null,
                    'value_float' => $pps->valueFloat ? $pps->valueFloat : null,
                    'value_int' => $pps->valueInt ? $pps->valueInt : null,
                    'value_text' => "",
                    'created' => GlbF::iso2Date($pps->createdAt),
                    'updated' => GlbF::iso2Date($pps->updatedAt)
                ];
                if (isset($pps->valueTexts) && $pps->valueTexts) {
                    foreach ($pps->valueTexts as $aText) {
                        if ($aText->lang == "de") {
                            $savePps['value_text'] = $aText->value;
                        }
                    }
                    if ($savePps['value_text'] == "") {
                        $savePps['value_text'] = $pps->valueTexts[0]->value;
                    }

                }
                $this->ItemProperty->save($savePps);
            }
        }
    }

    function importItemsAll () {
        $sum = 0;
        do {
            $data = $this->importItems();
            $sum += $data['menge'];
        } while (!$data['is_last_page']);
        return $sum;
    }

    function clearItemImports () {
        $this->checkLogin();
        $this->Item->query('TRUNCATE TABLE items;');
        $this->Item->query('TRUNCATE TABLE items_variations;');
        $this->Item->query('TRUNCATE TABLE items_variations_barcodes;');
        $this->Item->query('TRUNCATE TABLE item_shipping_profiles;');
        $this->Item->query('TRUNCATE TABLE item_cross_sellings;');
        $this->Item->query('delete from imports where type in ("items", "variations");');
    }

    function importItemById ($item_id) {
//        $this->checkLogin();
        ini_set("memory_limit","1024M");

        $url = str_replace('rest/items', 'rest/items/'.$item_id, $this->restAdress['items']);
        $data = json_decode($this->Rest->callAPI('GET', $url));

        if (isset($data->error)) {
            ErrorCode::throwException($data->error->message, ErrorCode::ErrorCodeBadRequest);
        }

        $this->__doImportItemData($data);


        $this->ItemsVariation->deleteAll([
            'item_id' => $item_id
        ]);
        $urlVa = str_replace($this->restAdress['variations'], 'rest/items/'.$item_id.'/variation', $this->restAdress['variations']);

        $data = $this->callJsonRest($urlVa);

        $variations = $data->entries;
        foreach ($variations as $var) {
            $this->__doImportVariationData($var);
        }
        return true;
    }

    function importVariations ($newImport=false) {
        $this->autoRender = false;
        $this->checkIP();
        ini_set("memory_limit","1024M");

        $newImport = $this->makeNewImport('variations', 0, $newImport);

        if ($newImport === false) return;

        $newImport['itemsPerPage'] = 100;

        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        if ($newImport['from']) {
            $params['updatedBetween'] = strtotime($newImport['from']);
            if ($newImport['to']) {
                $params['updatedBetween'] .= ',' . strtotime($newImport['to']);
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
            'version' => $this->version,
            'url' => $_SERVER['SCRIPT_URI'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ];

        $this->Import->create();
        $this->Import->save($importData);
        $importData['id'] = $this->Import->getLastInsertID();

        $url = $this->restAdress['variations'];
        $data = $this->callJsonRest($url, $params, 'GET', $importData['id']);

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

            'availability' => $var->availability,
            'availability_updated' => GlbF::iso2Date($var->availabilityUpdatedAt),
            'available_until' => $var->availableUntil,
            'bundke_type' => $var->bundleType,
            'default_shipping_cost' => $var->defaultShippingCosts + 0,
            'purchase_price' => $var->purchasePrice + 0,

            'is_active' => $var->isActive,
            'is_main' => $var->isMain,
            'interval_oder_quantity' => $var->intervalOrderQuantity,
            'max_order_quantity' => $var->maximumOrderQuantity,
            'min_order_quantity' => $var->minimumOrderQuantity,
            'parent_variation_id' => $var->parentVariationId,
            'parent_variation_quantity' => $var->parentVariationQuantity,
            'price_caclulation_id' => $var->priceCalculationId + 0,
            'operating_cost' => $var->operatingCosts + 0,
            'transportation_cost' => $var->transportationCosts + 0,
            'storage_cost' => $var->storageCosts + 0,
            'single_item_count' => $var->singleItemCount + 0,
            'stock_limit' => $var->stockLimitation + 0,
            'unit_combination_id' => $var->unitCombinationId + 0,
            'units_contained' => $var->unitsContained + 0,
            'vat_id' => $var->vatId + 0,

            'updated_at' => GlbF::iso2Date($var->updatedAt),
            'imported' => date('Y-m-d H:i:s')
        ];

        $d = $this->ItemsVariation->find('first',  [
            'fields' => 'id',
            'conditions' => [
                'extern_id' => $data['extern_id']
            ]
        ]);
        if ($d) {
            $data['id'] = $d['ItemsVariation']['id'];
        } else {
            $this->ItemsVariation->create();
        }

        $this->ItemsVariation->save($data);
        $this->ItemsVariationsBarcode->deleteAll([
            'variation_id' => $var->id
        ]);
        if ($var->variationBarcodes)
        foreach ($var->variationBarcodes as $barcode) {
            $bcd = [
                'variation_id' => $barcode->variationId + 0,
                'barcode_type_id' => $barcode->barcodeId + 0,
                'code' => $barcode->code,
            ];
            $this->ItemsVariationsBarcode->create();
            $this->ItemsVariationsBarcode->save($bcd);
        }

    }

    function importVariationsAll () {
        $sum = 0;
        do {
            $data = $this->importVariations();
            $sum += $data['menge'];
        } while (!$data['is_last_page']);
        return $sum;
    }

    function importUnits () {
        $this->autoRender = false;
        $this->Unit->query('TRUNCATE TABLE units;');
        CakeLog::write('import', "Import Units beginn...");
        $params = [
            'page' => 1,
            'itemsPerPage' => 1000
        ];

        $url = $this->restAdress['units'];
        $data = $this->callJsonRest($url);

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
        $url = $this->restAdress['barcode_types'];
        $data = $this->callJsonRest($url, $params);

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

    function callTodos () {
        $this->autoRender = false;
        CakeLog::write('import', "Search in todos...");
        $todos = $this->ImportTodo->find('all', [
            'conditions' => [
                'finished is null'
            ],
            'group' => ['type', 'extern_id']
        ]);

        if ($todos) {
            foreach ($todos as $todo) {
                switch($todo['ImportTodo']['type']) {
                    case 'item':
                        $this->importItemById($todo['ImportTodo']['extern_id']);
                        break;
                    case 'order':
                        $this->importOrderById($todo['ImportTodo']['extern_id']);
                        break;
                }

                $this->ImportTodo->updateAll(
                    [
                        'finished' => "'" . date('Y-m-d H:i:s') . "'"
                    ],
                    [
                        'type' => $todo['ImportTodo']['type'],
                        'extern_id' => $todo['ImportTodo']['extern_id']
                    ]
                );

                CakeLog::write('import', "import {$todo['ImportTodo']['type']} {$todo['ImportTodo']['extern_id']}.");
            }
        }

        CakeLog::write('import', "Todo finished!");
    }

    function importAvailabilities () {
        $url = $this->restAdress['availabilities'];
        $data = $this->callJsonRest($url);
        if ($data) {
            $this->Availability->query('TRUNCATE TABLE availabilities;');
            foreach ($data as $d) {
                $savedata = [
                    'id' => $d->id,
                    'average_days' => $d->averageDays,
                ];
                if (isset($d->names)) {
                    foreach ($d->names as $name) {
                        $savedata['name_' . $name->lang] = $name->name;
                    }
                }
                $this->Availability->create();
                $this->Availability->save($savedata);
            }
        }
    }

    function importItemPropertyGroups () {
        $url = $this->restAdress['item_property_groups'];
        $data = $this->callJsonRest($url);
        if ($data->entries) {
            $this->ItemPropertyGroup->query('TRUNCATE TABLE item_property_groups;');
            foreach ($data->entries as $d) {
                $savedata = [
                    'extern_id' => $d->id,
                    'backend_name' => $d->backendName,
                    'is_surcharge_percental' => $d->isSurchargePercental ? $d->isSurchargePercental : 0,
                    'order_property_grouping_type' => $d->orderPropertyGroupingType,
                    'otto_component' => $d->ottoComponent,
                    'updated' => GlbF::iso2Date($d->updatedAt),
                ];
                $this->ItemPropertyGroup->create();
                $this->ItemPropertyGroup->save($savedata);
            }
        }
    }

    function importItemPropertyTypes ($page = 1) {
        $this->importItemPropertyGroups();

        $url = $this->restAdress['item_property_types'];
        $data = $this->callJsonRest($url, ['itemsPerPage' => 250, 'page' => $page]);

        if ($data->entries) {
            $this->ItemPropertyType->query('TRUNCATE TABLE item_property_types;');
            $this->ItemPropertyType->query('TRUNCATE TABLE item_property_market_components;');
            $this->ItemPropertyType->query('TRUNCATE TABLE item_property_selections;');
            foreach ($data->entries as $d) {
                $savedata = [
                    'extern_id' => $d->id,
                    'backend_name' => $d->backendName,
                    'property_group_id' => $d->propertyGroupId ? $d->propertyGroupId : 0,
                    'value_type' => $d->valueType,
                    'comment' => $d->comment,
                    'is_oder_property' => $d->isOderProperty,
                    'is_searchable' => $d->isSearchable,
                    'is_shown_as_additional_costs' => $d->isShownAsAdditionalCosts,
                    'is_shown_at_checkout' => $d->isShownAtCheckout,
                    'is_shown_in_pdf' => $d->isShownInPdf,
                    'is_shown_on_item_list' => $d->isShownOnItemList,
                    'is_shown_on_item_page' => $d->isShownOnItemPage,
                    'position' => $d->position,
                    'surcharge' => $d->surcharge,
                    'unit' => $d->unit ? $d->unit : null,
                    'updated' => GlbF::iso2Date($d->updatedAt),
                ];
                $this->ItemPropertyType->create();
                $this->ItemPropertyType->save($savedata);

                if (isset($d->marketComponents)) {
                    foreach ($d->marketComponents as $mc) {
                        $this->ItemPropertyMarketComponent->create();
                        $this->ItemPropertyMarketComponent->save([
                            'property_idd' => $mc->propertyId,
                            'market_id' => $mc->marketId,
                            'component_id' => $mc->componentId,
                            'external_component' => $mc->externalComponent
                        ]);
                    }
                }

                if (isset($d->selections)) {
                    foreach ($d->selections as $sel) {
                        GlbF::printArray($sel);
                        $this->ItemPropertySelection->create();
                        $this->ItemPropertySelection->save([
                            'extern_id' => $sel->id,
                            'propertyId' => $sel->propertyId,
                            'name' => $sel->name,
                            'description' => $sel->description,
                            'lang' => $sel->lang
                        ]);
                    }
                }
            }
        }

        if (!$data->isLastPage) {
            $this->importItemPropertyTypes($page++);
        }
    }
}