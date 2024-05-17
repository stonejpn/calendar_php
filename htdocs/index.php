<?php

require_once __DIR__ . "/../vendor/autoload.php";

$path_info = array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '';
$start_date = array_key_exists('start-date', $_COOKIE) ? $_COOKIE['start-date'] : '';
$app = new \Calendar\CalendarApp($path_info, $start_date);
$app->display();
