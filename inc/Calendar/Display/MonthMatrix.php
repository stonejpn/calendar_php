<?php
namespace Calendar\Display;

use Calendar\ViewSettings;
use Calendar\WeekStartDate;
use Calendar\ViewType;

/**
 * 月ごとの表示用セルを格納する
 *
 * ▼第１週
 *
 * １日の曜日によって、最初の空白セルの個数を決める。
 *
 *  ▽例１：１日が水曜日
 *
 *  　日曜はじまり
 *  　日　月　火　水　木　金　土
 *  　□　□　□　１　２　３　４　　--> フィラーは３つ
 *
 *  　月曜はじまり
 *  　月　火　水　木　金　土　日
 *  　□　□　１　２　３　４　５　　--> フィラーは２つ
 *
 *  ▽例２：１日が日曜日
 *
 *  　日曜はじまり
 *  　日　月　火　水　木　金　土
 *  　１　２　３　４　５　６　７　　--> フィラーなし
 *
 *  　月曜はじまり
 *  　月　火　水　木　金　土　日
 *  　□　□　□　□　□　□　１
 *  　２　３　４　５　６　７　８　　--> フィラー６つ
 *
 * ▼最終週
 *
 * 表示が四角になるように月末以降を空白セルで埋める
 *
 * 　日　月　火　水　木　金　土
 * 　29　30　31　□　□　□　□　　--> フィラーで埋める
 *
 */
class MonthMatrix
{
    protected array $matrix;
    protected ?int $day_count;

    public function __construct(ViewSettings $settings, array $holidays)
    {
        $this->matrix = [];

        // 月初めの１日に設定
        $first_day = new \DateTimeImmutable("{$settings->getYear()}/{$settings->getMonth()}/1");
        $day_of_week = (int) $first_day->format('w');

        // １日までを空白セルで埋める
        $filler_count = ($settings->getWeekStartDate() === WeekStartDate::MONDAY)
            ? ($day_of_week + 6) % 7 : $day_of_week;
        for ($i = 0; $i < $filler_count; $i++) {
            $this->matrix[] = new DateCell(0, 0);
        }

        // `last day of`で月末の日付を取得
        $this->day_count = (int) $first_day->modify('last day of')->format('j');

        for ($day = 1; $day <= $this->day_count; $day++) {
            $holiday_key = sprintf("%02d%02d", $settings->getMonth(), $day);
            $this->matrix[] = new DateCell(
                $day,
                ($day_of_week + $day - 1) % 7,
                array_key_exists($holiday_key, $holidays) ? $holidays[$holiday_key] : ''
            );
        }

        // 月末より後も、１週間分マスがあるように空白セルで埋める
        while ((count($this->matrix) % 7) !== 0) {
            $this->matrix[] = new DateCell(0, 0);
        }
    }

    public function display(ViewSettings $settings): void
    {
        print <<<EOD
<div class="month-matrix">
EOD;
        if ($settings->getViewType() === ViewType::YEAR) {
            // 年間カレンダーでは、月名と月別カレンダーへのリンクを表示
            print <<<EOD
<div class="month-name"><a href="/{$settings->getYear()}/{$settings->getMonth()}">{$settings->getMonth()}月</a></div>
EOD;
        }

        foreach ($this->matrix as $i => $date_cell) {
            /** @var DateCell $date_cell */

            if (($i % 7) === 0) {
                // 週初め
                print <<<EOD
<ul class="week">
EOD;
            }

            // 日付セルを表示
            $date_cell->display();

            if (($i % 7) === 6) {
                // 週末
                print <<<EOD
</ul>
EOD;
            }
        }
        print <<<EOD
</div><!-- div.month-matrix -->
EOD;
    }
}