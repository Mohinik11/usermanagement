<?php

namespace UserManagement\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="UserManagement\Repository\UserRepository")
 * @UniqueEntity(fields={"name"},message="User already exists with the name")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank(message="Name can not be blank")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $city;

    /**
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="groupUsers")
     * @ORM\JoinTable(name="user_group")
     */
    private $userGroups;

    public function __construct()
    {
        $this->userGroups = new ArrayCollection();
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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function addUserGroups(Group $group)
    {
        if($this->userGroups->contains($group)) {
            return;
        }
        $this->userGroups[] = $group;
    }

    public function removeUserGroups(Group $group)
    {
        $this->userGroups->removeElement($group);
    }

    /**
     * @return ArrayCollection|Group[]
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }
}
