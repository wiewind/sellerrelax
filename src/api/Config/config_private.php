<?php
/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 15.12.2016
 * Time: 10:04
 */

$config['system']['name'] = 'SellerRelax';

$config['system']['domain'] = $_SERVER['HTTP_HOST'];
$config['system']['app']['dirname'] = 'ext';

$config['system']['rest']['url']            = 'https://www.delychi-group.de/';
$config['system']['rest']['username']       = 'SOAP_API_01';
$config['system']['rest']['password']       = 'De45892123!';
$config['system']['rest']['limitPerImport'] = 250;

$config['system']['dev']['email']  = 'zoubenying@hotmail.com';
$config['system']['admin']['frommail'] = 'hostmaster@delychi-group.de';
$config['system']['admin']['tomail'] = 'zoubenying@hotmail.com';