<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 28.05.2019
 * Time: 11:36
 */
class DeletedOrdersController extends RestAppController
{
    var $restAdress = [
        'orderStatusHistory' => 'rest/orders/status-history'
    ];

    function detetedOrders ($newImport=false) {
        $this->autoRender = false;
        $this->checkIP();
        ini_set("memory_limit","1024M");
        $newImport = $this->makeNewImport('detetedOrders', 0, $newImport);

        if ($newImport === false) return;

        $params = ['statusId' => 20];

        $newImport['itemsPerPage'] = 1000;
        $params['page'] = $newImport['page'];
        $params['itemsPerPage'] = $newImport['itemsPerPage'];

        if ($newImport['from']) {
            $params['createdAtFrom'] = GlbF::date2Iso($newImport['from']);
        }
        if ($newImport['to']) {
            $params['createdAtTo'] = GlbF::date2Iso($newImport['to']);
        }

        $now = date('Y-m-d H:i:s');

        CakeLog::write('import',  "Import deleted orders beginn...");
        CakeLog::write('import', "between: " . $newImport['from'] . " - " . $newImport['to'] . "; " . "page: " . $newImport['page']);

        $importData = [
            'type' => 'detetedOrders',
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
        $url = $this->restAdress['orderStatusHistory'];

        $data = $this->callJsonRest($url, $params, 'GET', $saveImportId);

        $orders = $data->entries;

        $errorOrders = [];
        foreach ($orders as $order) {
            $dataSource = $this->Order->getDataSource();
            $dataSource->begin();
            try {
                $this->__doImportDeletedOrders($order, $now);
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

        CakeLog::write('import', "Import deleted orders end with $mengeOfPage record(s)!");
    }

    private function __doImportDeletedOrders ($order, $now) {
        $odata = $this->Order->find('first', [
            'fields' => [
                'id',
                'status_id'
            ],
            'conditions' => [
                'extern_id' => $order->OrderId
            ]
        ]);

        if ($odata && $odata['Order']['status_id'] != '20') {
            $this->Order->save([
                'id' => $odata['Order']['id'],
                'status_id' => 20,
                'updated' => GlbF::iso2Date($order->createdAt),
                'imported' => $now
            ]);
        }
    }
}