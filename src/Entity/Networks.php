<?php

namespace App\Entity;

use App\Repository\NetworksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NetworksRepository::class)]
#[ORM\Table(name: "network")]
class Networks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, Path>
     */
    #[ORM\OneToMany(targetEntity: Type::class, mappedBy: 'network_id')]
    private Collection $types;

    /**
     * @var Collection<int, Path>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'network_id')]
    private Collection $users;

    /**
     * @var Collection<int, Structure>
     */
    #[ORM\OneToMany(targetEntity: Structure::class, mappedBy: 'network_id')]
    private Collection $structures;

    /**
     * @var Collection<int, Path>
     */
    #[ORM\OneToMany(targetEntity: Path::class, mappedBy: 'network_id')]
    private Collection $paths;

    public function __construct()
    {
        $this->structures = new ArrayCollection();
        $this->paths = new ArrayCollection();
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
            $structure->setNetworkId($this);
        }

        return $this;
    }

    public function removeStructure(Structure $structure): static
    {
        if ($this->structures->removeElement($structure) && $structure->getNetworkId() === $this) {
            $structure->setNetworkId(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Path>
     */
    public function getPaths(): Collection
    {
        return $this->paths;
    }

    public function addPath(Path $path): static
    {
        if (!$this->paths->contains($path)) {
            $this->paths->add($path);
            $path->setNetworkId($this);
        }

        return $this;
    }

    public function removePath(Path $path): static
    {
        if ($this->paths->removeElement($path) && $path->getNetworkId() === $this) {
            $path->setNetworkId(null);
        }

        return $this;
    }

    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function addType(Type $type): static
    {
        if (!$this->types->contains($type)) {
            $this->types->add($type);
            $type->setNetworkId($this);
        }

        return $this;
    }

    public function removeType(Type $type): static
    {
        if ($this->types->removeElement($type) && $type->getNetworkId() === $this) {
            $type->setNetworkId(null);
        }

        return $this;
    }
}
