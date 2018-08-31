<?php
/**
 * Created by PhpStorm.
 * User: benying.zou
 * Date: 15.02.2018
 * Time: 15:26
 */

$config['system']['https']                  = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on';
$config['system']['url']                    = ($config['system']['https'] ? 'https' : 'http') . '://' . $config['system']['domain'];
$config['system']['projUrl']                = $config['system']['url'] . '/' . $config['system']['path'];


$config['system']['api']['path']            = $config['system']['path'] . '/' . $config['system']['api']['dirname'];
$config['system']['app']['path']            = $config['system']['path'] . '/' . $config['system']['app']['dirname'];

$config['system']['import']['path']         = $config['system']['path'] . '/' . $config['system']['import']['dirname'];
$config['system']['import']['tmp']['path']  = $config['system']['import']['path'] . '/' . $config['system']['import']['tmp']['dirname'];

$config['system']['image']['path']          = $config['system']['path'] . '/' . $config['system']['image']['dirname'];
$config['system']['image']['logo']          = $config['system']['image']['path'] . '/' . $config['system']['image']['logoFile'];

$config['system']['css']['path']            = $config['system']['path'] . '/' . $config['system']['css']['dirname'];

$config['system']['js']['path']             = $config['system']['path'] . '/' . $config['system']['js']['dirname'];