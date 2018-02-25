<?php

namespace TimetrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Holiday
 *
 * @ORM\Table(name="holiday")
 * @ORM\Entity(repositoryClass="TimetrackerBundle\Repository\HolidayRepository")
 */
class Holiday
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
     * @var string
     * @Assert\NotBlank();
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     * @Assert\NotBlank();
     * @ORM\Column(name="year", type="integer")
     */
    private $year;

    /**
     * @var int
     * @Assert\NotBlank();
     * @ORM\Column(name="month", type="integer")
     */
    private $month;

    /**
     * @var int
     * @Assert\NotBlank();
     * @ORM\Column(name="day", type="integer")
     */
    private $day;

    /**
     * @var int
     *
     * @ORM\Column(name="hours", type="integer")
     */
    private $hours;



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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Holiday
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


    /**
     * Get Year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Holiday
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }


    /**
     * Get month
     *
     * @return integer
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set month
     *
     * @param integer $month
     *
     * @return Holiday
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }


    /**
     * Get day
     *
     * @return integer
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set day
     *
     * @param integer $day
     *
     * @return Holiday
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }


    /**
     * Get hours
     *
     * @return integer
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set hours
     *
     * @param integer $hours
     *
     * @return Holiday
     */
    public function setHours($hours)
    {
        $this->hours = $hours;

        return $this;
    }
}
