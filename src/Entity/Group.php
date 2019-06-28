<?php

namespace UserManagement\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="UserManagement\Repository\GroupRepository")
 * @ORM\Table(name="groups")
 * @UniqueEntity(fields={"name"},message="Group already exists with the name")
 */
class Group
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=55)
     * @Assert\NotBlank(message="Name can not be blank")
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="userGroups")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $groupUsers;

    public function __construct()
    {
        $this->groupUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getGroupUsers()
    {
        return $this->groupUsers;
    }
}
