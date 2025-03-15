<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["default"])]
    private ?int $id = null;

    #[Groups(["default"])]
    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(["default"])]
    #[ORM\Column]
    private ?bool $deleted = false;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[Groups(["default"])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    /**
     * @var Collection<int, OrganisationStatus>
     */
    #[ORM\OneToMany(targetEntity: OrganisationStatus::class, mappedBy: 'status')]
    private Collection $organisationStatuses;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $action = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'status')]
    private Collection $projects;

    public function __construct()
    {
        $this->deleted = false;
        $this->organisationStatuses = new ArrayCollection();
        $this->projects = new ArrayCollection();
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

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setStatus($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getStatus() === $this) {
                $project->setStatus(null);
            }
        }

        return $this;
    }

    public function toArray(string $kind = 'default'): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'color' => $this->getColor(),
            'type' => $this->getType(),
            'action' => $this->getAction(),
            'position' => $this->getPosition(),
        ];
    }
}
