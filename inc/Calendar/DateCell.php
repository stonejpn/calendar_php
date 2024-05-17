<?php
namespace Calendar;

class DateCell
{
    protected int $day_of_month;
    protected int $day_of_week; // 日:0 月:1 ... 土:6
    protected ?string $holiday_name;

    public function __construct(int $day_of_month, int $day_of_week, ?string $holiday_name = null)
    {
        $this->day_of_month = $day_of_month;
        $this->day_of_week = $day_of_week;
        $this->holiday_name = $holiday_name;
    }

    public function display(bool $is_last_day_of_week, bool $is_last_week): string
    {
        if ($this->day_of_month === 0) {
            // フィラー、空白マス
            $css_class = '';
            if ($is_last_day_of_week) {
                $css_class .= " last-day-of-week";
            }
            if ($is_last_week) {
                $css_class .= " last-week";
            }
            return "<li class=\"$css_class\"><div class=\"date\"><br/><br/><br/></div></li>";
        }

        $buffer = '<li class="';

        // 土曜日、日曜日には、それぞれ、saturday、sundayをつける
        if ($this->day_of_week === 0) {
            $buffer .= 'sunday';
        } elseif ($this->day_of_week === 6) {
            $buffer .= 'saturday';
        }
        // 一番右側のマスには、last-day-of-weekをつける
        if ($is_last_day_of_week) {
            $buffer .= " last-day-of-week";
        }
        // 一番下のマスには、last-weekをつける
        if ($is_last_week) {
            $buffer .= " last-week";
        }

        $buffer .= sprintf('"><div class="date">%d<br/>', $this->day_of_month);
        if ($this->holiday_name !== null) {
            $buffer .= $this->holiday_name;
        }
        $buffer .= '<br/><br/></div></li>';

        return $buffer;
    }
}