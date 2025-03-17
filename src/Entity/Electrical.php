<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "electrical")]
class Electrical extends Structure
{
    #[ORM\Column(length: 50)]
    private ?string $capacity = null;

    public function getCapacity(): ?string
    {
        return $this->capacity;
    }

    public function setCapacity(string $capacity): static
    {
        $this->capacity = $capacity;
        return $this;
    }
}
