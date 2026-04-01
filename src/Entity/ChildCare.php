<?php

namespace App\Entity;

use App\Repository\ChildcareRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChildcareRepository::class)]
class ChildCare extends User
{
    #[ORM\Column]
    private int $capacity;

    #[ORM\Column(length: 255)]
    private ?string $degree = null;

    #[ORM\Column(type: 'float')]
    private float $pricePerHour;


    public function getPricePerHour(): float
    {
        return $this->pricePerHour;
    }

    public function setPricePerHour(float $pricePerHour): static
    {
        $this->pricePerHour = $pricePerHour;

        return $this;
    }

    public function getDegree(): ?string
    {
        return $this->degree;
    }

    public function setDegree(string $degree): static
    {
        $this->degree = $degree;

        return $this;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }
}
