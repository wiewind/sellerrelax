<?php
/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 28.08.2018
 * Time: 13:21
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

    var $version = '1.01';

    var $address = 'https://f3651371c816bca86799615a6497ad3200ebfac2.plentymarkets-cloud-de.com/export/%d/nn1kZ6pnRichjq33c9k32cR48zeamZDb';

    var $exportId = [
        'orders' => 20,
        'items' => '',
        'variations' => '',
        'units' => '',
        'barcode_types' => 'rest/items/barcodes'
    ];

    function test () {
        $this->autoRender = false;
        $url = sprintf($this->address, $this->exportId['orders']);
        $file_contents = file($url);

        echo $file_contents;
    }
}