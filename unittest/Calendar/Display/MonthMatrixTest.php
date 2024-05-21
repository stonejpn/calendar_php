<?php
namespace Calendar\Display;

use PHPUnit\Framework\TestCase;

use Calendar\ViewSettings;
use Calendar\ViewType;
use Calendar\WeekStartDate;

class MonthMatrixTest extends TestCase
{
    protected ViewSettings $settings;

    function setUp(): void
    {
        $this->settings = new ViewSettings(
            2023,
            1,
            ViewType::MONTH,
            WeekStartDate::SUNDAY
        );
    }

    /**
     * @test
     */
    function コンストラクタ_matrixは7の倍数()
    {
        // 細かいことは置いといて、matrixのサイズは、7の倍数になる
        $month_matrix = new MonthMatrix($this->settings, []);
        $matrix = $this->forceGetProperty($month_matrix, 'matrix');

        $this->assertEquals(0, count($matrix) % 7);
    }

    /**
     * @test
     */
    function コンストラクタ_月初までのフィラーの数()
    {
        // 2023年1月1日は、日曜日

        /*
         * 2023年1月 + 日曜はじまり => フィラーは０
         */
        $settings = new ViewSettings(
            2023,
            1,
            ViewType::MONTH,
            WeekStartDate::SUNDAY
        );

        $month_matrix = new MonthMatrix($settings, []);
        $matrix = $this->forceGetProperty($month_matrix, 'matrix');
        /** @var DateCell[] $matrix */

        $date = $this->forceGetProperty($matrix[0], 'day_of_month');
        $this->assertEquals(1, $date);

        /*
         * 2023年1月 + 月曜はじまり => フィラーは6つ
         */
        $settings = new ViewSettings(
            2023,
            1,
            ViewType::MONTH,
            WeekStartDate::MONDAY
        );
        $month_matrix = new MonthMatrix($settings, []);
        $matrix = $this->forceGetProperty($month_matrix, 'matrix');

        $filler_count = 0;
        for ($i = 0; $i < 7; $i++) {
            $date = $this->forceGetProperty($matrix[$i], 'day_of_month');
            if ($date != 0) {
                break;
            }

            $filler_count++;
        }
        $this->assertEquals(6, $filler_count);
    }

    /**
     * @test
     */
    function コンストラクタ_月末以降のフィラーの()
    {
        // 2023年12月31日が日曜日

        /*
         * 2023年12月 + 日曜はじまり => フィラーは6つ
         */
        $settings = new ViewSettings(
          2023,
          12,
          ViewType::MONTH,
          WeekStartDate::SUNDAY
        );
        $month_matrix = new MonthMatrix($settings, []);
        $matrix = $this->forceGetProperty($month_matrix, 'matrix');

        $matrix = array_reverse($matrix);
        $filler_count = 0;
        for ($i = 0; $i < 7; $i++) {
            $date = $this->forceGetProperty($matrix[$i], 'day_of_month');
            if ($date != 0) {
                break;
            }

            $filler_count++;
        }
        $this->assertEquals(6, $filler_count);

        /*
         * 2023年12月 + 月曜はじまり => フィラーは0
         */
        $settings = new ViewSettings(
            2023,
            12,
            ViewType::MONTH,
            WeekStartDate::MONDAY
        );
        $month_matrix = new MonthMatrix($settings, []);
        $matrix = $this->forceGetProperty($month_matrix, 'matrix');
        $date = $this->forceGetProperty(array_pop($matrix), 'day_of_month');
        $this->assertNotEquals(0, $date);
    }


    /**
     * publicでないプロパティを取得する
     *
     * @param $instance
     * @param $prop_name
     * @return mixed
     * @throws \ReflectionException
     */
    private function forceGetProperty($instance, $prop_name)
    {
        $ref_prop = new \ReflectionProperty($instance, $prop_name);
        $ref_prop->setAccessible(true);
        return $ref_prop->getValue($instance);
    }
}