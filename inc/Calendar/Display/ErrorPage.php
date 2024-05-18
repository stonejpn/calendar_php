<?php
namespace Calendar\Display;

use Calendar\ViewSettings;

class ErrorPage
{
    /** エラーを表示 */
    public static function display(ViewSettings $settings, string $error_message):void
    {
        print <<<EOD
<!doctype html>
<html lang="ja">
<head>
  <title>カレンダー：エラー</title>
  <link rel="stylesheet" href="/style.css" type="text/css" />
</head>
<body class="error">
  <div class="title">{$settings->getYear()}年　{$settings->getMonth()}月</div>
  <div class="error">
    <div>エラーが起きました</div>
    <div>$error_message</div>
  </div>
</body>
</html>
EOD;
    }
}