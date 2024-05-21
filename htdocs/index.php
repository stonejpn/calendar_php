<?php

require_once __DIR__ . "/../vendor/autoload.php";

$path_info = array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '';
$start_date = array_key_exists('start-date', $_COOKIE) ? $_COOKIE['start-date'] : '';
try {
    $app = new \Calendar\CalendarApp($path_info, $start_date);
    $app->display();
} catch (Exception $e) {
    print <<<EOD
<!doctype html>
<html lang="ja">
<head>
  <title>エラー</title>
</head>
<body>
<div style="margin: 2rem; margin-left: 25%; border: 1px #DDDDDD solid; padding 1rem; width: 50%;">
<div style="padding: 0.3rem 0; background-color: #FFEEEE; font-size: 24px; text-align: center; font-weight: bold">ERROR</div>
<div style="padding: 1rem;">{$e->getMessage()}</div>
</div>
</body>
</html>
EOD;
}