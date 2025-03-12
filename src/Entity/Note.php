<?php

namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["default"])]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?string $uuid = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, ProjectNote>
     */
    #[ORM\OneToMany(targetEntity: ProjectNote::class, mappedBy: 'note')]
    private Collection $projectNotes;

    /**
     * @var Collection<int, OrganisationNote>
     */
    #[ORM\OneToMany(targetEntity: OrganisationNote::class, mappedBy: 'note')]
    private Collection $organisationNotes;

    #[ORM\Column]
    private ?bool $deleted = false;

    public function __construct()
    {
        $this->projectNotes = new ArrayCollection();
        $this->uuid = Uuid::v7()->toRfc4122();
        $this->organisationNotes = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->deleted = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ProjectNote>
     */
    public function getProjectNotes(): Collection
    {
        return $this->projectNotes;
    }

    public function addProjectNote(ProjectNote $projectNote): static
    {
        if (!$this->projectNotes->contains($projectNote)) {
            $this->projectNotes->add($projectNote);
            $projectNote->setNote($this);
        }

        return $this;
    }

    public function removeProjectNote(ProjectNote $projectNote): static
    {
        if ($this->projectNotes->removeElement($projectNote)) {
            // set the owning side to null (unless already changed)
            if ($projectNote->getNote() === $this) {
                $projectNote->setNote(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrganisationNote>
     */
    public function getOrganisationNotes(): Collection
    {
        return $this->organisationNotes;
    }

    public function addOrganisationNote(OrganisationNote $organisationNote): static
    {
        if (!$this->organisationNotes->contains($organisationNote)) {
            $this->organisationNotes->add($organisationNote);
            $organisationNote->setNote($this);
        }

        return $this;
    }

    public function removeOrganisationNote(OrganisationNote $organisationNote): static
    {
        if ($this->organisationNotes->removeElement($organisationNote)) {
            // set the owning side to null (unless already changed)
            if ($organisationNote->getNote() === $this) {
                $organisationNote->setNote(null);
            }
        }

        return $this;
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

    public function toArray(string $kind = 'default'): array
    {
        return [
            'id' => $this->getUuid(),
            'name' => $this->getName(),
            'content' => $this->getContent(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
    }
}
