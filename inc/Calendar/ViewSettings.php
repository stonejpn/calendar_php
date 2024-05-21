<?php
namespace Calendar;

class ViewSettings
{
    protected int $year;
    protected ?int $month;
    protected string $view_type;
    protected string $week_start_date;

    public function __construct(int  $year, ?int $month, string $type, string $start)
    {
        $this->year = $year;
        $this->month = $month;
        $this->view_type = $type;
        $this->week_start_date = $start;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getViewType(): string
    {
        return $this->view_type;
    }

    public function getWeekStartDate(): string
    {
        return $this->week_start_date;
    }

    public function modifyMonth($month): ViewSettings
    {
        return new self($this->year, $month, $this->view_type, $this->week_start_date);
    }
}