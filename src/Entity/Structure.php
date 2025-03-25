<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\StructureRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: StructureRepository::class)]
// #[ORM\Entity]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "discriminator", type: "string")]
#[ORM\DiscriminatorMap(["structure" => Structure::class, "bus_stop" => BusStop::class, "water" => Water::class, "electrical" => Electrical::class])]
#[ORM\Table(name: "structure")]
class Structure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 128)]
    protected ?string $name = null;

    #[ORM\Column(length: 255)]
    protected ?string $location = null;

    #[ORM\ManyToOne(inversedBy: 'structures')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Networks $network = null;

    #[ORM\ManyToOne(inversedBy: 'structures')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Type $type = null;

    /**
     * @var Collection<int, PartOf>
     */
    #[ORM\ManyToMany(targetEntity: PartOf::class, mappedBy: 'structure_id')]
    private Collection $partOfs;

    public function __construct()
    {
        $this->partOfs = new ArrayCollection();
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getNetworkId(): ?Networks
    {
        return $this->network_id;
    }

    public function setNetworkId(?Networks $network_id): static
    {
        $this->network_id = $network_id;

        return $this;
    }

    public function getNetworkTypeId(): ?Type
    {
        return $this->type_id;
    }

    public function setNetworkTypeId(?Type $type_id): static
    {
        $this->type_id = $type_id;

        return $this;
    }

    /**
     * @return Collection<int, PartOf>
     */
    public function getPartOfs(): Collection
    {
        return $this->partOfs;
    }

    public function addPartOf(PartOf $partOf): static
    {
        if (!$this->partOfs->contains($partOf)) {
            $this->partOfs->add($partOf);
            $partOf->addStructureId($this);
        }

        return $this;
    }

    public function removePartOf(PartOf $partOf): static
    {
        if ($this->partOfs->removeElement($partOf)) {
            $partOf->removeStructureId($this);
        }

        return $this;
    }
}
