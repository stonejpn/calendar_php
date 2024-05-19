<?php

namespace Calendar\Display;

use Calendar\ViewSettings;
use Calendar\WeekStartDate;

class Month extends Page
{
    protected function getTitle(ViewSettings $settings): string
    {
        return "{$settings->getYear()}年 {$settings->getMonth()}月";
    }

    protected function getNaviContent(ViewSettings $settings): array
    {
        $prev_buffer = '<br/>';
        $year = $settings->getYear();
        $month = $settings->getMonth() - 1;
        if ($month === 0) {
            $year--;
            $month = 12;
        }
        if ($year >= self::MIN_YEAR ) {
            $prev_buffer = sprintf('<a href="/%d/%02d">&lt;&lt; %d年 %d月</a>', $year, $month, $year, $month);
        }

        // 次の月
        $next_buffer = '<br/>';
        $year = $settings->getYear();
        $month = $settings->getMonth() + 1;
        if ($month > 12) {
            $year++;
            $month = 1;
        }
        if ($year <= self::MAX_YEAR) {
            $next_buffer = sprintf('<a href="/%d/%02d">%d年 %d月 &gt;&gt;</a>', $year, $month, $year, $month);
        }

        $center_buffer = "<a href=\"/{$settings->getYear()}\">{$settings->getYear()}年 年間カレンダー</a>";

        return [$prev_buffer, $center_buffer, $next_buffer];
    }

    protected function content(ViewSettings $settings, array $holidays): void
    {
        // 曜日名ヘッダー
        print <<<EOD
<ul class="day-of-week-header">
EOD;
        // ヘッダー要素
        //   string $label // 曜日名
        //   string[] $css_styles // 表示用CSSクラス
        $header_list  = [];
        foreach (['月', '火', '水', '木', '金'] as $label) {
            $header_list[] = ['label' => $label, 'css_styles' => []];
        }
        switch ($settings->getWeekStartDate()) {
            case WeekStartDate::Sunday:
                // 日曜日を先頭に
                array_unshift($header_list, ["label" => '日', "css_styles" => ['sunday']]);
                $header_list[] = ['label' => '土', 'css_styles' => ['saturday', 'last-day-of-week']];
                break;
            case WeekStartDate::Monday:
                $header_list[] = ['label' => '土', 'css_styles' => ['saturday']];
                $header_list[] = ['label' => '日', 'css_styles' => ['sunday', 'last-day-of-week']];
                break;
        }
        foreach ($header_list as $date_elem) {
            $css_styles_str = 'header ' . join(' ', $date_elem['css_styles']);
            print <<<EOD
<li class="$css_styles_str">{$date_elem['label']}</li>
EOD;
        }
        print <<<EOD
</ul> <!-- ul.day-of-week-header -->
EOD;

        // 日付セルを表示
        $matrix = new MonthMatrix($settings, $holidays);
        $matrix->display($settings);
    }
}