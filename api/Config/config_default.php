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
$config['system']['app']['dirname']             = 'page';

$config['system']['import']['dirname']          = '__import__';
$config['system']['import']['tmp']['dirname']   = '__tmp__';

$config['system']['image']['dirname']           = 'img';
$config['system']['image']['logoFile']          = 'logo_22_16.png';

$config['system']['css']['dirname']             = 'css';

$config['system']['js']['dirname']              = 'js';

$config['auth']['key'] = 'sellerrelax.com2u';


$config['system']['rest']['url']            = 'https://www.delychi-group.de/';
$config['system']['rest']['username']       = 'SOAP_API_01';
$config['system']['rest']['password']       = 'De45892123!';
$config['system']['rest']['limitPerImport'] = 2000;

//include_once 'config_private.php';
include_once 'config_combi.php';