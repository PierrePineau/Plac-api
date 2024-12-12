<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, ProjectNote>
     */
    #[ORM\OneToMany(targetEntity: ProjectNote::class, mappedBy: 'project')]
    private Collection $projectNotes;

    /**
     * @var Collection<int, ProjectFile>
     */
    #[ORM\OneToMany(targetEntity: ProjectFile::class, mappedBy: 'project')]
    private Collection $projectFiles;

    public function __construct()
    {
        $this->projectNotes = new ArrayCollection();
        $this->uuid = Uuid::v7()->toRfc4122();
        $this->projectFiles = new ArrayCollection();
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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'uuid' => $this->getUuid(),
            'name' => $this->getName(),
        ];
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
            $projectNote->setProject($this);
        }

        return $this;
    }

    public function removeProjectNote(ProjectNote $projectNote): static
    {
        if ($this->projectNotes->removeElement($projectNote)) {
            // set the owning side to null (unless already changed)
            if ($projectNote->getProject() === $this) {
                $projectNote->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProjectFile>
     */
    public function getProjectFiles(): Collection
    {
        return $this->projectFiles;
    }

    public function addProjectFile(ProjectFile $projectFile): static
    {
        if (!$this->projectFiles->contains($projectFile)) {
            $this->projectFiles->add($projectFile);
            $projectFile->setProject($this);
        }

        return $this;
    }

    public function removeProjectFile(ProjectFile $projectFile): static
    {
        if ($this->projectFiles->removeElement($projectFile)) {
            // set the owning side to null (unless already changed)
            if ($projectFile->getProject() === $this) {
                $projectFile->setProject(null);
            }
        }

        return $this;
    }
}
