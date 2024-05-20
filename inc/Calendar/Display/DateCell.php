<?php
namespace Calendar\Display;

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

    public function display(): void
    {
        if ($this->day_of_month === 0) {
            // フィラー、空白マス
            print <<<EOD
<li class='filler'><div class='day-of-month'></div><div class='holiday-name'></div></li>
EOD;
            return;
        }

        $css_class = [];
        // 土曜日、日曜日には、それぞれ、saturday、sundayをつける
        if ($this->day_of_week === 0) {
            $css_class[] = 'sunday';
        } elseif ($this->day_of_week === 6) {
            $css_class[] = 'saturday';
        }
        if ($this->holiday_name !== '') {
            $css_class[] = 'holiday';
        }
        $css_class_str = join(' ', $css_class);

        $holiday_name = $this->holiday_name ?: '&nbsp';
        print <<<EOD
<li class="$css_class_str"><div class="day-of-month">$this->day_of_month</div><div class="holiday-name">$holiday_name</div></li>
EOD;
    }
}