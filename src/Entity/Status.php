<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $deleted = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    /**
     * @var Collection<int, OrganisationStatus>
     */
    #[ORM\OneToMany(targetEntity: OrganisationStatus::class, mappedBy: 'status')]
    private Collection $organisationStatuses;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $action = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    public function __construct()
    {
        $this->deleted = false;
        $this->organisationStatuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = strtoupper($type);

        return $this;
    }

    /**
     * @return Collection<int, OrganisationStatus>
     */
    public function getOrganisationStatuses(): Collection
    {
        return $this->organisationStatuses;
    }

    public function addOrganisationStatus(OrganisationStatus $organisationStatus): static
    {
        if (!$this->organisationStatuses->contains($organisationStatus)) {
            $this->organisationStatuses->add($organisationStatus);
            $organisationStatus->setStatus($this);
        }

        return $this;
    }

    public function removeOrganisationStatus(OrganisationStatus $organisationStatus): static
    {
        if ($this->organisationStatuses->removeElement($organisationStatus)) {
            // set the owning side to null (unless already changed)
            if ($organisationStatus->getStatus() === $this) {
                $organisationStatus->setStatus(null);
            }
        }

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
