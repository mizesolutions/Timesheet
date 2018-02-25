<?php

namespace TimetrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TrackedProject
 *
 * @ORM\Table(name="trackedProject")
 * @ORM\Entity(repositoryClass="TimetrackerBundle\Repository\TrackedProjectRepository")
 */
class TrackedProject
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
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    protected $project;

    /**
     * @ORM\OneToMany(targetEntity="TrackedTask", mappedBy="trackedProject", cascade={"remove"})
     */
    protected $trackedTasks;


    /**
     * @ORM\ManyToOne(targetEntity="TrackedMonth", inversedBy="trackedProjects")
     * @ORM\JoinColumn(name="trackedMonth_id", referencedColumnName="id")
     */
    protected $trackedMonth;

    /**
    * @var int
    *
    * @ORM\Column(name="user_editable", type="integer", nullable=true, options={"default":1})
    */
    private $editable = 1;


    public function __construct()
    {
        $this->trackedTasks = new ArrayCollection();
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
     * Set project
     *
     * @param Project $project
     * @return TrackedProject
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \TimetrackerBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }


    /**
     * Add trackedTask
     *
     * @param \TimetrackerBundle\Entity\TrackedTask $trackedTask
     * @return Company
     */
    public function addTrackedTask(\TimetrackerBundle\Entity\TrackedTask $trackedTask)
    {
        $this->trackedTasks[] = $trackedTask;
        return $this;
    }

    /**
     * Remove trackedTask
     *
     * @param \TimetrackerBundle\Entity\TrackedTask $trackedTask
     */
    public function removeTrackedTask(\TimetrackerBundle\Entity\TrackedTask $trackedTask)
    {
        $this->trackedTasks->removeElement($trackedTask);
    }

    /**
     * Get trackedTasks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTrackedTasks()
    {
        return $this->trackedTasks;
    }

    /**
     * Set trackedMonth
     *
     * @param TrackedMonth $trackedMonth
     *
     * @return TrackedMonth
     */
    public function setTrackedMonth($trackedMonth)
    {
        $this->trackedMonth = $trackedMonth;
        return $this;
    }

    /**
     * Get trackedMonth
     *
     * @return TrackedMonth
     */
    public function getTrackedMonth()
    {
        return $this->trackedMonth;
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
