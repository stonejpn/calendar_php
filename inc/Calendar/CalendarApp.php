<?php
namespace Calendar;

require_once "ViewTypes.php";
require_once "WeekStartDate.php";

use Calendar\ViewType;
use Calendar\WeekStartDate;

class CalendarApp
{
    // 表示年月
    public int $display_year;
    public int $display_month;

    // １ヶ月／１年の切り替え
    public ViewType $view_type;

    // 週の始まり
    public WeekStartDate $week_start_date;

    /**
     * コンストラクタ
     */
    function __construct()
    {
        $this->view_type = ViewType::Month;

        // 現在の年月
        $today = localtime(null, true);
        $this->display_year = 1900 + $today['tm_year'];
        $this->display_month = $today['tm_mon'] + 1;

        $this->week_start_date = WeekStartDate::Sunday;
    }

    /**
     * カレンダーを表示
     */
    function display()
    {
?>
年：<?= $this->display_year ?>
月: <?= $this->display_month ?>
<?php
    }
}