<?php

/**
 * Import the data from Schukat.
 * User: benying.zou
 * Date: 30.07.2019
 * Time: 15:06
 */
class SchukatController extends AppController
{
    var $zipUrl = 'https://www.schukat.com/schukat/schukat_cms_de.nsf/78e3877cd05b8905c1256d3d003c1083/7a494cd3a6009a3bc125754a0036811b/$FILE/SE_ART4.zip';
    var $schukatPath = ROOT.'/__import__/stocks';
    var $csvFile = 'SE_ART4.csv';

    var $uses = [
        'ItemsVariation',
        'Warehouse',
        'StockHistory',
        'Stock',
        'SchukatImport',
        'Warehouse'
    ];

    public function download () {
        $urlLogin = "https://www.schukat.com/names.nsf?Login";

        $post = array(
            'Username' => 'delychigmbh',
            'Password' => 'ym4kZWUfw8gB',
            'redirectTo' => $this->zipUrl
        );

        $cookie = $this->schukatPath.'/tmpcookie.txt';
        $this->login_post($urlLogin, $cookie, $post);

        $savefile = $this->schukatPath . '/schukat.zip';
        $this->get_content($this->zipUrl, $cookie, $savefile);

        $zip = new ZipArchive;
        if ($zip->open($savefile) === TRUE) {
            $zip->extractTo($this->schukatPath);
            $zip->close();
        } else {
            $this->__throwError('Unzip Error: ' . $savefile, ErrorCode::ErrorCodeServerInternal);
        }

        @unlink($cookie);
        @unlink($savefile);
    }

    function login_post($url, $cookie, $post){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_exec($ch);
        curl_close($ch);
    }

    function get_content($url, $cookie, $savefile){
        //Open file handler.
        $fp = fopen($savefile, 'w+');

        //If $fp is FALSE, something went wrong.
        if($fp === false){
            $this->__throwError('Could not open: ' . $savefile, ErrorCode::ErrorCodeServerInternal);
        }

        $ch = curl_init(); //初始化curl模块
        curl_setopt($ch, CURLOPT_URL, $url); //登录提交的地址

        //Pass our file handle to cURL.
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        $rs = curl_exec($ch);

        //If there was an error, throw an Exception
        if(curl_errno($ch)){
            $this->__throwError(curl_error($ch), curl_errno($ch));
        }

        //Get the HTTP status code.
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if($statusCode != 200){
            $this->__throwError(__('Zipfile can not be download!'), $statusCode);
        }

        $now = date('Y-m-d H:i:s');
        $this->Warehouse->save([
            'id' => 4,
            'fdate' => $now,
            'downloaded' => $now
        ]);


        return true;

    }

    private function __throwError ($msg, $code) {
        $Email = new CakeEmail();
        $Email->from(Configure::read('system.admin.frommail'));
        $Email->to(Configure::read('system.admin.tomail'));
        $Email->cc(Configure::read('system.dev.email'));

        $Email->subject("Fehler bei Schukat Download!");
        $Email->emailFormat('html');
        $Email->template('resterror');

        $Email->viewVars(array(
            'url' => 'schukat/json/download',
            'err' =>$msg,
            'params' => [
                'zipUrl' => $this->zipUrl
            ]
        ));
        $Email->send();

        ErrorCode::throwException($msg, $code);
    }

    private function __getLastImportLine ($today) {
        $data = $this->SchukatImport->find('first', [
            'fields' => 'line',
            'conditions' => [
                'download_date' => $today
            ],
            'order' => 'created desc'
        ]);
        return ($data) ? $data['SchukatImport']['line'] : 0;
    }


    public function import () {
        ini_set("memory_limit","1024M");
        ini_set('max_execution_time', 0);

        $importLine = 500000;

        $path = Configure::read('system.import.stock.path');
        $archivePath = $path . '/archive';
        GlbF::mkDir($archivePath);
        $file = $path . '/' . $this->csvFile;
        $spliter = ',';
        $today = date('Y-m-d');
        $fdate = $today . ' 00:00:00';
        $lastLine = $this->__getLastImportLine($today);
        $importData = [];
        $importRow = $lastLine;
        $totalRow = 0;

        if (file_exists($file)) {
            $fdate = date ("Y-m-d H:i:s.", filemtime($file));

            if (($handle = fopen($file, "r")) !== FALSE) {
                $isHeader = 1;
                while (($data = fgetcsv($handle, 5000, $spliter)) !== FALSE) {
                    if ($isHeader) {
                        $isHeader = 0;
                    } else {
                        $totalRow++;
                        if (($totalRow > $lastLine) && ($totalRow <= ($lastLine + $importLine))) {
                            $importData[$data[2]] = $data;
                            $importRow++;
                        }
                    }
                }
                fclose($handle);
            }
        }

        // only when realy import, than do the follows
        if ($importRow > 0 && $totalRow > 0) {

            // 1. import the data
            $this->__importData($importData, $fdate);
            $now = date('Y-m-d H:i:s');

            // 2. save the import log
            $this->SchukatImport->create();
            $this->SchukatImport->save([
                'download_date' => $today,
                'line' => $importRow,
                'total' => $totalRow,
                'created' => $now
            ]);

            // 3. if import not finished throw error and send mail, otherwise look at 'else'
            if ($importRow < $totalRow) {
                $this->__throwImportError("The import is not finished! Count of imported rows $importRow / $totalRow .", 800);
            } else {
                // a. the articles, which is not today imported, move into history
                $this->__clearOldStock($today);

                // b. the csv file move to archive
                $archiveFile = $archivePath . '/' . $this->csvFile . '_' . date('Ymd_His');
                @rename($file, $archiveFile);
                $this->Warehouse->save([
                    'id' => 4,
                    'imported' => $now
                ]);
            }
        }

        return [
            'line' => $importRow,
            'total' => $totalRow
        ];
    }

    private function __importData ($datas, $fdate) {
        $numbers = array_keys($datas);
        $dbDatas = [];
        $variations = [];

        $createValues = '';
        $saveHistories = '';

        if ($numbers) {
            $data = $this->Stock->find('all', [
                'conditions' => [
                    'number in ' => $numbers
                ]
            ]);
            if ($data) {
                foreach ($data as $d) {
                    $dbDatas[$d['Stock']['number']] = $d['Stock'];
                }
            }

            // items
            $data = $this->ItemsVariation->find('all', [
                'fields' => [
                    'item_id',
                    'extern_id',
                    'number'
                ],
                'conditions' => [
                    'number' => $numbers
                ]
            ]);
            if ($data) {
                foreach ($data as $d) {
                    $variations[$d['ItemsVariation']['number']] = $d['ItemsVariation'];
                }
            }
        }

        foreach ($datas as $num => $data) {
            $changedQuantity = $data[18];
            // set History
            if (isset($dbDatas[$num])) {
                $history = $dbDatas[$num];
                $changedQuantity = $changedQuantity - $history['quantity'];

                if ($saveHistories != '') $saveHistories .= ',';
                $saveHistories .= "(".
                    // stock_id, number, warehouse_id, item_id, variation_id, quantity, changed_quantity, next_receipt, next_receipt_on, fdate, imported, deleted
                    $history['id'].", ".
                    "'".$history['number']."', ".
                    $history['warehouse_id'].", ".
                    $history['item_id'].", ".
                    $history['variation_id'].", ".
                    $history['quantity'].", ".
                    $history['changed_quantity'].", ".
                    $history['next_receipt'].", ".
                    ($history['next_receipt_on'] ? "'".$history['next_receipt_on']."'" : "NULL").", ".
                    "'".$history['fdate']."', ".
                    "'".$history['imported']."', ".
                    "'".date('Y-m-d H:i:s')."'".
                    ")";
            }

            $itemId = isset($variations[$num]) ? $variations[$num]['item_id'] : 0;
            $variationId = isset($variations[$num]) ? $variations[$num]['extern_id'] : 0;
            if ($createValues != '') $createValues .= ',';
            // number, warehouse_id, item_id, variation_id, quantity, changed_quantity, next_receipt, next_receipt_on, fdate, imported
            $createValues .= "(".
                "'".$num."', ".                                      // number
                "4, ".                                               // warehouse_id
                $itemId.", ".                                        // item_id
                $variationId.", ".                                   // variation_id
                ($data[18] ? $data[18] : 0).", ".                    // quantity
                $changedQuantity.", ".                               // changed_quantity
                ($data[21] ? $data[21] : 0).", ".                    // next_receipt
                ($data[20] ? "'".$data[20]."'" : "NULL").", ".       // next_receipt_on
                "'".$fdate."', ".                                    // fdate
                "'".date('Y-m-d H:i:s')."'".                         // imported
                ")";
        }

        $dataSource =$this->Stock->getDataSource();
        $dataSource->begin();
        try {
            if ($saveHistories != "") {
                $sql_history = $this->__makeInsertHistorySql($saveHistories);
                $this->StockHistory->query($sql_history);
            }

            if ($numbers) {
                $this->Stock->deleteAll([
                    'warehouse_id' => 4,
                    'number' => $numbers
                ]);
            }

            if ($createValues != "") {
                $sql_stock = $this->__makeInsertStockSql($createValues);
                $this->Stock->query($sql_stock);
            }

            $dataSource->commit();
        } catch (Exception $e) {
            $dataSource->rollback();
            $this->__throwImportError($e->getMessage(), $e->getCode());
        }
    }

    private function __clearOldStock ($date) {
        $saveHistories = '';
        $ids = [];
        $data = $this->Stock->find('all', [
            'conditions' => [
                'warehouse_id' => 4,
                'imported < ' => $date
            ]
        ]);
        if ($data) {
            foreach ($data as $d) {
                $history = $d['Stock'];
                if ($saveHistories != '') $saveHistories .= ',';
                $saveHistories .= "(".
                    // stock_id, number, warehouse_id, item_id, variation_id, quantity, changed_quantity, next_receipt, next_receipt_on, fdate, imported, deleted
                    $history['id'].", ".
                    "'".$history['number']."', ".
                    $history['warehouse_id'].", ".
                    $history['item_id'].", ".
                    $history['variation_id'].", ".
                    $history['quantity'].", ".
                    $history['changed_quantity'].", ".
                    $history['next_receipt'].", ".
                    ($history['next_receipt_on'] ? "'".$history['next_receipt_on']."'" : "NULL").", ".
                    "'".$history['fdate']."', ".
                    "'".$history['imported']."', ".
                    "'".date('Y-m-d H:i:s')."'".
                    ")";

                $ids[] = $history['id'];
            }
        }

        if ($ids) {
            $sql_history = $this->__makeInsertHistorySql($saveHistories);
            $this->StockHistory->query($sql_history);
            $this->Stock->deleteAll(['id' => $ids]);
        }
    }

    private function __makeInsertHistorySql ($strValues) {
        return "insert into stock_histories (stock_id, number, warehouse_id, item_id, variation_id, quantity, changed_quantity, next_receipt, next_receipt_on, fdate, imported, deleted) values " . $strValues;
    }

    private function __makeInsertStockSql ($strValues) {
        return "insert into stocks (number, warehouse_id, item_id, variation_id, quantity, changed_quantity, next_receipt, next_receipt_on, fdate, imported) values " . $strValues;
    }

    private function __throwImportError ($msg, $code) {
        $Email = new CakeEmail();
        $Email->from(Configure::read('system.admin.frommail'));
        $Email->to(Configure::read('system.admin.tomail'));
        $Email->cc(Configure::read('system.dev.email'));

        $Email->subject("Fehler bei Import Schukat!");
        $Email->emailFormat('html');
        $Email->template('resterror');

        $Email->viewVars(array(
            'url' => 'schukat/json/import',
            'err' =>$msg,
            'params' => [
                'file' => $this->csvFile
            ]
        ));
        $Email->send();

        ErrorCode::throwException($msg, $code);
    }
}