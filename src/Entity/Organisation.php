<?php

namespace App\Entity;

use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
class Organisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["default"])]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?string $uuid = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, UserOrganisation>
     */
    #[ORM\OneToMany(targetEntity: UserOrganisation::class, mappedBy: 'organisation')]
    private Collection $userOrganisations;

    /**
     * @var Collection<int, OrganisationModule>
     */
    #[ORM\OneToMany(targetEntity: OrganisationModule::class, mappedBy: 'organisation')]
    private Collection $organisationModules;

    /**
     * @var Collection<int, OrganisationFile>
     */
    #[ORM\OneToMany(targetEntity: OrganisationFile::class, mappedBy: 'organisation')]
    private Collection $organisationFiles;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, OrganisationClient>
     */
    #[ORM\OneToMany(targetEntity: OrganisationClient::class, mappedBy: 'organisation')]
    private Collection $organisationClients;

    /**
     * @var Collection<int, OrganisationProject>
     */
    #[ORM\OneToMany(targetEntity: OrganisationProject::class, mappedBy: 'organisation')]
    private Collection $organisationProjects;

    /**
     * @var Collection<int, OrganisationNote>
     */
    #[ORM\OneToMany(targetEntity: OrganisationNote::class, mappedBy: 'organisation')]
    private Collection $organisationNotes;

    /**
     * @var Collection<int, OrganisationStatus>
     */
    #[ORM\OneToMany(targetEntity: OrganisationStatus::class, mappedBy: 'organisation')]
    private Collection $organisationStatuses;

    #[Groups(["default"])]
    #[ORM\Column]
    private ?bool $deleted = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Subscription $currentSubscription = null;

    /**
     * @var Collection<int, Subscription>
     */
    #[ORM\OneToMany(targetEntity: Subscription::class, mappedBy: 'organisation')]
    private Collection $subscriptions;

    #[ORM\ManyToOne]
    private ?User $owner = null;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $freeTrialEndAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::v7()->toRfc4122();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->userOrganisations = new ArrayCollection();
        $this->organisationModules = new ArrayCollection();
        $this->organisationFiles = new ArrayCollection();
        $this->organisationClients = new ArrayCollection();
        $this->organisationProjects = new ArrayCollection();
        $this->organisationNotes = new ArrayCollection();
        $this->organisationStatuses = new ArrayCollection();
        $this->deleted = false;
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->uuid;
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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, UserOrganisation>
     */
    public function getUserOrganisations(): Collection
    {
        return $this->userOrganisations;
    }

    public function addUserOrganisation(UserOrganisation $userOrganisation): static
    {
        if (!$this->userOrganisations->contains($userOrganisation)) {
            $this->userOrganisations->add($userOrganisation);
            $userOrganisation->setOrganisation($this);
        }

        return $this;
    }

    public function removeUserOrganisation(UserOrganisation $userOrganisation): static
    {
        if ($this->userOrganisations->removeElement($userOrganisation)) {
            // set the owning side to null (unless already changed)
            if ($userOrganisation->getOrganisation() === $this) {
                $userOrganisation->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrganisationModule>
     */
    public function getOrganisationModules(): Collection
    {
        return $this->organisationModules;
    }

    public function addOrganisationModule(OrganisationModule $organisationModule): static
    {
        if (!$this->organisationModules->contains($organisationModule)) {
            $this->organisationModules->add($organisationModule);
            $organisationModule->setOrganisation($this);
        }

        return $this;
    }

    public function removeOrganisationModule(OrganisationModule $organisationModule): static
    {
        if ($this->organisationModules->removeElement($organisationModule)) {
            // set the owning side to null (unless already changed)
            if ($organisationModule->getOrganisation() === $this) {
                $organisationModule->setOrganisation(null);
            }
        }

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
            $organisationFile->setOrganisation($this);
        }

        return $this;
    }

    public function removeOrganisationFile(OrganisationFile $organisationFile): static
    {
        if ($this->organisationFiles->removeElement($organisationFile)) {
            // set the owning side to null (unless already changed)
            if ($organisationFile->getOrganisation() === $this) {
                $organisationFile->setOrganisation(null);
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

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

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
            $organisationClient->setOrganisation($this);
        }

        return $this;
    }

    public function removeOrganisationClient(OrganisationClient $organisationClient): static
    {
        if ($this->organisationClients->removeElement($organisationClient)) {
            // set the owning side to null (unless already changed)
            if ($organisationClient->getOrganisation() === $this) {
                $organisationClient->setOrganisation(null);
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
            $organisationProject->setOrganisation($this);
        }

        return $this;
    }

    public function removeOrganisationProject(OrganisationProject $organisationProject): static
    {
        if ($this->organisationProjects->removeElement($organisationProject)) {
            // set the owning side to null (unless already changed)
            if ($organisationProject->getOrganisation() === $this) {
                $organisationProject->setOrganisation(null);
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
            $organisationNote->setOrganisation($this);
        }

        return $this;
    }

    public function removeOrganisationNote(OrganisationNote $organisationNote): static
    {
        if ($this->organisationNotes->removeElement($organisationNote)) {
            // set the owning side to null (unless already changed)
            if ($organisationNote->getOrganisation() === $this) {
                $organisationNote->setOrganisation(null);
            }
        }

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
            $organisationStatus->setOrganisation($this);
        }

        return $this;
    }

    public function removeOrganisationStatus(OrganisationStatus $organisationStatus): static
    {
        if ($this->organisationStatuses->removeElement($organisationStatus)) {
            // set the owning side to null (unless already changed)
            if ($organisationStatus->getOrganisation() === $this) {
                $organisationStatus->setOrganisation(null);
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

    public function getCurrentSubscription(): ?Subscription
    {
        return $this->currentSubscription;
    }

    public function setCurrentSubscription(?Subscription $currentSubscription): static
    {
        $this->currentSubscription = $currentSubscription;

        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setOrganisation($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getOrganisation() === $this) {
                $subscription->setOrganisation(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function toArray(string $kind = 'default'): array
    {
        return [
            // 'id' => $this->getId(),
            'uuid' => $this->getUuid(),
            'name' => $this->getName(),
            'freeTrialEndAt' => $this->getFreeTrialEndAt(),
            'deleted' => $this->isDeleted(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
            'deletedAt' => $this->getDeletedAt(),
        ];
    }

    // Utilisé au moment de la connection
    public function getInfos(): array
    {
        return [
            // 'id' => $this->getId(),
            'id' => $this->getUuid(),
            'name' => $this->getName(),
            'freeTrialEndAt' => $this->getFreeTrialEndAt(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
    }

    public function getFreeTrialEndAt(): ?\DateTimeInterface
    {
        return $this->freeTrialEndAt;
    }

    public function setFreeTrialEndAt(?\DateTimeInterface $freeTrialEndAt): static
    {
        $this->freeTrialEndAt = $freeTrialEndAt;

        return $this;
    }
}
