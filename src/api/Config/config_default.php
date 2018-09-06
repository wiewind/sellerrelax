<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$config['system']['brand'] = 'dev';
$config['system']['name'] = 'SellerRelax';
$config['system']['appName'] = 'SRX';
$config['system']['author'] = 'Wiewind Studio';
$config['system']['domain'] = 'daheim-outlet.de';
$config['system']['defaultLanguage']['id'] = 1;
$config['system']['path'] = '';
$config['system']['classic']['maxPageSize'] = 30;
$config['system']['modern']['maxPageSize'] = 10;

$config['system']['api']['dirname']             = 'api/index.php';
$config['system']['app']['dirname']             = 'ext';

$config['system']['import']['dirname']          = '__import__';
$config['system']['import']['tmp']['dirname']   = '__tmp__';

$config['system']['image']['dirname']           = 'resources/images';
$config['system']['image']['logoFile']          = 'logo/logo.png';

$config['system']['css']['dirname']             = 'resources/css';

$config['system']['js']['dirname']              = 'resources/js';

$config['auth']['key'] = 'sellerrelax.com2u';

$config['system']['rest']['url']            = '';
$config['system']['rest']['username']       = '';
$config['system']['rest']['password']       = '';
$config['system']['rest']['limitPerImport'] = 2000;


$config['Glb']['formatting'] = [
    'zho' => [
        'currency_char' => '￥',
        'date_format' => 'Y年m月d日',
        'date_format_short' => 'y.m.d',
        'decimal_separator' => '.',
        'thousands_separator' => ','
    ],
    'deu' => [
        'currency_char' => '€',
        'date_format' => 'd.m.Y',
        'date_format_short' => 'd.m.Y',
        'decimal_separator' => ',',
        'thousands_separator' => '.'
    ],
    'eng' => [
        'currency_char' => '$',
        'date_format' => 'm/d/Y',
        'date_format_short' => 'm/d/y',
        'decimal_separator' => '.',
        'thousands_separator' => ','
    ]
];

include_once 'config_private.php';
include_once 'config_combi.php';