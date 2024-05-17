<?php
namespace Calendar;

class DateCell
{
    protected int $day_of_month;
    protected int $day_of_week; // 日:0 月:1 ... 土:6
    protected ?string $holiday_name;

    public function __construct(int $day_of_month, int $day_of_week, ?string $holiday_name = '')
    {
        $this->day_of_month = $day_of_month;
        $this->day_of_week = $day_of_week;
        $this->holiday_name = $holiday_name;
    }

    public function display(ViewType $view_type, bool $is_last_day_of_week, bool $is_last_week): void
    {
        $br_buffer = "";
        if ($view_type === ViewType::Month) {
            $br_buffer = '<br/>';
        }
        if ($this->day_of_month === 0) {
            // フィラー、空白マス
            $css_class = '';
            if ($is_last_day_of_week) {
                $css_class .= " last-day-of-week";
            }
            if ($is_last_week) {
                $css_class .= " last-week";
            }
            print "<li class=\"$css_class\"><div class=\"date\"><br/><br/>$br_buffer</div></li>";
            return;
        }

        $css_class = [];
        // 土曜日、日曜日には、それぞれ、saturday、sundayをつける
        if ($this->day_of_week === 0) {
            $css_class[] = 'sunday';
        } elseif ($this->day_of_week === 6) {
            $css_class[] = 'saturday';
        }
        // 一番右側のマスには、last-day-of-weekをつける
        if ($is_last_day_of_week) {
            $css_class[] = " last-day-of-week";
        }
        // 一番下のマスには、last-weekをつける
        if ($is_last_week) {
            $css_class[] = " last-week";
        }
        $css_class_str = join(' ', $css_class);

        print <<<EOD
<li class="$css_class_str"><div class="date">$this->day_of_month<br/>$this->holiday_name<br/>$br_buffer</div></li>
EOD;
    }
}