<?php
namespace Calendar;

use Calendar\ViewType;
use Calendar\WeekStartDate;
use Calendar\MonthMatrix;

class CalendarApp
{
    // 表示年月
    protected int $display_year;
    protected int $display_month;

    // １ヶ月／１年の切り替え
    protected ViewType $view_type;

    // 週の始まり
    protected WeekStartDate $week_start_date;

    // コンストラクタ中のエラー
    protected bool $error = false;

    /**
     * コンストラクタ
     */
    function __construct(?string $path_info)
    {
        $this->view_type = ViewType::Month;

        // 現在の年月
        $today = localtime(null, true);
        $this->display_year = 1900 + $today['tm_year'];
        $this->display_month = $today['tm_mon'] + 1;

        $this->week_start_date = WeekStartDate::Sunday;

        if (strlen($path_info) !== 0) {
            // '' と null はstrlenが0になる
            $elem = explode('/', $path_info);

            $this->display_year = $elem[1];
            $this->display_month = $elem[2];

            if ($this->display_year < 2015 || $this->display_year > 2034) {
                $this->error = true;
            }
            if ($this->display_month < 1 || $this->display_month > 12) {
                $this->error = true;
            }
        }
    }

    /**
     * カレンダーを表示
     */
    function display()
    {
        if ($this->error) {
?>
            <html>
            <head>
              <title>カレンダー <?= $this->display_year ?>年 <?= $this->display_month ?>月</title>
              <link rel="stylesheet" href="/style.css" type="text/css" />
            </head>
            <body>
            <div class="title"><?= $this->display_year ?>年　<?= $this->display_month ?>月</div>
            <div class="error">エラーが起きました。</div>
            </body>
            </html>
<?php
            return;
        }

        $matrix = new MonthMatrix($this->display_year, $this->display_month, $this->week_start_date);

        // 前の月
        $prev_buffer = '';
        $year = $this->display_year;
        $month = $this->display_month - 1;
        if ($month === 0) {
          $year--;
          $month = 12;
        }
        // 2015年より前の期間は表示しなくていい
        if ($year < 2015 ) {
            $prev_buffer = '<br/>';
        } else {
            $prev_buffer = sprintf('<a href="/%d/%02d">&lt;&lt; 前の月</a>', $year, $month);
        }

        // 次の月
        $next_buffer = '';
        $year = $this->display_year;
        $month = $this->display_month + 1;
        if ($month > 12) {
            $year++;
            $month = 1;
        }
        // 2034年より後の期間は表示しなくていい
        if ($year > 2034) {
            $next_buffer = '<br/>';
        } else {
            $next_buffer = sprintf('<a href="/%d/%02d">次の月 &gt;&gt;</a>', $year, $month);
        }
?>
        <html>
        <head>
          <title>カレンダー <?= $this->display_year ?>年 <?= $this->display_month ?>月</title>
          <link rel="stylesheet" href="/style.css" type="text/css" />
        </head>
        <body>
        <div class="title"><?= $this->display_year ?>年　<?= $this->display_month ?>月</div>
        <div class="switcher">
          <form id="change-start-date" method="POST" action="/">
            <span><label><input type="radio" name="start_date" value="sunday" <?php if ($this->week_start_date == WeekStartDate::Sunday) { print "checked"; } ?>/>日曜始まり</label></span>
            <span><label><input type="radio" name="start_date" value="monday" <?php if ($this->week_start_date == WeekStartDate::Monday) { print "checked"; } ?>/>月曜始まり</label></span>
          </form>
        </div>
        <div class="container">
          <ul class="navi">
            <li class="prev"><?= $prev_buffer ?></li>
            <li class="next"><?= $next_buffer ?></li>
          </ul>
          <ul class="day-of-week-header">
            <li class="sunday header">Sun</li><li class="header">Mon</li><li class="header">Tue</li><li class="header">Wed</li><li class="header">Thu</li><li class="header">Fri</li><li class="saturday header last-day-of-week">Sat</li>
          </ul>
<?php
        $buffer = '';
        $last_week_day = $matrix->getCount() - 6;
        foreach ($matrix as $i => $date_cell) {
            /** @var DateCell $date_cell */

            if (($i % 7) === 0) {
                $buffer .= '<ul class="week">';
            }
            $buffer .= $date_cell->display(($i % 7) === 6, $i >= $last_week_day);
            if (($i % 7) === 6) {
                $buffer .= '</ul>';
            }
        }
?>
          <?= $buffer ?>
        </div>
        </body>
        </html>
<?php
    }
}