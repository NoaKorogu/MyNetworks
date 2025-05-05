<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "bus_stop")]
class BusStop extends Structure
{
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $line_number = null;

    public function getLineNumber(): ?string
    {
        return $this->line_number;
    }

    public function setLineNumber(string $line_number): static
    {
        $this->line_number = $line_number;
        return $this;
    }
}
