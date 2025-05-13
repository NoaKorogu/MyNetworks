<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\LogRepository;

#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $table_name = null;
    
    #[ORM\Column]
    private ?int $id_element = null;

    #[ORM\Column(type: Types::JSON)]
    private array $old_data = [];

    #[ORM\Column(type: Types::JSON)]
    private array $new_data = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTableName(): ?string
    {
        return $this->table_name;
    }

    public function setTableName(string $table_name): static
    {
        $this->table_name = $table_name;

        return $this;
    }

    public function getIdElement(): ?int
    {
        return $this->id_element;
    }

    public function setIdElement(int $id_element): static
    {
        $this->id_element = $id_element;

        return $this;
    }

    public function getOldData(): array
    {
        return $this->old_data;
    }

    public function setOldData(array $old_data): static
    {
        $this->old_data = $old_data;

        return $this;
    }

    public function getNewData(): array
    {
        return $this->new_data;
    }

    public function setNewData(array $new_data): static
    {
        $this->new_data = $new_data;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setModifieddAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function createNewLog(string $table_name, int $id_element, array $old_data, array $new_data, User $user): static
    {

        $this->table_name = $table_name;
        $this->id_element = $id_element;
        $this->old_data = $old_data;
        $this->new_data = $new_data;
        $this->updated_at = new \DateTimeImmutable();
        $this->user = $user;

        return $this;
    }
}
