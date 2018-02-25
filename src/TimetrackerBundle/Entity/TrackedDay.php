<?php

namespace TimetrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrackedDay
 *
 * @ORM\Table(name="trackedDay")
 * @ORM\Entity(repositoryClass="TimetrackerBundle\Repository\TrackedDayRepository")
 */
class TrackedDay
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
     * @var int
     *
     * @ORM\Column(name="day", type="integer")
     */
    private $day;

    /**
     * @var int
     *
     * @ORM\Column(name="hours", type="integer")
     */
    private $hours;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;


    /**
     * @ORM\ManyToOne(targetEntity="TrackedTask", inversedBy="trackedDays")
     * @ORM\JoinColumn(name="trackedTask_id", referencedColumnName="id")
     */
    protected $trackedTask;


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
     * Set day
     *
     * @param int $day
     *
     * @return TrackedDay
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return string
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set hours
     *
     * @param int $hours
     *
     * @return TrackedDay
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
        return $this;
    }

    /**
     * Get hours
     *
     * @return int
     */
    public function getHours()
    {
        return $this->hours;
    }



    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return TrackedDay
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set trackedTask
     *
     * @param TrackedTask $trackedTask
     * @return TrackedDay
     */
    public function setTrackedTask($trackedTask)
    {
        $this->trackedTask = $trackedTask;

        return $this;
    }

    /**
     * Get trackedTask
     *
     * @return \TimetrackerBundle\Entity\TrackedTask
     */
    public function getTrackedTask()
    {
        return $this->trackedTask;
    }
}
