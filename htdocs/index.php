<?php

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . "/../inc/");

require_once "Calendar/CalendarApp.php";

use \Calendar\CalendarApp;

$app = new CalendarApp();
$app->display();
