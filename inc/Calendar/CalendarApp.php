<?php
namespace Calendar;

class CalendarApp
{
    // 表示セッティング
    protected ViewSettings $settings;

    // コンストラクタ中のエラー
    protected bool $error = false;
    protected string $error_message = '';

    /**
     * コンストラクタ
     */
    public function __construct(string $path_info, string $start_date)
    {
        $view_type = ViewType::Month;

        // 現在の年月
        $today = localtime(null, true);
        $year = 1900 + $today['tm_year'];
        $month = $today['tm_mon'] + 1;


        if ($start_date !== 'sunday'&& $start_date !== 'monday') {
            $this->error = false;
            $this->error_message = '週の始まり：sunday/monday以外の文字列が指定されています';
        }

        if (strlen($path_info) !== 0) {
            // '' と null はstrlenが0になる
            $elem = explode('/', $path_info);

            $year = $elem[1];
            if ($year < 2015 || $year > 2034) {
                $this->error = true;
                $this->error_message = '表示範囲外の年月が指定されました';
            }
            if (!array_key_exists(2, $elem) || strlen($elem[2]) === 0) {
                $view_type = ViewType::Year;
            } else {
                $month = $elem[2];

                if ($month < 1 || $month > 12) {
                    $this->error = true;
                    $this->error_message = '１～１２月以外の月が指定されています';
                }
            }
        }

        $this->settings = new ViewSettings($year, $month, $view_type, $start_date === 'monday' ? WeekStartDate::Monday : WeekStartDate::Sunday);
    }

    /**
     * カレンダーを表示
     */
    public function display(): void
    {
        if ($this->error) {
            $this->displayError();
            return;
        }

        if ($this->settings->getViewType() === ViewType::Month) {
            $this->displayMonth();
        } elseif ($this->settings->getViewType() === ViewType::Year) {
            $this->displayYear();
        } else {
            $this->error_message = 'DEBUG: 不正なViewTypeの値です';
            $this->displayError();
        }
    }

    /**
     * 月別カレンダーを表示する
     *
     * @return void
     */
    protected function displayMonth(): void
    {
        // <body>タグまでの共通部分を表示
        $this->displayHead();

        // 前の月
        $year = $this->settings->getYear();
        $month = $this->settings->getMonth() - 1;
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
        $year = $this->settings->getYear();
        $month = $this->settings->getMonth() + 1;
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

        print <<<EOD
        <div class="title">{$this->settings->getYear()}年　{$this->settings->getMonth()}月</div>
        <div class="container container-month">
          <ul class="navi">
            <li class="prev">$prev_buffer</li>
            <li class="year"><a href="/{$this->settings->getYear()}">{$this->settings->getYear()}年 年間カレンダー</a></li>
            <li class="next">$next_buffer</li>
          </ul>
EOD;
        $this->displaySwitcher($this->settings->getWeekStartDate());

        // 曜日名ヘッダー
        print '<ul class="day-of-week-header">';
        if ($this->settings->getWeekStartDate() === WeekStartDate::Sunday) {
            print '<li class="sunday header">日</li>';
        }
        print '<li class="header">月</li><li class="header">火</li><li class="header">水</li><li class="header">木</li><li class="header">金</li>';
        if ($this->settings->getWeekStartDate() === WeekStartDate::Sunday) {
            print '<li class="saturday header last-day-of-week">土</li>';
        } elseif ($this->settings->getWeekStartDate() === WeekStartDate::Monday) {
            print '<li class="saturday header">土</li><li class="sunday header last-day-of-week">日</li>';
        }
        print "</ul>";

        // 日付セルを表示
        $matrix = new MonthMatrix($this->settings);
        $this->displayMatrix($matrix, $this->settings);

        $this->displayFooter();
    }

    /**
     * 年間カレンダーを表示する
     *
     * @return void
     */
    protected function displayYear(): void
    {
        $this->displayHead();

        // 前の年／次の年
        $prev_buffer = '&nbsp;';
        if ($this->settings->getYear() > 2015) {
            $year = $this->settings->getYear() - 1;
            $prev_buffer = "<a href=\"/$year\">{$year}年</a>";
        }
        $next_buffer = '&nbsp;';
        if ($this->settings->getYear() < 2034) {
            $year = $this->settings->getYear() + 1;
            $next_buffer = "<a href=\"$year\">{$year}年</a>";
        }
        print <<<EOD
        <div class="title">{$this->settings->getYear()}年</div>
        <div class="container container-year">
          <ul class="navi">
            <li class="prev">$prev_buffer</li>
            <li class="year">&nbsp;</li>
            <li class="next">$next_buffer</li>
          </ul>
        EOD;

        $this->displaySwitcher($this->settings->getWeekStartDate());

        /**
         * ３×４で表示させる
         */
        for ($month = 1; $month <= 12; $month++) {
            if (($month % 3) === 1) {
                print '<div class="year-row">';
            }

            $settings = new ViewSettings(
                $this->settings->getYear(),
                $month,
                $this->settings->getViewType(),
                $this->settings->getWeekStartDate()
            );
            $matrix = new MonthMatrix($settings);
            $this->displayMatrix($matrix, $settings);

            if (($month % 3) === 0) {
                // year-row
                print '</div>';
            }
        }
        $this->displayFooter();
    }

    /**
     * bodyタグまでの共通部分を表示する
     *
     * @return void
     */
    protected function displayHead(): void
    {
        print <<<EOD
          <!doctype html>
          <html lang="ja">
          <head>
            <title>カレンダー {$this->settings->getYear()}年 {$this->settings->getMonth()}月</title>
            <link rel="stylesheet" href="/style.css" type="text/css" />
            <script src="/calendar.js"></script>
          </head>
          <body class="calendar">
        EOD;
    }

    /**
     * 日曜はじまり・月曜はじまりの切り替え
     *
     * @param WeekStartDate $week_start_date
     * @return void
     */
    protected function displaySwitcher(WeekStartDate $week_start_date): void
    {
        $sunday_checked = ($week_start_date == WeekStartDate::Sunday) ? "checked" : "";
        $monday_checked = ($week_start_date == WeekStartDate::Monday) ? "checked" : "";
        print <<<EOD
        <div class="switcher">
          <form>
            <span><label><input id="change-start-date-sunday" type="radio" name="start_date" value="sunday" $sunday_checked/>日曜始まり</label></span>
            <span><label><input id="change-start-date-monday" type="radio" name="start_date" value="monday" $monday_checked/>月曜始まり</label></span>
          </form>
        </div>
EOD;
    }

    /**
     * 月ごとのカレンダーを表示する
     *
     * @param MonthMatrix $matrix
     * @param ViewSettings $settings
     *
     * @return void
     */
    protected function displayMatrix(MonthMatrix $matrix, ViewSettings $settings): void
    {
        print '<div class="month-container">';
        if ($settings->getViewType() === ViewType::Year) {
            print "<div class=\"month-name\"><a href=\"/{$settings->getYear()}/{$settings->getMonth()}\">{$settings->getMonth()}月</a></div>";
        }

        $last_week_day = $matrix->getCount() - 7;
        foreach ($matrix as $i => $date_cell) {
            /** @var DateCell $date_cell */

            if (($i % 7) === 0) {
                print '<ul class="week">';
            }
            $date_cell->display($this->settings->getViewType(), ($i % 7) === 6, $i >= $last_week_day);
            if (($i % 7) === 6) {
                print '</ul>';
            }
        }
        print '</div>';
    }

    protected function displayFooter(): void {
        print <<<EOD
          </div> <!-- /div.container -->
          </body>
          </html>
        EOD;
    }

    protected function displayError(): void
    {
        print <<<EOD
        <!doctype html>
        <html lang="ja">
        <head>
          <title>カレンダー {$this->settings->getYear()}年 {$this->settings->getMonth()}月</title>
          <link rel="stylesheet" href="/style.css" type="text/css" />
        </head>
        <body class="error">
        <div class="title">{$this->settings->getYear()}年　{$this->settings->getMonth()}月</div>
        <div class="error">
          <div>エラーが起きました</div>
          <div>$this->error_message</div>
        </div>
        </body>
        </html>
EOD;
    }
}