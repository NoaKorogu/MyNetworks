<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PathRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;

#[ORM\Entity(repositoryClass: PathRepository::class)]
#[ORM\Table(name: "path")]
class Path
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $color = null;

    #[ORM\Column(type: 'geometry', options: ['geometry_type' => 'LINESTRING'], nullable: false)]
    private ?LineString $path = null;

    /**
     * @var Collection<int, PartOf>
     */
    #[ORM\ManyToMany(targetEntity: PartOf::class, mappedBy: 'path_id')]
    private Collection $partOfs;

    #[ORM\ManyToOne(inversedBy: 'paths')]
    #[ORM\JoinColumn(name: 'network_id', referencedColumnName: 'id')]
    private ?Networks $network_id = null;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getPath(): ?LineString
    {
        return $this->path;
    }

    public function setPath(?LineString $path): static
    {
        $this->path = $path;
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
            $partOf->addPathId($this);
        }
        return $this;
    }

    public function removePartOf(PartOf $partOf): static
    {
        if ($this->partOfs->removeElement($partOf)) {
            $partOf->removePathId($this);
        }
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
}
