<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 18.09.2018
 * Time: 09:31
 */
class ImportStockController extends AppController
{
    var $uses = [
        'Stock',
        'Item',
        'ItemsVariation',
        'ItemsVariationsBarcode',
        'BarcodeType',
        'Warehouse',
        'StockHistory',
        'WarehouseImport'
    ];

    function download ($warehouse_id) {
        $this->autoRender = false;
        $warehouse = $this->Warehouse->findById($warehouse_id);
        $res = false;
        if ($warehouse) {
            $warehouse = $warehouse['Warehouse'];
            switch ($warehouse['protokoll']) {
                case 'ftp':
                    $newImport = [
                        'warehouse_id' => $warehouse_id,
                        'access_at' => date('Y-m-d H:i:s'),
                        'success' => 1
                    ];
                    try {
                        $res = $this->downloadPerFtp($warehouse['host'], $warehouse['username'], $warehouse['password'], $warehouse['server_file'], $warehouse['local_file'], $warehouse['downloaded'], $warehouse['id']);
                    } catch (Exception $e) {
                        $newImport['success'] = 0;
                        $newImport['message'] = $e->getMessage();
                    }
                    $this->WarehouseImport->create();
                    $this->WarehouseImport->save($newImport);

                    break;
                case 'email':
                    $res = $this->downloadPerEmail($warehouse['host'], $warehouse['username'], $warehouse['password'], $warehouse['server_file'], $warehouse['local_file'], $warehouse['downloaded'], $warehouse['id']);
                    break;
            }
            $this->Warehouse->save([
                'id' => $warehouse['id'],
                'downloaded' => date('Y-m-d H:i:s')
            ]);
        }
        return ($res) ?
            sprintf(__("Successfully written to %s!"), $warehouse['local_file']) :
            sprintf(__("There are not new file for warehouse %s!"), $warehouse['id']);
    }

    function downloadPerFtp ($hostname, $username, $password, $server_file, $local_file, $last_downloaded, $warehouse_id) {
        // open some file to write to
        $path = Configure::read('system.import.stock.path');
        GlbF::mkDir($path);
        $local_filename = $path . '/' . $local_file;

        $success = false;

        // set up basic connection
        $conn_id = ftp_connect($hostname);
        if (!$conn_id) {
            ErrorCode::throwException(sprintf(__("FTP connect error at warehouse %s!"), $warehouse_id), ErrorCode::ErrorCodeBadRequest);
        }

        // login with username and password
        if (ftp_login($conn_id, $username, $password) && ftp_pasv($conn_id, true)) {
            $modifiedTS = ftp_mdtm($conn_id, $server_file);
            if ($modifiedTS != -1) {
                if ($last_downloaded<=0 || $modifiedTS >= strtotime($last_downloaded)) {
                    $success = ftp_get($conn_id, $local_filename, $server_file, FTP_ASCII, 0);
                    if (!$success) {
                        ErrorCode::throwException(sprintf(__("FTP transport error at warehouse %s!"), $warehouse_id), ErrorCode::ErrorCodeBadRequest);
                    }
                    $this->Warehouse->save([
                        'id' => $warehouse_id,
                        'fdate' => date('Y-m-d H:i:s', $modifiedTS)
                    ]);
                }
            }
        } else {
            ErrorCode::throwException(sprintf(__("FTP login error at warehouse %s!"), $warehouse_id), ErrorCode::ErrorCodeBadRequest);
        }

        // close the connection
        ftp_close($conn_id);
        return $success;
    }

    function downloadPerEmail ($hostname, $username, $password, $server_file, $local_file, $last_downloaded, $warehouse_id) {
        set_time_limit(3000);
        $success = false;

        // open some file to write to
        $path = Configure::read('system.import.stock.path');
        GlbF::mkDir($path);
        $local_filename = $path . '/' . $local_file;

        $inbox = imap_open($hostname, $username, $password);
        if (!$inbox) {
            ErrorCode::throwException(sprintf(__("The emailbox %s can not be login!"), $warehouse_id), ErrorCode::Success);
        }

        if (!$last_downloaded) {
            $last_downloaded = '2018-01-01 01:00:00';
        }

        $emails = imap_search($inbox,'SINCE "'.date('Y-m-d', strtotime($last_downloaded)).'"');

        if($emails) {

            $count = 1;

            /* put the newest emails on top */
            rsort($emails);

            /* for every email... */
            foreach ($emails as $email_number) {
                /* get information specific to this email */
                $overview = imap_fetch_overview($inbox, $email_number, 0);
                if (!$overview) {
                    continue;
                }

                $dateTS = intval($overview[0]->udate);
                $last_downloadedTS = strtotime($last_downloaded);

                if ($last_downloaded<=0 || $dateTS >= $last_downloadedTS) {
                    /* get mail message */
//                    $message = imap_fetchbody($inbox, $email_number, 2);

                    /* get mail structure */
                    $structure = imap_fetchstructure($inbox, $email_number);

                    $attachments = array();

                    /* if any attachments found... */
                    if(isset($structure->parts) && count($structure->parts))
                    {
                        for($i = 0; $i < count($structure->parts); $i++)
                        {
                            $attachments[$i] = array(
                                'is_attachment' => false,
                                'filename' => '',
                                'name' => '',
                                'attachment' => ''
                            );

                            if($structure->parts[$i]->ifdparameters)
                            {
                                foreach($structure->parts[$i]->dparameters as $object)
                                {
                                    if(strtolower($object->attribute) == 'filename')
                                    {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['filename'] = $object->value;
                                    }

                                    if(strtolower($object->attribute) == 'name')
                                    {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['name'] = $object->value;
                                    }
                                }
                            }

                            if($attachments[$i]['is_attachment'])
                            {
                                $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);

                                /* 3 = BASE64 encoding */
                                if($structure->parts[$i]->encoding == 3)
                                {
                                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                }
                                /* 4 = QUOTED-PRINTABLE encoding */
                                elseif($structure->parts[$i]->encoding == 4)
                                {
                                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                }
                            }
                        }
                    }

                    /* iterate through each attachment and save it */
                    foreach($attachments as $attachment)
                    {
                        if($attachment['is_attachment'] == 1 && ($attachment['filename'] === $server_file || $attachment['name'] === $server_file))
                        {
                            $fp = fopen($local_filename, "w+");
                            fwrite($fp, $attachment['attachment']);
                            fclose($fp);
                            $success = true;

                            $this->Warehouse->save([
                                'id' => $warehouse_id,
                                'fdate' => date('Y-m-d H:i:s', $dateTS)
                            ]);
                            break;
                        }
                    }
                }
            }

        }

        /* close the connection */
        imap_close($inbox);

        return $success;
    }

    function import ($warehouse_id) {
        $path = Configure::read('system.import.stock.path');
        $archivePath = $path . '/archive';
        GlbF::mkDir($archivePath);

        $warehouse = $this->Warehouse->findById($warehouse_id);
        if ($warehouse) {
            $warehouse = $warehouse['Warehouse'];
            $localfile = $path . '/' . $warehouse['local_file'];
            if (is_file($localfile)) {
                $now = date('Y-m-d H:i:s');
                $now2 = date('Ymd_His');
                switch ($warehouse['local_file']) {
                    case '1.csv':
                    case '2.csv':
                        $this->importCsv($localfile, 'importLine1', $warehouse['id'], $warehouse['fdate'], $now);
                        break;
                    case '3.csv':
                        $this->importCsv($localfile, 'importLine3', $warehouse['id'], $warehouse['fdate'], $now);
                        break;
                }

                // set all other stock 0
                $others = $this->Stock->find('all', [
                    'conditions' => [
                        'warehouse_id' => $warehouse['id'],
                        'quantity > ' => 0,
                        'imported < ' => $now
                    ]
                ]);

                if ($others) {
                    foreach ($others as $stock) {
                        $this->saveToHistory($stock, $now);
                        $stock['Stock'] = array_merge($stock['Stock'], [
                            'quantity' => 0,
                            'changed_quantity' => (0 - $stock['Stock']['quantity']),
                            'next_receipt' => 0,
                            'next_receipt_on' => null,
                            'reserved' => 0,
                            'imported' => $now
                        ]);
                        $this->Stock->save($stock['Stock']);
                    }
                }

                @rename($localfile, $archivePath . '/' . $warehouse['local_file'] . '_' . $now2);

                $this->Warehouse->save([
                    'id' => $warehouse['id'],
                    'imported' => $now
                ]);
            }
        }
    }

    function importCsv ($file, $fn, $warehouse_id, $fdate, $now) {
        if (($handle = fopen($file, "r")) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $row++;
                if ($row <= 1) continue;
                $this->$fn($data, $warehouse_id, $fdate, $now);
            }
            fclose($handle);
        }
    }

    function importLine1 ($data, $warehouse_id, $fdate, $time) {
        $variation = $this->ItemsVariation->find('first', [
            'fields' => [
                'ItemsVariation.item_id',
                'ItemsVariation.extern_id'
            ],
            'joins' => [
                [
                    'table' => Inflector::tableize('ItemsVariationsBarcode'),
                    'alias' => 'ItemsVariationsBarcode',
                    'conditions' => array(
                        'ItemsVariation.extern_id = ItemsVariationsBarcode.variation_id',
                        'ItemsVariationsBarcode.barcode_type_id' => 1
                    ),
                    'type' => 'inner'
                ]
            ],
            'conditions' => [
                'ItemsVariationsBarcode.code' => $data[1]
            ]
        ]);

        if (!$variation && !empty($data[0])) {
            $variation = $this->ItemsVariation->findByNumber($data[0]);
        }

        $saveData = [
            'warehouse_id' => $warehouse_id,
            'item_id' => ($variation) ? $variation['ItemsVariation']['item_id'] : 0,
            'variation_id' =>($variation) ? $variation['ItemsVariation']['extern_id'] : 0,
            'number' => $data[0],
            'ean' => $data[1],
            'quantity' => $data[6],
            'changed_quantity' => $data[6],
            'be_down' => $this->__getBoolean($data[5]),
            'next_receipt' => $data[7],
            'next_receipt_on' => $this->__getDate($data[8]),
            'fdate' => $fdate,
            'imported' => $time
        ];

        $stock = $this->Stock->find('first', [
            'conditions' => [
                'ean' => $data[1],
                'warehouse_id' => $warehouse_id
            ]
        ]);
        if ($stock) {
            $saveData['id'] = $stock['Stock']['id'];
            $saveData['changed_quantity'] = $saveData['quantity'] - $stock['Stock']['quantity'];
            //save history
            $this->saveToHistory($stock, $time);
        } else {
            $this->Stock->create();
        }

        $this->Stock->save($saveData);
    }

    function importLine3 ($data, $warehouse_id, $fdate, $time) {
        $variation = $this->ItemsVariation->findByNumber($data[0]);

        $saveData = [
            'warehouse_id' => $warehouse_id,
            'item_id' => ($variation) ? $variation['ItemsVariation']['item_id'] : 0,
            'variation_id' => ($variation) ? $variation['ItemsVariation']['extern_id'] : 0,
            'number' => $data[0],
            'quantity' => $data[2],
            'changed_quantity' => $data[2],
            'reserved' => $data[3],
            'fdate' => $fdate,
            'imported' => $time
        ];

        $stock = $this->Stock->find('first', [
            'conditions' => [
                'number' => $data[0],
                'warehouse_id' => $warehouse_id
            ]
        ]);
        if ($stock) {
            $saveData['id'] = $stock['Stock']['id'];
            $saveData['changed_quantity'] = $saveData['quantity'] - $stock['Stock']['quantity'];
            //save history
            $this->saveToHistory($stock, $time);
        } else {
            $this->Stock->create();
        }

        $this->Stock->save($saveData);
    }

    private function __getBoolean ($data) {
        if ($data === true) return 1;
        if ($data === 1) return 1;
        if (in_array(strtolower($data), ['1', 'j', 'ja', 'y', 'yes', 'ok', 'on', 'true'])) return 1;
        return 0;
    }

    private function __getDate ($data) {
        if (!$data) return;
        list($d, $m, $y) = explode('.', $data);
        return "$y-$m-$d";
    }

    public function saveToHistory ($stock, $deletedAt = false) {
        if (!$deletedAt) {
            $deletedAt = date('Y-m-d H:i:s');
        }
        $history = [
            'stock_id' => $stock['Stock']['id'],
            'number' => $stock['Stock']['number'],
            'ean' => $stock['Stock']['ean'],
            'warehouse_id' => $stock['Stock']['warehouse_id'],
            'item_id' => $stock['Stock']['item_id'],
            'variation_id' => $stock['Stock']['variation_id'],
            'be_down' => $stock['Stock']['be_down'],
            'quantity' => $stock['Stock']['quantity'],
            'changed_quantity' => $stock['Stock']['changed_quantity'],
            'next_receipt' => $stock['Stock']['next_receipt'],
            'next_receipt_on' => $stock['Stock']['next_receipt_on'],
            'reserved' => $stock['Stock']['reserved'],
            'fdate' => $stock['Stock']['fdate'],
            'imported' => $stock['Stock']['imported'],
            'deleted' => $deletedAt,
        ];
        $this->StockHistory->create();
        $this->StockHistory->save($history);
    }
}