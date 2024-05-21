<?php
namespace Calendar;

use PHPUnit\Framework\TestCase;

class CalendarAppTest extends TestCase
{
    /**
     * @test
     */
    function コンストラクタ_不正な週の始まりの指定するとRuntimeException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('週の始まり：sunday/monday以外の文字列が指定されています');

        new CalendarApp('', 'wednesday');
    }

    /**
     * @test
     */
    function コンストラクタ_有効な週の始まりを指定すれば例外は飛ばない()
    {
        $this->expectNotToPerformAssertions();
        // 日曜はじまり
        new CalendarApp('', WeekStartDate::SUNDAY);
        // 月曜始まり
        new CalendarApp('' , WeekStartDate::MONDAY);

        // 指定なし
        new CalendarApp('' , '');
    }

    /**
     * @test
     */
    function コンストラクタ_2015年より前の年が指定されていたらRuntimeException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('表示範囲外の年月が指定されました');

        new CalendarApp('/2014', '');
    }

    /**
     * @test
     */
    function コンストラクタ_2034年より後の年が指定されていたらRuntimeException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('表示範囲外の年月が指定されました');

        new CalendarApp('/2035', '');
    }

    /**
     * @test
     */
    function コンストラクタ_2015年～2034年が指定されていたら例外は飛ばない()
    {
        $this->expectNotToPerformAssertions();
        for ($year = 2015; $year < 2035; $year++) {
            new CalendarApp("/$year", '');
        }
    }

    /**
     * @test
     */
    function コンストラクタ_月に0が指定されていたらRuntimeException() {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('１～１２月以外の月が指定されています');

        new CalendarApp('/2024/0', '');
    }

    /**
     * @test
     */
    function コンストラクタ_月に13以上が指定されていたらRuntimeException() {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('１～１２月以外の月が指定されています');

        new CalendarApp('/2024/13', '');
    }

    /**
     * @test
     */
    function コンストラクタ_デフォルトのViewSettings() {
        $today = new \DateTimeImmutable();

        $app = new CalendarApp('', '');

        // view_type: ViewType::MONTH
        // year: 現在の年
        // month: 現在の月
        $ref_property = new \ReflectionProperty($app, 'settings');
        $ref_property->setAccessible(true);
        $settings = $ref_property->getValue($app);

        $this->assertEquals(ViewType::MONTH, $settings->getViewType());
        $this->assertEquals($today->format("Y"), $settings->getYear());
        $this->assertEquals($today->format("n"), $settings->getMonth());
    }

    /**
     * コンストラクタの引数バリエーション
     *
     * [
     *   $path_info, // 引数`path_info`
     *   $start_date, // 引数`start_date`
     *   $expected_year,      // 結果: year
     *   $expected_month,     // 結果: month
     *   $expected_view_type, // 結果: view_type
     *   $expected_start_date // 結果: start_date
     * ]
     *
     * @return array[]
     */
    function constrcutorProvider()
    {
        $today = new \DateTimeImmutable();
        $curr_year = (int) $today->format("Y");
        $curr_month = (int) $today->format("n");
        return [
            // デフォルトでは、今月の月別表示＋日曜はじまり
            ['', '', $curr_year, $curr_month, ViewType::MONTH, WeekStartDate::SUNDAY],
            // 日曜はじまりを指定
            ['', WeekStartDate::SUNDAY, null, null, null, WeekStartDate::SUNDAY],
            // 月曜はじまりを指定
            ['', WeekStartDate::MONDAY, null, null, null, WeekStartDate::MONDAY],
            // 表示年月に`/2023/1`を指定
            ['/2023/1', '', 2023, 1, ViewType::MONTH, null],
            // 表示年月に`/2023`を指定
            ['/2023', '', 2023, null, ViewType::YEAR, null],
        ];
    }

    /**
     * @test
     * @dataProvider constrcutorProvider
     */
    function コンストラクタ_引数バリエーション(
        string $arg_path_info,
        string $arg_start_date,
        ?int $expected_year,
        ?int $expected_month,
        ?string $expected_view_type,
        ?string $expected_start_date
    ) {
        $app = new CalendarApp($arg_path_info, $arg_start_date);
        $ref_property = new \ReflectionProperty($app, 'settings');
        $ref_property->setAccessible(true);
        $settings = $ref_property->getValue($app); /** @var ViewSettings $settings */

        if ($expected_year !== null) {
            $this->assertEquals($expected_year, $settings->getYear());
        }
        if ($expected_month !== null) {
            $this->assertEquals($expected_month, $settings->getMonth());
        }
        if ($expected_view_type !== null) {
            $this->assertEquals($expected_view_type, $settings->getViewType());
        }
        if ($expected_start_date !== null) {
            $this->assertEquals($expected_start_date, $settings->getWeekStartDate());
        }
    }
}