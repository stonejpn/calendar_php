<?php

namespace Calendar\Display;

use Calendar\ViewSettings;

class Year extends Page
{
    protected function getTitle(ViewSettings $settings):string
    {
        return "{$settings->getYear()}年";
    }

    protected function getNaviContent(ViewSettings $settings):array
    {
        $prev_buffer = '<br/>';
        if ($settings->getYear() > self::MIN_YEAR) {
            $year = $settings->getYear() - 1;
            $prev_buffer = "<a href=\"/$year\">&lt;&lt; {$year}年</a>";
        }
        $next_buffer = '<br/>';
        if ($settings->getYear() < self::MAX_YEAR) {
            $year = $settings->getYear() + 1;
            $next_buffer = "<a href=\"$year\">{$year}年 &gt;&gt;</a>";
        }

        return [$prev_buffer, '<br/>', $next_buffer];
    }

    protected function content(ViewSettings $settings, array $holidays):void
    {
        // ３X４で表示させる
        for ($month = 1; $month <= 12; $month++) {
            if (($month % 3) === 1) {
                print '<div class="year-row">';
            }

            $this_month = $settings->modifyMonth($month);
            $matrix = new MonthMatrix($this_month, $holidays);
            $matrix->display($this_month);

            if (($month % 3) === 0) {
                // year-row
                print '</div>';
            }
        }
    }
}