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
$config['system']['path'] = '';

$config['system']['api']['dirname']             = 'api/index.php/';
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

include_once 'config_private.php';
include_once 'config_combi.php';