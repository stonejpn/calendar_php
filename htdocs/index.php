<?php

require_once __DIR__ . "/../vendor/autoload.php";

$path_info = array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '';
$app = new \Calendar\CalendarApp($path_info);
$app->display();
