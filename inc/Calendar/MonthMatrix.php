<?php
namespace Calendar;

class MonthMatrix implements \IteratorAggregate
{
    protected array $matrix;
    protected ?int $day_count;

    public function __construct(ViewSettings $settings)
    {
        $END_OF_DAY = [
            /* １月 */ 31,
            /* ２月 */ null,
            /* ３月 */ 31,
            /* ４月 */ 30,
            /* ５月 */ 31,
            /* ６月 */ 30,
            /* ７月 */ 31,
            /* ８月 */ 31,
            /* ９月 */ 30,
            /* １０月 */ 31,
            /* １１月 */ 30,
            /* １２月 */ 31
        ];

        $this->matrix = [];

        // 月初めの１日に設定
        $first_day_time = strtotime(sprintf("%d/%02d/01", $settings->getYear(), $settings->getMonth()));
        $first_day = localtime($first_day_time, true);

        /**
         * 最初のフィラーを決める
         *
         * １日の曜日によって、最初の空白マスの個数を決める。
         *
         * ▼例１：１日が水曜日
         *
         * 　日曜はじまり
         * 　日　月　火　水　木　金　土
         * 　□　□　□　１　２　３　４　　--> フィラーは３つ
         *
         * 　月曜はじまり
         * 　月　火　水　木　金　土　日
         * 　□　□　１　２　３　４　５　　--> フィラーは２つ
         *
         * ▼例２：１日が日曜日
         *
         * 　日曜はじまり
         * 　日　月　火　水　木　金　土
         * 　１　２　３　４　５　６　７　　--> フィラーなし
         *
         * 　月曜はじまり
         * 　月　火　水　木　金　土　日
         * 　□　□　□　□　□　□　１
         * 　２　３　４　５　６　７　８　　--> フィラー６つ
         *
         */
        $filler_count = ($settings->getWeekStartDate() === WeekStartDate::Monday) ? ($first_day['tm_wday'] + 6) % 7 : $first_day['tm_wday'];
        while ($filler_count > 0) {
            $this->matrix[] = new DateCell(0, 0);
            $filler_count--;
        }

        // 月の日数を判定
        $this->day_count = $END_OF_DAY[$settings->getMonth() - 1];
        if ($this->day_count === null)
        {
            /**
             * ２月の最終日
             *
             * 閏年の計算するより、「３月１日の前日」で判定してしまった方がわかりやすい
             */
            $last_day_time = strtotime(sprintf("%d/%02d/01", $settings->getYear(), $settings->getMonth() + 1)) - 86400;
            $last_day = localtime($last_day_time, true);
            $this->day_count = $last_day['tm_mday'];
        }

        for ($day = 1; $day <= $this->day_count; $day++)
        {
            $this->matrix[] = new DateCell($day, ($first_day['tm_wday'] + $day - 1) % 7);
        }

        while ((count($this->matrix) % 7) !== 0) {
            $this->matrix[] = new DateCell(0, 0);
        }
    }

    public function display(ViewSettings $settings): void
    {
        print '<div class="month-container">';
        if ($settings->getViewType() === ViewType::Year) {
            print "<div class=\"month-name\"><a href=\"/{$settings->getYear()}/{$settings->getMonth()}\">{$settings->getMonth()}月</a></div>";
        }

        $last_week_day = $this->getCount() - 7;
        foreach ($this->matrix as $i => $date_cell) {
            /** @var DateCell $date_cell */

            if (($i % 7) === 0) {
                print '<ul class="week">';
            }
            $date_cell->display($settings->getViewType(), ($i % 7) === 6, $i >= $last_week_day);
            if (($i % 7) === 6) {
                print '</ul>';
            }
        }
        print '</div>';
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->matrix);
    }

    public function getCount(): int
    {
        return count($this->matrix);
    }
}