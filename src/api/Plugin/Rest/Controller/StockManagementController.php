<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 22.11.2018
 * Time: 09:11
 */
class StockManagementController extends RestAppController
{
    var $restAdress = [
        'lcStocks'      => 'rest/stockmanagement/warehouses/{warehouseId}/stock/storageLocations',
        'warehouses'    => 'rest/stockmanagement/warehouses',
        'locations'     => 'rest/warehouses/{warehouseId}/locations',
        'dimensions'    => 'rest/warehouses/{warehouseId}/locations/dimensions',
        'levels'        => 'rest/warehouses/{warehouseId}/locations/levels'
    ];

    function  importAllLocationStocks () {
        $wdata = $this->SmWarehouse->find('all', [
            'fields' => 'extern_id'
        ]);
        if ($wdata) {
            foreach ($wdata as $wh) {
                $this->importLocationStocks($wh['SmWarehouse']['extern_id']);
            }
        }
    }

    function importLocationStocks ($warehouseId=1) {
        $this->autoRender = false;
        ini_set("memory_limit","1024M");

        $importType = 'lcst' . $warehouseId;
        $newImport = $this->makeNewImport($importType);

        if ($newImport === false) return;

        //$newImport['itemsPerPage'] = Configure::read('system.rest.limitPerImport');
        $newImport['itemsPerPage'] = 3000;

        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];
        if ($newImport['from']) {
            $from = GlbF::date2Iso($newImport['from']);
            $params['updatedAtFrom'] = $from;
        }

        if ($newImport['to']) {
            $to = GlbF::date2Iso($newImport['to']);
            $params['updatedAtTo'] = $to;
        }

        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import LocationStocks of warehouse ({$warehouseId}) beginn...");
        CakeLog::write('import', "between: " . $newImport['from'] . " - " . $newImport['to'] . "; " . "page: " . $newImport['page']);

        $importData = [
            'type' => $importType,
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

        $url = str_replace('{warehouseId}', $warehouseId, $this->restAdress['lcStocks']);
        $data = $this->Rest->callAPI('GET', $url, $params);

        $data = json_decode($data);

        $items = $data->entries;

        $errors = [];
        foreach ($items as $item) {
            $dataSource = $this->SmLocationStock->getDataSource();
            $dataSource->begin();
            try {
                $this->__doImportLocationStocksData($item, $now);
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $errors['storageLocationId:'.$item->storageLocationId.'_variationId:'.$item->variationId] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }

        $now2 = date('Y-m-d H:i:s');
        $mengeOfPage = ($data->lastOnPage) ? $data->lastOnPage - $data->firstOnPage + 1 : 0;

        if ($errors) {
            $logStr = "Import Errors: ";
            foreach ($errors as $oid => $err) {
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
            'errors' => json_encode($errors),
            'import_end' => $now2
        ];
        $this->Import->save($importData);

        CakeLog::write('import', "Import LocationStocks of warehouse ({$warehouseId}) end with $mengeOfPage record(s)!");
    }

    private function __doImportLocationStocksData ($data, $now="") {
        if (!$now) $now = date('Y-m-d H:i:s');

        $oData = [
            'warehouse_id'     => ($data->warehouseId) ? $data->warehouseId : 0,
            'location_id'      => ($data->storageLocationId) ? $data->storageLocationId : 0,
            'item_id'          => ($data->itemId) ? $data->itemId : 0,
            'variation_id'     => ($data->variationId) ? $data->variationId : 0,
            'quantity'         => ($data->quantity) ? $data->quantity : 0,
            'batch'            => ($data->batch) ? $data->batch : null,
            'best_before_date' => ($data->bestBeforeDate) ? GlbF::iso2Date($data->bestBeforeDate) : null,
            'updated'          => GlbF::iso2Date($data->updatedAt),
            'imported'         => $now
        ];

        $dbData = $this->SmLocationStock->find('first', [
            'fields' => 'id',
            'conditions' => [
                'warehouse_id' => $oData['warehouse_id'],
                'location_id' => $oData['location_id'],
                'item_id' => $oData['item_id'],
                'variation_id' => $oData['variation_id']
            ]
        ]);
        if ($dbData) {
            $oData['id'] = $dbData['SmLocationStock']['id'];
        } else {
            $this->SmLocationStock->create();
        }

        $this->SmLocationStock->save($oData);
    }


    function importWarehouses () {
        $this->autoRender = false;
        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import warehouses beginn...");

        $url = $this->restAdress['warehouses'];
        $data = $this->Rest->callAPI('GET', $url);

        $data = json_decode($data);

        $errors = [];
        foreach ($data as $item) {
            $dataSource = $this->SmWarehouse->getDataSource();
            $dataSource->begin();
            try {
                $this->__doImportWarehouseData($item, $now);
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $errors[$item->id] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }

        if ($errors) {
            $logStr = "Import Errors: ";
            foreach ($errors as $wid => $err) {
                $logStr .= "\t" . $wid . " (" . $err['code'] . ") " . $err['message'] . "\n";
            }
            CakeLog::write('import', $logStr);
        }

        CakeLog::write('import', "Import warehouses end with " . count($data) . " record(s)!");
    }

    private function __doImportWarehouseData ($data, $now="") {
        if (!$now) $now = date('Y-m-d H:i:s');

        $address = $data->address;

        $savedata = [
            'extern_id'                 => $data->id,
            'name'                      => $data->name,
            'average_price_source'      => ($data->average_price_source) ? $data->average_price_source : "0",
            'is_inventory_mode_active'  => $data->isInventoryModeActive,
            'logistiscs_type'           => $data->logisticsType,
            'note'                      => $data->note,
            'on_stock_availability'     => ($data->onStockAvailability) ? $data->onStockAvailability : 0,
            'out_of_stock_availability' => ($data->outOfStockAvailability) ? $data->outOfStockAvailability : 0,
            'reorder_level_dynamic'     => ($data->reorder_level_dynamic) ? $data->reorder_level_dynamic : "0",
            'repair_warehouse_id'       => ($data->repairWarehouseId) ? $data->repairWarehouseId : 0,
            'split_by_shipping_profile' => ($data->splitByShippingProfile) ? 1 : 0,
            'storage_location_type'     => $data->storageLocationType,
            'storage_location_zone'     => ($data->storageLocationZone) ? $data->storageLocationZone : 0,
            'type_id'                   => ($data->typeId) ? $data->typeId : 0,
            'imported'                  => $now,

            'street'                    => trim($address->address1) . ' ' . trim($address->address2),
            'postalcode'                => trim($address->postalCode),
            'town'                      => trim($address->town),
            'country_id'                => $address->countryId
        ];

        if (isset($address->options)) {
            foreach ($address->options as $op) {
                switch ($op->typeId) {
                    case 4:
                        $savedata['telephone'] = $op->value;
                        break;
                    case 5:
                        $savedata['email'] = $op->value;
                        break;
                }
            }
        }

        $dbData = $this->SmWarehouse->find('first', [
            'fields' => 'id',
            'conditions' => [
                'extern_id' => $savedata['extern_id']
            ]
        ]);

        if ($dbData) {
            $savedata['id'] = $dbData['SmWarehouse']['id'];
        } else {
            $this->SmWarehouse->create();
        }

        $this->SmWarehouse->save($savedata);

        $this->importLocations($savedata['extern_id']);
        $this->importDimensions($savedata['extern_id']);
        $this->importLevels($savedata['extern_id']);
    }

    function importLocations ($warehouseId, $page=1) {
        $this->autoRender = false;
        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import Locations of warehouse {$warehouseId} page {$page} beginn...");

        $params['itemsPerPage'] = 3000;
        $params['page'] = $page;

        $url = str_replace('{warehouseId}', $warehouseId, $this->restAdress['locations']);
        $data = $this->Rest->callAPI('GET', $url, $params);

        $data = json_decode($data);
        $errors = [];
        foreach ($data->entries as $item) {
            $dataSource = $this->SmLocation->getDataSource();
            $dataSource->begin();
            try {
                $savedata = [
                    'extern_id'             => $item->id,
                    'warehouse_id'          => $warehouseId,
                    'label'                 => $item->label,
                    'full_label'            => $item->fullLabel,
                    'level_id'              => $item->levelId,
                    'notes'                 => ($item->notes) ? $item->notes : null,
                    'pickup_path_position'  => ($item->pickupPathPosition) ? $item->pickupPathPosition : '0',
                    'position'              => $item->position,
                    'purpose_key'           => ($item->purposeKey) ? $item->purposeKey : null,
                    'status_key'            => ($item->statusKey) ? $item->statusKey : null,
                    'type'                  => $item->type,
                    'created'               => GlbF::iso2Date($item->createdAt),
                    'modified'              => GlbF::iso2Date($item->updatedAt),
                    'imported'              => $now
                ];

                $dbData = $this->SmLocation->find('first', [
                    'fields' => 'id',
                    'conditions' => [
                        'extern_id' => $savedata['extern_id']
                    ]
                ]);
                if ($dbData) {
                    $savedata['id'] = $dbData['SmLocation']['id'];
                } else {
                    $this->SmLocation->create();
                }

                $this->SmLocation->save($savedata);
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $errors[$item->id] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }

        if ($errors) {
            $logStr = "Import Errors: ";
            foreach ($errors as $id => $err) {
                $logStr .= "\t" . $id . " (" . $err['code'] . ") " . $err['message'] . "\n";
            }
            CakeLog::write('import', $logStr);
        }

        CakeLog::write('import', "Import Locations of warehouse {$warehouseId} page {$page} end with " . count($data->entries) . " record(s)!");

        if (!$data->isLastPage) {
            $this->importLocations($warehouseId, ++$page);
        }
    }

    function importDimensions ($warehouseId) {
        $this->autoRender = false;
        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import dimensions of warehouse {$warehouseId} beginn...");

        $params['itemsPerPage'] = 3000;
        $url = str_replace('{warehouseId}', $warehouseId, $this->restAdress['dimensions']);
        $data = $this->Rest->callAPI('GET', $url, $params);


        $data = json_decode($data);
        $errors = [];

        foreach ($data as $item) {
            $dataSource = $this->SmDimension->getDataSource();
            $dataSource->begin();
            try {
                $savedata = [
                    'extern_id'                 => $item->id,
                    'warehouse_id'              => $item->warehouseId,
                    'parent_id'                 => ($item->parentId) ? $item->parentId : 0,
                    'level'                     => $item->level,
                    'name'                      => $item->name,
                    'separator'                 => $item->separator,
                    'shortcut'                  => $item->shortcut,
                    'display_in_name'           => ($item->displayInName) ? 1 : 0,
                    'is_active_for_pickup_path' => ($item->isActiveForPickupPath) ? 1 : 0,
                    'created'                   => GlbF::iso2Date($item->createdAt),
                    'modified'                  => GlbF::iso2Date($item->updatedAt),
                    'imported'                  => $now
                ];

                $dbData = $this->SmDimension->find('first', [
                    'fields' => 'id',
                    'conditions' => [
                        'extern_id' => $savedata['extern_id']
                    ]
                ]);
                if ($dbData) {
                    $savedata['id'] = $dbData['SmDimension']['id'];
                } else {
                    $this->SmDimension->create();
                }

                $this->SmDimension->save($savedata);
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $errors[$item->id] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }

        if ($errors) {
            $logStr = "Import Errors: ";
            foreach ($errors as $id => $err) {
                $logStr .= "\t" . $id . " (" . $err['code'] . ") " . $err['message'] . "\n";
            }
            CakeLog::write('import', $logStr);
        }

        CakeLog::write('import', "Import dimensions of warehouse {$warehouseId} end with " . count($data) . " record(s)!");
    }

    function importLevels ($warehouseId) {
        $this->autoRender = false;
        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import levels of warehouse {$warehouseId} beginn...");

        $params['itemsPerPage'] = 3000;
        $url = str_replace('{warehouseId}', $warehouseId, $this->restAdress['levels']);
        $data = $this->Rest->callAPI('GET', $url, $params);

        $data = json_decode($data);
        $errors = [];
        foreach ($data as $item) {
            $dataSource = $this->SmLevel->getDataSource();
            $dataSource->begin();
            try {
                $savedata = [
                    'extern_id'             => $item->id,
                    'warehouse_id'          => $warehouseId,
                    'parent_id'             => ($item->parentId) ? $item->parentId : 0,
                    'dimension_id'          => ($item->dimensionId) ? $item->dimensionId : 0,
                    'name'                  => $item->name,
                    'pathname'              => $item->pathName,
                    'pickup_path_position'  => $item->pickupPathPosition,
                    'position'              => ($item->position) ? $item->position : 0,
                    'created'               => GlbF::iso2Date($item->createdAt),
                    'modified'              => GlbF::iso2Date($item->updatedAt),
                    'imported'              => $now
                ];
                $dbData = $this->SmLevel->find('first', [
                    'fields' => 'id',
                    'conditions' => [
                        'extern_id' => $savedata['extern_id']
                    ]
                ]);
                if ($dbData) {
                    $savedata['id'] = $dbData['SmLevel']['id'];
                } else {
                    $this->SmLevel->create();
                }

                $this->SmLevel->save($savedata);
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $errors[$item->id] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }
        }

        if ($errors) {
            $logStr = "Import Errors: ";
            foreach ($errors as $id => $err) {
                $logStr .= "\t" . $id . " (" . $err['code'] . ") " . $err['message'] . "\n";
            }
            CakeLog::write('import', $logStr);
        }

        CakeLog::write('import', "Import levels of warehouse {$warehouseId} end with " . count($data) . " record(s)!");
    }
}