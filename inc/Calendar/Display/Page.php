<?php
namespace Calendar\Display;

use Calendar\ViewSettings;
use Calendar\ViewType;
use Calendar\WeekStartDate;

abstract class Page
{
    protected const MIN_YEAR = 2015;
    protected const MAX_YEAR = 2034;

    public static function create(ViewSettings $settings): ?Page
    {
        // match式は、PHP8以上の書式
        return match ($settings->getViewType()) {
            ViewType::Year => new Year(),
            ViewType::Month => new Month(),
            default => throw new \RuntimeException('Unknown ViewType ' . $settings->getViewType()),
        };
    }

    public function display(ViewSettings $settings):void
    {
        $this->headerFragment($this->getTitle($settings));

        $css_class = '';
        switch ($settings->getViewType()) {
            case ViewType::Year:
                $css_class = 'container-year';
                break;
            case ViewType::Month:
                $css_class = 'container-month';
                break;
        }
        print <<<EOD
<div class="container $css_class">
EOD;

        $this->navigation($settings);
        $this->switcher($settings);

        $this->content($settings);

        print <<<EOD
</div> <!-- div.container -->
</body>
</html>
EOD;
    }

    abstract protected function getTitle(ViewSettings $settings):string;
    abstract protected function getNaviContent(ViewSettings $settings):array;
    abstract protected function content(ViewSettings $settings):void;

    protected function headerFragment(string $title):void
    {
        print <<<EOD
<!doctype html>
<html lang="ja">
<head>
  <title>カレンダー $title</title>
  <link rel="stylesheet" href="/style.css" type="text/css" />
  <script src="/calendar.js"></script>
</head>
<body class="calendar">
  <div class="title">$title</div>
EOD;
    }

    protected function navigation(ViewSettings $settings):void
    {
        [$prev, $center, $next] = $this->getNaviContent($settings);

        print <<<EOD
  <ul class="navi">
    <li class="prev">$prev</li>
    <li class="year">$center</li>
    <li class="next">$next</li>
  </ul>
EOD;
    }

    protected function switcher(ViewSettings $settings):void
    {
        $sunday_checked = $monday_checked = '';
        switch ($settings->getWeekStartDate()) {
            case WeekStartDate::Sunday:
                $sunday_checked = 'checked';
                break;
            case WeekStartDate::Monday:
                $monday_checked = 'checked';
                break;
        }
        print <<<EOD
<div class="switcher">
  <form>
    <span><label><input id="change-start-date-sunday" type="radio" name="start_date" value="sunday" $sunday_checked/>日曜はじまり</label></span>
    <span><label><input id="change-start-date-monday" type="radio" name="start_date" value="monday" $monday_checked/>月曜はじまり</label></span>
  </form>
</div>
EOD;
    }
}