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

        $title_str = "{$this->settings->getYear()}年";
        if ($this->settings->getViewType() === ViewType::Month) {
            $title_str .= " {$this->settings->getMonth()}月";
        }
        print <<<EOD
<!doctype html>
<html lang="ja">
<head>
  <title>カレンダー {$this->settings->getYear()}年 {$this->settings->getMonth()}月</title>
  <link rel="stylesheet" href="/style.css" type="text/css" />
  <script src="/calendar.js"></script>
</head>
<body class="calendar">
  <div class="title">$title_str</div>
EOD;

        if ($this->settings->getViewType() === ViewType::Month) {
            $this->displayMonth();
        } elseif ($this->settings->getViewType() === ViewType::Year) {
            $this->displayYear();
        } else {
            print <<<EOD
<div class="error">
  <div>エラーが起きました</div>
  <div>DEBUG: 不正なViewTypeの値です</div>
 </div>
EOD;
        }

        print <<<EOD
</body>
</html>
EOD;
    }

    /**
     * 月別カレンダーを表示する
     *
     * @return void
     */
    protected function displayMonth(): void
    {
        print <<<EOD
<div class="container container-month">
EOD;

        // 前の月
        $prev_buffer = '<br/>';
        $year = $this->settings->getYear();
        $month = $this->settings->getMonth() - 1;
        if ($month === 0) {
            $year--;
            $month = 12;
        }
        if ($year >= 2015 ) {
            $prev_buffer = sprintf('<a href="/%d/%02d">&lt;&lt; %d年 %d月</a>', $year, $month, $year, $month);
        }

        // 次の月
        $next_buffer = '<br/>';
        $year = $this->settings->getYear();
        $month = $this->settings->getMonth() + 1;
        if ($month > 12) {
            $year++;
            $month = 1;
        }
        if ($year <= 2034) {
            $next_buffer = sprintf('<a href="/%d/%02d">%d年 %d月 &gt;&gt;</a>', $year, $month, $year, $month);
        }

        print <<<EOD
  <ul class="navi">
    <li class="prev">$prev_buffer</li>
    <li class="year"><a href="/{$this->settings->getYear()}">{$this->settings->getYear()}年 年間カレンダー</a></li>
    <li class="next">$next_buffer</li>
  </ul>
EOD;
        // 曜日はじまりのスイッチ
        $this->displaySwitcher($this->settings->getWeekStartDate());

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
        switch ($this->settings->getWeekStartDate()) {
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
        $matrix = new MonthMatrix($this->settings);
        $matrix->display($this->settings);

        print <<<EOD
</div> <!-- div.container -->
EOD;

    }

    /**
     * 年間カレンダーを表示する
     *
     * @return void
     */
    protected function displayYear(): void
    {
        print <<<EOD
<div class='container container-year'>
EOD;

        // ナビゲーションを表示：前の年／次の年
        $prev_buffer = '<br/>';
        if ($this->settings->getYear() > 2015) {
            $year = $this->settings->getYear() - 1;
            $prev_buffer = "<a href=\"/$year\">&lt;&lt; {$year}年</a>";
        }
        $next_buffer = '<br/>';
        if ($this->settings->getYear() < 2034) {
            $year = $this->settings->getYear() + 1;
            $next_buffer = "<a href=\"$year\">{$year}年 &gt;&gt;</a>";
        }
        print <<<EOD
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
            $matrix->display($settings);

            if (($month % 3) === 0) {
                // year-row
                print '</div>';
            }
        }

        print <<<EOD
</div> <!-- div.container -->
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
    <span><label><input id="change-start-date-sunday" type="radio" name="start_date" value="sunday" $sunday_checked/>日曜はじまり</label></span>
    <span><label><input id="change-start-date-monday" type="radio" name="start_date" value="monday" $monday_checked/>月曜はじまり</label></span>
  </form>
</div>
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