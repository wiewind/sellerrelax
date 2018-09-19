<?php

/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 22.08.2018
 * Time: 13:55
 */
App::import('Vendor', 'BarcodeGenerator', array('file' => 'BarcodeGenerator'.DS.'main.php'));
class Barcode extends \Picqer\Barcode\BarcodeGenerator
{
    /*
     * Accepted BarcodeTypes:
     * TYPE_CODE_39
     * TYPE_CODE_39_CHECKSUM
     * TYPE_CODE_39E
     * TYPE_CODE_39E_CHECKSUM
     * TYPE_CODE_93
     * TYPE_STANDARD_2_5
     * TYPE_STANDARD_2_5_CHECKSUM
     * TYPE_INTERLEAVED_2_5
     * TYPE_INTERLEAVED_2_5_CHECKSUM
     * TYPE_CODE_128
     * TYPE_CODE_128_A
     * TYPE_CODE_128_B
     * TYPE_CODE_128_C
     * TYPE_EAN_2
     * TYPE_EAN_5
     * TYPE_EAN_8
     * TYPE_EAN_13
     * TYPE_UPC_A
     * TYPE_UPC_E
     * TYPE_MSI
     * TYPE_MSI_CHECKSUM
     * TYPE_POSTNET
     * TYPE_PLANET
     * TYPE_RMS4CC
     * TYPE_KIX
     * TYPE_IMB
     * TYPE_CODABAR
     * TYPE_CODE_11
     * TYPE_PHARMA_CODE
     * TYPE_PHARMA_CODE_TWO_TRACKS
     */
    public static function getHtmlImg ($code, $barcodeType, $width=0, $height=0) {
        $size = '';
        if ($width > 0) {
            $size .= ' width="'.$width.'"';
        }
        if ($height > 0) {
            $size .= ' height="'.$height.'"';
        }
        return '<img src="data:image/png;base64,' . base64_encode(self::getBarcode($code, $barcodeType)) . '"' . $size . '>';
    }

    public static function getBarcode ($code, $barcodeType, $filetype='png')  {
        $barcodeType = self::getBarcodeType($barcodeType);
        $generator = self::getGenerator($filetype);
        return $generator->getBarcode($code, $barcodeType);
    }

    public static function getBarcode64 ($code, $barcodeType, $filetype='png')  {
        return base64_encode(self::getBarcode($code, $barcodeType, $filetype));
    }

    public static function getGenerator ($filetype = 'png') {
        switch (strtolower($filetype)) {
            case 'png':
                return new \Picqer\Barcode\BarcodeGeneratorPNG();
            case 'jpg':
                return new \Picqer\Barcode\BarcodeGeneratorJPG();
            case 'svg':
                return new \Picqer\Barcode\BarcodeGeneratorSVG();
            case 'html':
                return new \Picqer\Barcode\BarcodeGeneratorHTML();
            default:
                return new \Picqer\Barcode\BarcodeGeneratorPNG();
        }
    }

    public static function getBarcodeType ($type) {
        return constant('\Picqer\Barcode\BarcodeGenerator::' . $type);
    }
}