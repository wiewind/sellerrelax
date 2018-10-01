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
        'BarcodeType'
    ];

    function import () {
        $path = Configure::read('system.import.stock.path');
        $archivePath = $path . '/archive';
        GlbF::mkDir($archivePath);

        $d = dir($path);
        while (false !== ($entry = $d->read())) {
            $file = $path . '/' . $entry;
            if (is_dir($file)) continue;
            switch ($entry) {
                case 'ausgabe.csv':
                    $this->importCsv($file, 'importLine1');
                    break;
                case 'bestaende.csv':
                    $this->importCsv($file, 'importLine2');
                    break;
            }
            @rename($file, $archivePath . '/' . $entry . '_' . date('Ymd_his'));
        }
        $d->close();
    }

    function importCsv ($file, $fn) {

        $now = date('Y-m-d H:i:s');
        if (($handle = fopen($file, "r")) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $row++;
                if ($row <= 1) continue;
                $this->$fn($data, $now);
            }
            fclose($handle);
        }
    }

    function importLine1 ($data, $time) {
        $variation = $this->ItemsVariation->findByNumber($data[0]);
        //if (!$variation) return;

        $saveData = [
            'warehouse_id' => '1',
            'item_id' => ($variation) ? $variation['ItemsVariation']['item_id'] : 0,
            'variation_id' => ($variation) ? $variation['ItemsVariation']['extern_id'] : 0,
            'number' => $data[0],
            'quantity' => $data[2],
            'reserved' => $data[3],
            'imported' => $time
        ];

        $stock = $this->Stock->find('first', [
            'fields' => 'id',
            'conditions' => [
                'number' => $data[0]
            ]
        ]);
        if ($stock) {
            $saveData['id'] = $stock['Stock']['id'];
        } else {
            $this->Stock->create();
        }

        $this->Stock->save($saveData);
    }

    function importLine2 ($data, $time) {
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

        $saveData = [
            'warehouse_id' => '2',
            'item_id' => ($variation) ? $variation['ItemsVariation']['item_id'] : 0,
            'variation_id' =>($variation) ? $variation['ItemsVariation']['extern_id'] : 0,
            'ean' => $data[1],
            'quantity' => $data[6],
            'be_down' => $this->__getBoolean($data[5]),
            'next_receipt' => $data[7],
            'next_receipt_on' => $this->__getDate($data[8]),
            'imported' => $time
        ];

        $stock = $this->Stock->find('first', [
            'fields' => 'id',
            'conditions' => [
                'ean' => $data[1]
            ]
        ]);
        if ($stock) {
            $saveData['id'] = $stock['Stock']['id'];
        } else {
            $this->Stock->create();
        }

        $this->Stock->save($saveData);
    }

    function importLine3 ($data, $time) {
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

        $saveData = [
            'warehouse_id' => '3',
            'item_id' => ($variation) ? $variation['ItemsVariation']['item_id'] : 0,
            'variation_id' =>($variation) ? $variation['ItemsVariation']['extern_id'] : 0,
            'ean' => $data[1],
            'quantity' => $data[6],
            'be_down' => $this->__getBoolean($data[5]),
            'next_receipt' => $data[7],
            'next_receipt_on' => $this->__getDate($data[8]),
            'imported' => $time
        ];

        $stock = $this->Stock->find('first', [
            'fields' => 'id',
            'conditions' => [
                'ean' => $data[1]
            ]
        ]);
        if ($stock) {
            $saveData['id'] = $stock['Stock']['id'];
        } else {
            $this->Stock->create();
        }

        $this->Stock->save($saveData);
    }

    private function __getBoolean ($data) {
        if (in_array(strtolower($data), ['1', 1, 'j', 'ja', 'y', 'yes', 'ok', 'on', 'true', true])) return 1;
        return 0;
    }

    private function __getDate ($data) {
        if (!$data) return;
        list($d, $m, $y) = explode('.', $data);
        return "$y-$m-$d";
    }
}