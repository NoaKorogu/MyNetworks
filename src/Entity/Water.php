<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "water")]
class Water extends Structure
{
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $water_pressure = null;

    #[ORM\Column(type: "boolean", nullable: true,  options: ["default" => true])]
    private bool $is_open = true;

    public function getWaterPressure(): ?string
    {
        return $this->water_pressure;
    }

    public function setWaterPressure(?string $water_pressure): static
    {
        $this->water_pressure = $water_pressure;
        return $this;
    }

    public function isOpen(): bool
    {
        return $this->is_open;
    }

    public function setOpen(bool $is_open): static
    {
        $this->is_open = $is_open;
        return $this;
    }
}
