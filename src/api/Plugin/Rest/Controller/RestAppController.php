<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 22.11.2018
 * Time: 09:09
 */
class RestAppController extends AppController
{
    var $uses = [
        'Import',
        'ImportTodo',
        'Order',
        'Item',
        'OrderItem',
        'OrderProperty',
        'RestToken',
        'Unit',
        'ItemsVariation',
        'ItemsVariationsBarcode',
        'BarcodeType',

        'SmWarehouse',
        'SmLocation',
        'SmDimension',
        'SmLevel',
        'SmLocationStock',

        'Address',
        'Account',
        'AccountsContact',
        'Contact',
        'ContactClass',
        'ContactOptionType',
        'ContactOptionSubType',
        'ContactPosition',
        'ContactType',
        'AddressRelationType',
        'AddressOptionType',
        'ContactOption',
        'AddressOption',
        'ContactAddress'
    ];

    var $components = ['MySession', 'MyCookie', 'Rest'];

    var $version = '1.02';

    /**
     * make the data for new Import
     * @param string $type Importtype: orders, items, contacts, variations, lcStocks
     * @param int $importId, when importId > 0 than import only this, others import all
     * @return array|bool
     */
    function makeNewImport ($type, $importId = 0) {

        if ($importId > 0) {
            $lastImport = $this->Import->findById($importId);
            return [
                'type' => $lastImport['Import']['type'],
                'from' => $lastImport['Import']['update_from'],
                'to' => $lastImport['Import']['update_to'],
                'page' => $lastImport['Import']['page'],
                'install' => $lastImport['Import']['update_from'] <= 0
            ];
        }

        $lastImport = $this->Import->find('first', [
            'conditions' => [
                'type' => $type
            ],
            'order' => 'id desc'
        ]);

        $update_from = '';
        $update_to = date('Y-m-d H:i:s');
        $page = 1;

        if ($lastImport) {
            if (!$lastImport['Import']['import_end']) {
                if (strtotime($lastImport['Import']['import_beginn']) >= strtotime('-3 minute')) {
                    return false;
                }
            }

            if (!$lastImport['Import']['is_last_page'] && !$lastImport['Import']['last_page_no']) {
                CakeLog::write('import',  "***********************  reimport ". $lastImport['Import']['page'] ."  **************************");
                $page = $lastImport['Import']['page'];
                $update_from = $lastImport['Import']['update_from'];
                $update_to = $lastImport['Import']['update_to'];
            } else if ($lastImport['Import']['last_page_no'] > $lastImport['Import']['page']) {
                $page = $lastImport['Import']['page'] + 1;
                $update_from = $lastImport['Import']['update_from'];
                $update_to = $lastImport['Import']['update_to'];
            } else {
                $update_from = $lastImport['Import']['update_to'];
            }
        }

        return [
            'type' => $type,
            'from' => $update_from,
            'to' => $update_to,
            'page' => $page,
            'install' => ($update_from <= 0)
        ];
    }

    function sendRestError ($err, $url, $params=[], $importId=0) {
        $Email = new CakeEmail();
        $Email->from(Configure::read('system.admin.frommail'));
        $Email->to(Configure::read('system.admin.tomail'));
        $Email->cc(Configure::read('system.dev.email'));

        $Email->subject("Rest Fehler!");
        $Email->emailFormat('html');
        $Email->template('resterror');

        if (isset($err->message)) {
            $err = $err->message;
        } else {
            $err = "unknown error";
        }

        $Email->viewVars(array(
            'url' => $url,
            'err' =>$err,
            'params' => $params
        ));
        $Email->send();

        if ($importId > 0) {
            $this->Import->save([
                'id' => $importId,
                'errors' => "['".$err."']"
            ]);
        }
    }

    function callJsonRest ($url, $params = false, $method = 'GET', $importId=0) {
        $data = $this->Rest->callAPI($method, $url, $params);
        $data = json_decode($data);

        if (isset($data->error)) {
            $this->sendRestError($data->error, $url, $params, $importId);
            ErrorCode::throwException($data->error->message);
        }
        return $data;
    }
}