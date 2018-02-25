<?php

namespace TimetrackerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="TimetrackerBundle\Repository\ProjectRepository")
 */
class Project
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
     * @var string
     *
     * @ORM\Column(name="purchaseOrder", type="string", length=255, nullable=true)
     */
    private $purchaseOrder;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
    * @var int
    *
    * @ORM\Column(name="user_editable", type="integer", nullable=true, options={"default":1})
    */
    private $editable = 1;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="projects", cascade={"remove"})
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    protected $company;

    /**
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="projects")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="project", cascade={"remove"})
     */
    protected $tasks;


    public function __construct()
    {
        $this->tasks = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Project
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
     * Set purchaseOrder
     *
     * @param string $purchaseOrder
     *
     * @return Project
     */
    public function setPurchaseOrder($purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    /**
     * Get purchaseOrder
     *
     * @return string
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Project
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
     * Set editable
     *
     * @param int $editable
     *
     * @return Project
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



    /**
     * Set company
     *
     * @param Company $company
     * @return Project
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \TimetrackerBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }


    /**
     * Set contact
     *
     * @param Contact $contact
     * @return Project
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \TimetrackerBundle\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Add task
     *
     * @param \TimetrackerBundle\Entity\Task $tasks
     * @return Company
     */
    public function addTask(\TimetrackerBundle\Entity\Task $task)
    {
        $this->tasks[] = $task;
        return $this;
    }

    /**
     * Remove task
     *
     * @param \TimetrackerBundle\Entity\Task $tasks
     */
    public function removeTask(\TimetrackerBundle\Entity\Task $task)
    {
        $this->tasks->removeElement($task);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}
