<?php

namespace App\Entity;

use App\Repository\PartOfRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartOfRepository::class)]
#[ORM\Table(name: "part_of")]
class PartOf
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Path>
     */
    #[ORM\ManyToMany(targetEntity: Path::class, inversedBy: 'partOfs')]
    #[ORM\JoinTable(name: 'path_part_of')] // Update with correct association table if needed
    #[ORM\JoinColumn(name: 'path_id', referencedColumnName: 'id')] // Update with correct column name
    #[ORM\InverseJoinColumn(name: 'part_of_id', referencedColumnName: 'id')] // Update with correct column name
    private Collection $path_id;

    /**
     * @var Collection<int, Structure>
     */
    #[ORM\ManyToMany(targetEntity: Structure::class, inversedBy: 'partOfs')]
    #[ORM\JoinTable(name: 'structure_part_of')] // Update with correct association table if needed
    #[ORM\JoinColumn(name: 'structure_id', referencedColumnName: 'id')] // Update with correct column name
    #[ORM\InverseJoinColumn(name: 'part_of_id', referencedColumnName: 'id')] // Update with correct column name
    private Collection $structure_id;

    public function __construct()
    {
        $this->path_id = new ArrayCollection();
        $this->structure_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Path>
     */
    public function getPathId(): Collection
    {
        return $this->path_id;
    }

    public function addPathId(Path $pathId): static
    {
        if (!$this->path_id->contains($pathId)) {
            $this->path_id->add($pathId);
        }

        return $this;
    }

    public function removePathId(Path $pathId): static
    {
        $this->path_id->removeElement($pathId);

        return $this;
    }

    /**
     * @return Collection<int, Structure>
     */
    public function getStructureId(): Collection
    {
        return $this->structure_id;
    }

    public function addStructureId(Structure $structureId): static
    {
        if (!$this->structure_id->contains($structureId)) {
            $this->structure_id->add($structureId);
        }

        return $this;
    }

    public function removeStructureId(Structure $structureId): static
    {
        $this->structure_id->removeElement($structureId);

        return $this;
    }
}
