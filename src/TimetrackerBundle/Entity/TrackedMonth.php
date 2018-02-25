<?php

namespace TimetrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use UserBundle\Entity\User;

/**
 * TrackedMonth
 *
 * @ORM\Table(name="trackedMonth")
 * @ORM\Entity(repositoryClass="TimetrackerBundle\Repository\TrackedMonthRepository")
 */
class TrackedMonth
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
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="trackedMonths")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var int
     *
     * @ORM\Column(name="month", type="integer")
     */
    private $month;

    /**
     * @var int
     *
     * @ORM\Column(name="year", type="integer")
     */
    private $year;


    /**
     * @ORM\OneToMany(targetEntity="TrackedProject", mappedBy="trackedMonth")
     */
    protected $trackedProjects;




    public function __construct()
    {
        $this->trackedProjects = new ArrayCollection();
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
     * Set user
     *
     * @param User $user
     *
     * @return TrackedMonth
     */
    public function setUser(\UserBundle\Entity\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set month
     *
     * @param string $month
     *
     * @return TrackedMonth
     */
    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    /**
     * Get month
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set year
     *
     * @param string $year
     *
     * @return TrackedMonth
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * Get year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }



    /**
     * Add trackedProject
     *
     * @param \TimetrackerBundle\Entity\TrackedProject $trackedProject
     * @return Company
     */
    public function addTrackedProject(\TimetrackerBundle\Entity\TrackedProject $trackedProject)
    {
        $this->trackedProjects[] = $trackedProject;
        return $this;
    }

    /**
     * Remove trackedProject
     *
     * @param \TimetrackerBundle\Entity\TrackedProject $trackedProject
     */
    public function removeTrackedProject(\TimetrackerBundle\Entity\TrackedProject $trackedProject)
    {
        $this->trackedProjects->removeElement($trackedProject);
    }

    /**
     * Get trackedProjects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTrackedProjects()
    {
        return $this->trackedProjects;
    }
}
