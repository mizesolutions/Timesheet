<?php

namespace TimetrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 *
 * @ORM\Table(name="task")
 * @ORM\Entity(repositoryClass="TimetrackerBundle\Repository\TaskRepository")
 */
class Task
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
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="tasks")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    protected $project;


    /**
    * @var int
    *
    * @ORM\Column(name="user_editable", type="integer", nullable=true, options={"default":1})
    */
    private $editable = 1;


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
     * Set name
     *
     * @param string $name
     *
     * @return Task
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set project
     *
     * @param Project $project
     * @return Project
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
