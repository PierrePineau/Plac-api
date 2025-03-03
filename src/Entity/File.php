<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["default"])]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?string $uuid = null;

    #[Groups(["default"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(["default"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(nullable: true)]
    private ?array $meta = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ext = null;

    #[Groups(["default"])]
    #[ORM\Column(nullable: true)]
    private ?float $size = null;

    /**
     * @var Collection<int, OrganisationFile>
     */
    #[ORM\OneToMany(targetEntity: OrganisationFile::class, mappedBy: 'file')]
    private Collection $organisationFiles;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $viewedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, ProjectFile>
     */
    #[ORM\OneToMany(targetEntity: ProjectFile::class, mappedBy: 'file')]
    private Collection $projectFiles;

    public function __construct()
    {
        $this->uuid = Uuid::v7()->toRfc4122();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->organisationFiles = new ArrayCollection();
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->url;
    }

    public function setPath(?string $url): static
    {
        $this->url = $url;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function setMeta(?array $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function setExt(?string $ext): static
    {
        $this->ext = $ext;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(?float $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return Collection<int, OrganisationFile>
     */
    public function getOrganisationFiles(): Collection
    {
        return $this->organisationFiles;
    }

    public function addOrganisationFile(OrganisationFile $organisationFile): static
    {
        if (!$this->organisationFiles->contains($organisationFile)) {
            $this->organisationFiles->add($organisationFile);
            $organisationFile->setFile($this);
        }

        return $this;
    }

    public function removeOrganisationFile(OrganisationFile $organisationFile): static
    {
        if ($this->organisationFiles->removeElement($organisationFile)) {
            // set the owning side to null (unless already changed)
            if ($organisationFile->getFile() === $this) {
                $organisationFile->setFile(null);
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

    public function getViewedAt(): ?\DateTimeInterface
    {
        return $this->viewedAt;
    }

    public function setViewedAt(?\DateTimeInterface $viewedAt): static
    {
        $this->viewedAt = $viewedAt;

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
            $projectFile->setFile($this);
        }

        return $this;
    }

    public function removeProjectFile(ProjectFile $projectFile): static
    {
        if ($this->projectFiles->removeElement($projectFile)) {
            // set the owning side to null (unless already changed)
            if ($projectFile->getFile() === $this) {
                $projectFile->setFile(null);
            }
        }

        return $this;
    }
    
    public function toArray(string $kind = 'default'): array
    {
        return [
            'id' => $this->getUuid(),
            'url' => $this->getUrl(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'meta' => $this->getMeta(),
            'ext' => $this->getExt(),
            'size' => $this->getSize(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
    }
}
