<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 17.09.2018
 * Time: 11:23
 */

class GeneratorController extends AppController
{
    function beforeFilter () {
        $this->autoRender = false;
        parent::beforeFilter();
    }

    function getImgSrc ($code, $barcodeType='TYPE_EAN_13') {
        return 'data:image/png;base64,' . Barcode::getBarcode64($code, $barcodeType);
    }

    function getImgHtml ($code, $barcodeType='TYPE_EAN_13') {
        return '<img src="' .$this->getImgSrc($code, $barcodeType) . '">';
    }
}