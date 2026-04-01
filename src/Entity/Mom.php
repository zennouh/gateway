<?php

namespace App\Entity;

use App\Repository\MomRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MomRepository::class)]
class Mom extends User
{
    #[ORM\Column]
    private int $numberOfChildren;

    #[ORM\Column(length: 255)]
    private string $address;

    #[ORM\Column]
    private ?int $childrenCount = null;



    public function getChildrenCount(): ?int
    {
        return $this->childrenCount;
    }

    public function setChildrenCount(int $childrenCount): static
    {
        $this->childrenCount = $childrenCount;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getNumberOfChildren(): int
    {
        return $this->numberOfChildren;
    }

    public function setNumberOfChildren(int $numberOfChildren): static
    {
        $this->numberOfChildren = $numberOfChildren;

        return $this;
    }
}
