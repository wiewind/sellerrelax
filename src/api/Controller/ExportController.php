<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 04.09.2018
 * Time: 10:48
 */
class ExportController extends AppController
{

    var $uses = [
        'Order',
        'Item',
        'OrderItem',
        'RestToken',
        'Unit',
        'ItemsVariation',
        'ItemsVariationsBarcode',
        'BarcodeType'
    ];

    function stockv1 () {
        $this->checkLogin();
        $this->layout = 'csv';
        $skus = $this->__getSkusFile();
        $fbacustomer="255214515";


    }

    private function __getSkusFile(){
        $file = '/meldenbestandskus.csv';

        $skus = [];

        $file_name = $file;

        if (file_exists($file_name)){

            $h_file=file($file_name);

            foreach ($h_file as $line) {
                if (trim($line)) {
                    $a_skus = explode(';',$line);
                    $sku = $a_skus[0];

                    $d = $this->VaritionNumber->find();
                }
            }
        }

        return $skus;
    }

}