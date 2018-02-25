<?php

namespace TimetrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TrackedTask
 *
 * @ORM\Table(name="trackedTask")
 * @ORM\Entity(repositoryClass="TimetrackerBundle\Repository\TrackedTaskRepository")
 */
class TrackedTask
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
     * @ORM\ManyToOne(targetEntity="Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    protected $task;

    /**
     * @ORM\ManyToOne(targetEntity="TrackedProject", inversedBy="trackedTasks")
     * @ORM\JoinColumn(name="trackedProject_id", referencedColumnName="id")
     */
    protected $trackedProject;


    /**
     * @ORM\OneToMany(targetEntity="TrackedDay", mappedBy="trackedTask", cascade={"remove"})
     */
    protected $trackedDays;


    /**
    * @var int
    *
    * @ORM\Column(name="user_editable", type="integer", nullable=true, options={"default":1})
    */
    private $editable = 1;


    public function __construct()
    {
        $this->trackedDays = new ArrayCollection();
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
     * Set task
     *
     * @param Task $task
     * @return TrackedTask
     */
    public function setTask($task)
    {
        $this->task = $task;
        return $this;
    }

    /**
     * Get task
     *
     * @return \TimetrackerBundle\Entity\Task
     */
    public function getTask()
    {
        return $this->task;
    }


    /**
     * Set trackedProject
     *
     * @param TrackedProject $trackedProject
     * @return TrackedProject
     */
    public function setTrackedProject($trackedProject)
    {
        $this->trackedProject = $trackedProject;
        return $this;
    }

    /**
     * Get trackedProject
     *
     * @return \TimetrackerBundle\Entity\TrackedProject
     */
    public function getTrackedProject()
    {
        return $this->trackedProject;
    }



    /**
     * Add trackedDay
     *
     * @param \TimetrackerBundle\Entity\TrackedDay $trackedDay
     * @return Company
     */
    public function addTrackedDay(\TimetrackerBundle\Entity\TrackedDay $trackedDay)
    {
        $this->trackedDays[] = $trackedDay;
        return $this;
    }

    /**
     * Remove trackedDay
     *
     * @param \TimetrackerBundle\Entity\TrackedDay $trackedDay
     */
    public function removeTrackedDay(\TimetrackerBundle\Entity\TrackedDay $trackedDay)
    {
        $this->trackedDays->removeElement($trackedDay);
    }

    /**
     * Get trackedDays
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTrackedDays()
    {
        return $this->trackedDays;
    }


    /**
     * Generate Tracked Days
     *
     * @return \TimetrackerBundle\Entity\TrackedTask $trackedTask
     */
    public function generateTrackedDays($numDays, $em)
    {
        for ($i=0; $i<$numDays; $i++) {
            $trackedDay = new TrackedDay();
            $trackedDay->setDay($i+1);
            $trackedDay->setHours(0);
            $trackedDay->setTrackedTask($this);
            $this->addTrackedDay($trackedDay);

            $em->persist($trackedDay);
        }

        $em->persist($this);
        $em->flush();
    }

    /**
     * Set editable
     *
     * @param int $editable
     *
     * @return Task
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;

        return $this;
    }


    /**
     * Get editable
     *
     * @return int
     */
    public function getEditable()
    {
        return $this->editable;
    }
}
