<?php

namespace UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
* Role
*
* @ORM\Table(name="role")
* @ORM\Entity
*/
class Role implements RoleInterface
{
    /**
    * @ORM\ManyToMany(targetEntity="User", mappedBy="userRoles")
    *
    */
    private $users;

    /**
    * @var integer
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


    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /*
    * methods for RoleInterface
    */
    public function getRole()
    {
        $this->name;
    }

    /**
    * Get id
    *
    * @return integer
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Set name
    *
    * @param string $name
    * @return Role
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
    * Add users
    *
    * @param \UserBundle\Entity\User $users
    * @return Role
    */
    public function addUser(\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
    * Remove users
    *
    * @param \UserBundle\Entity\User $users
    */
    public function removeUser(\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
    * Get users
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getUsers()
    {
        return $this->users;
    }
}
