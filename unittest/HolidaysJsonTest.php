<?php

use PHPUnit\Framework\TestCase;

class HolidaysJsonTest extends TestCase
{
    /**
     * @test
     */
    function 休日ファイルをパース()
    {
        $file_path = __DIR__ . '/../inc/holidays.json';

        $this->assertFileIsReadable($file_path);

        // 例外が飛ばない
        $json_data = file_get_contents($file_path);
        json_decode($json_data);
    }
}