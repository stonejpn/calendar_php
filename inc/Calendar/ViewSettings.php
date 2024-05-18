<?php
namespace Calendar;

class ViewSettings
{
    protected int $year;
    protected ?int $month;
    protected ?ViewType $view_type;
    protected ?WeekStartDate $week_start_date;

    public function __construct(int  $year, ?int $month, ?ViewType $type, ?WeekStartDate $start)
    {
        $this->year = $year;
        $this->month = $month;
        $this->view_type = $type;
        $this->week_start_date = $start;
    }

    /**
     * @return int
     */
    public function getYear():int
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getMonth():int
    {
        return $this->month;
    }

    /**
     * @return ViewType
     */
    public function getViewType():ViewType
    {
        return $this->view_type;
    }

    /**
     * @return WeekStartDate
     */
    public function getWeekStartDate(): WeekStartDate
    {
        return $this->week_start_date;
    }

    public function modifyMonth($month):ViewSettings
    {
        return new self($this->year, $month, $this->view_type, $this->week_start_date);
    }
}