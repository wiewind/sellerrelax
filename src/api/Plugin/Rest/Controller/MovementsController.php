<?php

/**
 * Created by PhpStorm.
 * User: benyingz
 * Date: 29.11.2019
 * Time: 10:34
 */
class MovementsController extends RestAppController
{
    var $restAdress = [
        'movements' => '/rest/stockmanagement/warehouses/{warehouse_id}/stock/movements'
    ];

    function importMovementsOnce ($wid, $newImport=false) {
        $this->autoRender = false;
        $this->checkIP();
        ini_set("memory_limit","1024M");
        $importType = 'movements'.$wid;
        $newImport = $this->makeNewImport($importType, 0, $newImport);

        if ($newImport === false) return;

        //$newImport['itemsPerPage'] = Configure::read('system.rest.limitPerImport');
        $newImport['itemsPerPage'] = 250;
        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        if ($newImport['from']) {
            $from = GlbF::date2Iso($newImport['from']);
            $params['createdAtFrom'] = $from;
        }
        if ($newImport['to']) {
            $to = GlbF::date2Iso($newImport['to']);
            $params['createdAtTo'] = $to;
        }

        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import movements for warehous $wid beginn...");
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
        $url = str_replace("{warehouse_id}", $wid, $this->restAdress['movements']);

        $data = $this->callJsonRest($url, $params, 'GET', $saveImportId);

        $movements = $data->entries;

        $errorOrders = [];
        foreach ($movements as $movement) {
            $dataSource = $this->Movement->getDataSource();
            $dataSource->begin();
            try {
                $this->__doImportMovementData($movement, $now);
                $dataSource->commit();
            } catch (Exception $e) {
                $dataSource->rollback();
                $errorOrders[$movement->id] = [
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

        CakeLog::write('import', "Import movements for warehous $wid end with $mengeOfPage record(s)!");

        return $importData;
    }

    private function __doImportMovementData ($movement, $now="") {
        if (!$now) $now = date('Y-m-d H:i:s');

        $saveData = [
            'extern_id'     => $movement->id + 0,
            'warehouse_id'     => $movement->warehouseId + 0,
            'location_full_label'   => $movement->storageLocationName,
            'item_id'      => $movement->itemId + 0,
            'variation_id'       => $movement->variationId + 0,
            'quantity'     => $movement->quantity + 0,
            'reason'       => $movement->reason + 0,
            'reason_string'       => $movement->reasonString,
            'attribute_values' => $movement->attributeValues,
            'batch' => $movement->batch,
            'best_before_date' => $movement->bestBeforeDate ? GlbF::iso2Date($movement->bestBeforeDate) : '',
            'process_row_id' => $movement->processRowId + 0,
            'process_row_type' => $movement->processRowType + 0,
            'purchase_price' => $movement->purchasePrice + 0,
            'user_id' => $movement->userId + 0,
            'created'       => GlbF::iso2Date($movement->createdAt),
            'imported'      => $now
        ];

        $dbMovement = $this->Movement->find('first', [
            'fields' => 'id',
            'conditions' => [
                'extern_id' => $saveData['extern_id']
            ]
        ]);
        if ($dbMovement) {
            $saveData['id'] = $dbMovement['Movement']['id'];
        } else {
            $this->Movement->create();
        }

        $this->Movement->save($saveData);

    }

    function importMovementFull ($wid) {
        $importStep = 0;
        $sum = 0;
        do {
            try {
                $data = $this->importMovementsOnce($wid);
            } catch (Exception $e) {
                return $e->getCode() . ': ' . $e->getMessage();
            }
            $sum += $data['menge'];
            $importStep++;
        } while (!$data['is_last_page'] && $data['total'] > 0);
        return "import summe: " . $sum;
    }
}