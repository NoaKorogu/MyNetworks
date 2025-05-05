<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
#[ORM\Table(name: "type")]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, Structure>
     */
    #[ORM\OneToMany(targetEntity: Structure::class, mappedBy: 'type_id')]  // Updated to match the relationship
    private Collection $structures;

    #[ORM\ManyToOne(targetEntity: Networks::class, inversedBy: 'types')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Networks $network = null;


    public function __construct()
    {
        $this->structures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Structure>
     */
    public function getStructures(): Collection
    {
        return $this->structures;
    }

    public function addStructure(Structure $structure): static
    {
        if (!$this->structures->contains($structure)) {
            $this->structures->add($structure);
            $structure->setNetworkTypeId($this);  // Make sure this reflects the correct naming
        }

        return $this;
    }

    public function removeStructure(Structure $structure): static
    {
        if ($this->structures->removeElement($structure) && $structure->getNetworkTypeId() === $this) {
                $structure->setNetworkTypeId(null);
        }

        return $this;
    }

    public function getNetwork(): ?Networks
    {
        return $this->network;
    }

    public function setNetwork(?Networks $network): static
    {
        $this->network = $network;

        return $this;
    }
}
