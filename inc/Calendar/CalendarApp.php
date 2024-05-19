<?php
namespace Calendar;

class CalendarApp
{
    // 表示セッティング
    protected ViewSettings $settings;

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


        if (strlen($start_date) && $start_date !== 'sunday' && $start_date !== 'monday') {
            throw new \RuntimeException('週の始まり：sunday/monday以外の文字列が指定されています');
        }

        if (strlen($path_info) !== 0) {
            // '' と null はstrlenが0になる
            $elem = explode('/', $path_info);

            $year = $elem[1];
            if ($year < 2015 || $year > 2034) {
                throw new \RuntimeException('表示範囲外の年月が指定されました');
            }
            if (!array_key_exists(2, $elem) || strlen($elem[2]) === 0) {
                $view_type = ViewType::Year;
            } else {
                $month = $elem[2];

                if ($month < 1 || $month > 12) {
                    throw new \RuntimeException('１～１２月以外の月が指定されています');
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
        $page = Display\Page::create($this->settings);
        if ($page !== null) {
            // 休日ファイルを読み込み
            $holidays_json = file_get_contents(__DIR__ . '/../holidays.json');
            $holidays = json_decode($holidays_json, true);

            $page->display($this->settings, $holidays[(string) $this->settings->getYear()]);
        } else {
            Display\ErrorPage::display($this->settings, '不正なViewTypeの値です');
        }
    }
}