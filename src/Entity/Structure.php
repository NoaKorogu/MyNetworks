<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\StructureRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

#[ORM\Entity(repositoryClass: StructureRepository::class)]
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deleted_at = null;

    #[ORM\Column(length: 128)]
    protected ?string $name = null;

    #[ORM\Column(type: 'geometry', options: ['geometry_type' => 'POINT', 'srid' => 4326], nullable: true)]
    protected ?Point $location = null;

    #[ORM\ManyToOne(inversedBy: 'structures')]
    #[ORM\JoinColumn(name: 'network_id', referencedColumnName: 'id', nullable: false)]
    protected ?Networks $network_id = null;

    #[ORM\ManyToOne(inversedBy: 'structures')]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id',nullable: false)]
    protected ?Type $type_id = null;

    /**
     * @var Collection<int, PartOf>
     */
    #[ORM\ManyToMany(targetEntity: PartOf::class, mappedBy: 'structure_id')]
    private Collection $partOfs;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;


    public function __construct()
    {
        $this->partOfs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(): void
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeInterface $deleted_at): static
    {
        $this->deleted_at = $deleted_at;
        return $this;
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

    public function getLocation(): ?Point
    {
        return $this->location;
    }

    public function setLocation(Point $location): static
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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $user): static
    {
        $this->createdBy = $user;

        return $this;
    }
}
