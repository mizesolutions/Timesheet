<?php

namespace TimetrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarMonth
 *
 * @ORM\Table(name="calendarMonth")
 * @ORM\Entity(repositoryClass="TimetrackerBundle\Repository\CalendarMonthRepository")
 */
class CalendarMonth
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var currentMonth
     *
     * @ORM\Column(name="currentMonth", type="integer")
     */
    private $currentMonth;


    /**
     * @var currentYear
     *
     * @ORM\Column(name="currentYear", type="integer")
     */
    private $currentYear;

    /**
     * @var startDay
     *
     * @ORM\Column(name="startDay", type="integer")
     */
    private $startDay;

    /**
     * @var startMonth
     *
     * @ORM\Column(name="startMonth", type="integer")
     */
    private $startMonth;

    /**
     * @var startYear
     *
     * @ORM\Column(name="startYear", type="integer")
     */
    private $startYear;


    /**
     * @var endDay
     *
     * @ORM\Column(name="endDay", type="integer")
     */
    private $endDay;

    /**
     * @var endMonth
     *
     * @ORM\Column(name="endMonth", type="integer")
     */
    private $endMonth;

    /**
     * @var endYear
     *
     * @ORM\Column(name="endYear", type="integer")
     */
    private $endYear;


    public function __construct()
    {
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * Set currentMonth
     *
     * @param integer $currentMonth
     *
     * @return CalendarMonth
     */
    public function setCurrentMonth($currentMonth)
    {
        $this->currentMonth = $currentMonth;

        return $this;
    }

    /**
     * Get currentMonth
     *
     * @return integer
     */
    public function getCurrentMonth()
    {
        return $this->currentMonth;
    }

    /**
     * Set currentYear
     *
     * @param integer $currentYear
     *
     * @return CalendarMonth
     */
    public function setCurrentYear($currentYear)
    {
        $this->currentYear = $currentYear;

        return $this;
    }

    /**
     * Get currentYear
     *
     * @return integer
     */
    public function getCurrentYear()
    {
        return $this->currentYear;
    }

    /**
     * Set startDay
     *
     * @param integer $startDay
     *
     * @return CalendarMonth
     */
    public function setStartDay($startDay)
    {
        $this->startDay = $startDay;

        return $this;
    }

    /**
     * Get startDay
     *
     * @return integer
     */
    public function getStartDay()
    {
        return $this->startDay;
    }

    /**
     * Set startMonth
     *
     * @param integer $startMonth
     *
     * @return CalendarMonth
     */
    public function setStartMonth($startMonth)
    {
        $this->startMonth = $startMonth;

        return $this;
    }

    /**
     * Get startMonth
     *
     * @return integer
     */
    public function getStartMonth()
    {
        return $this->startMonth;
    }

    /**
     * Set startYear
     *
     * @param integer $startYear
     *
     * @return CalendarMonth
     */
    public function setStartYear($startYear)
    {
        $this->startYear = $startYear;

        return $this;
    }

    /**
     * Get startYear
     *
     * @return integer
     */
    public function getStartYear()
    {
        return $this->startYear;
    }

    /**
     * Set endDay
     *
     * @param integer $endDay
     *
     * @return CalendarMonth
     */
    public function setEndDay($endDay)
    {
        $this->endDay = $endDay;

        return $this;
    }

    /**
     * Get endDay
     *
     * @return integer
     */
    public function getEndDay()
    {
        return $this->endDay;
    }

    /**
     * Set endMonth
     *
     * @param integer $endMonth
     *
     * @return CalendarMonth
     */
    public function setEndMonth($endMonth)
    {
        $this->endMonth = $endMonth;

        return $this;
    }

    /**
     * Get endMonth
     *
     * @return integer
     */
    public function getEndMonth()
    {
        return $this->endMonth;
    }

    /**
     * Set endYear
     *
     * @param integer $endYear
     *
     * @return CalendarMonth
     */
    public function setEndYear($endYear)
    {
        $this->endYear = $endYear;

        return $this;
    }

    /**
     * Get endYear
     *
     * @return integer
     */
    public function getEndYear()
    {
        return $this->endYear;
    }
}
