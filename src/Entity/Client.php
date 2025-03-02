<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["default"])]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?string $uuid = null;

    #[Groups(["default", "create"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    private ?bool $archived = false;

    #[Groups(["default"])]
    #[ORM\Column]
    private ?bool $deleted = false;

    /**
     * @var Collection<int, OrganisationClient>
     */
    #[ORM\OneToMany(targetEntity: OrganisationClient::class, mappedBy: 'client')]
    private Collection $organisationClients;

    /**
     * @var Collection<int, ProjectClient>
     */
    #[ORM\OneToMany(targetEntity: ProjectClient::class, mappedBy: 'client')]
    private Collection $projectClients;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\ManyToOne(inversedBy: 'clients')]
    private ?Address $address = null;

    public function __construct()
    {
        $this->uuid = Uuid::v7()->toRfc4122();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->archived = false;
        $this->deleted = false;
        $this->organisationClients = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

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

    public function isArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): static
    {
        $this->archived = $archived;

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

    /**
     * @return Collection<int, OrganisationClient>
     */
    public function getOrganisationClients(): Collection
    {
        return $this->organisationClients;
    }

    public function addOrganisationClient(OrganisationClient $organisationClient): static
    {
        if (!$this->organisationClients->contains($organisationClient)) {
            $this->organisationClients->add($organisationClient);
            $organisationClient->setClient($this);
        }

        return $this;
    }

    public function removeOrganisationClient(OrganisationClient $organisationClient): static
    {
        if ($this->organisationClients->removeElement($organisationClient)) {
            // set the owning side to null (unless already changed)
            if ($organisationClient->getClient() === $this) {
                $organisationClient->setClient(null);
            }
        }

        return $this;
    }

    public function toArray(string $kind = 'default'): array
    {
        return [
            // 'id' => $this->getId(),
            'id' => $this->getUuid(),
            'email' => $this->getEmail(),
            'name' => $this->getName(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'phone' => $this->getPhone(),
        ];
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
            $projectClient->setClient($this);
        }

        return $this;
    }

    public function removeProjectClient(ProjectClient $projectClient): static
    {
        if ($this->projectClients->removeElement($projectClient)) {
            // set the owning side to null (unless already changed)
            if ($projectClient->getClient() === $this) {
                $projectClient->setClient(null);
            }
        }

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

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
}
