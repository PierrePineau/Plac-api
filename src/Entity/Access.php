<?php

namespace App\Entity;

use App\Repository\AccessRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AccessRepository::class)]
class Access
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["default", "delete"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $uuid = null;

    #[Groups(["default"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[Groups(["default"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entityIdentifier = null;

    #[Groups(["default"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entityId = null;

    public function __construct()
    {
        $this->uuid = Uuid::v7()->toRfc4122();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getEntityIdentifier(): ?string
    {
        return $this->entityIdentifier;
    }

    public function setEntityIdentifier(?string $entityIdentifier): static
    {
        $this->entityIdentifier = $entityIdentifier;

        return $this;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(?string $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function toArray(string $kind = 'default'): array
    {
        return [
            'id' => $this->getUuid(),
            // 'type' => $this->getType(),
            // 'entityIdentifier' => $this->getEntityIdentifier(),
            // 'entityId' => $this->getEntityId(),
        ];
    }
}
