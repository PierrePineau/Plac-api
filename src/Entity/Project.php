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

    #[ORM\Column(unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
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

    /**
     * @var Collection<int, OrganisationProject>
     */
    #[ORM\OneToMany(targetEntity: OrganisationProject::class, mappedBy: 'project')]
    private Collection $organisationProjects;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    private ?bool $deleted = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, ProjectClient>
     */
    #[ORM\OneToMany(targetEntity: ProjectClient::class, mappedBy: 'project')]
    private Collection $projectClients;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?Address $address = null;

    public function __construct()
    {
        $this->projectNotes = new ArrayCollection();
        $this->uuid = Uuid::v7()->toRfc4122();
        $this->projectFiles = new ArrayCollection();
        $this->organisationProjects = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->deleted = false;
        $this->projectClients = new ArrayCollection();
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

    /**
     * @return Collection<int, OrganisationProject>
     */
    public function getOrganisationProjects(): Collection
    {
        return $this->organisationProjects;
    }

    public function addOrganisationProject(OrganisationProject $organisationProject): static
    {
        if (!$this->organisationProjects->contains($organisationProject)) {
            $this->organisationProjects->add($organisationProject);
            $organisationProject->setProject($this);
        }

        return $this;
    }

    public function removeOrganisationProject(OrganisationProject $organisationProject): static
    {
        if ($this->organisationProjects->removeElement($organisationProject)) {
            // set the owning side to null (unless already changed)
            if ($organisationProject->getProject() === $this) {
                $organisationProject->setProject(null);
            }
        }

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

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, ProjectClient>
     */
    public function getProjectClients(): Collection
    {
        return $this->projectClients;
    }

    public function addProjectClient(ProjectClient $projectClient): static
    {
        if (!$this->projectClients->contains($projectClient)) {
            $this->projectClients->add($projectClient);
            $projectClient->setProject($this);
        }

        return $this;
    }

    public function removeProjectClient(ProjectClient $projectClient): static
    {
        if ($this->projectClients->removeElement($projectClient)) {
            // set the owning side to null (unless already changed)
            if ($projectClient->getProject() === $this) {
                $projectClient->setProject(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function toArray(string $kind = 'default'): array
    {
        $defaultData = [
            // 'id' => $this->getId(),
            'id' => $this->getUuid(),
            'reference' => $this->getReference(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
            'deleted' => $this->isDeleted(),
        ];
        
        if (in_array($kind, ['get', 'update', 'add'])) {
            $data = array_merge(
                $defaultData,
                [
                    'address' => $this->getAddress()?->toArray(),
                    'status' => $this->getStatus()?->toArray(),
                ]
            );
        }else{
            $data = array_merge(
                $defaultData,
                [
                ]
            );
        }

        return $data;
    }
}
