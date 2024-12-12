<?php

namespace App\Entity;

use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
class Organisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, UserOrganisation>
     */
    #[ORM\OneToMany(targetEntity: UserOrganisation::class, mappedBy: 'organisation')]
    private Collection $userOrganisations;

    #[ORM\OneToOne(mappedBy: 'organisation', cascade: ['persist', 'remove'])]
    private ?OrganisationSubscription $organisationSubscription = null;

    /**
     * @var Collection<int, OrganisationModule>
     */
    #[ORM\OneToMany(targetEntity: OrganisationModule::class, mappedBy: 'organisation')]
    private Collection $organisationModules;

    /**
     * @var Collection<int, Employe>
     */
    #[ORM\ManyToMany(targetEntity: Employe::class, mappedBy: 'organisations')]
    private Collection $employes;

    /**
     * @var Collection<int, OrganisationFile>
     */
    #[ORM\OneToMany(targetEntity: OrganisationFile::class, mappedBy: 'organisation')]
    private Collection $organisationFiles;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, OrganisationClient>
     */
    #[ORM\OneToMany(targetEntity: OrganisationClient::class, mappedBy: 'organisation')]
    private Collection $organisationClients;

    public function __construct()
    {
        $this->uuid = Uuid::v7()->toRfc4122();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->userOrganisations = new ArrayCollection();
        $this->organisationModules = new ArrayCollection();
        $this->employes = new ArrayCollection();
        $this->organisationFiles = new ArrayCollection();
        $this->organisationClients = new ArrayCollection();
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

    public function getOrganisationSubscription(): ?OrganisationSubscription
    {
        return $this->organisationSubscription;
    }

    public function setOrganisationSubscription(OrganisationSubscription $organisationSubscription): static
    {
        // set the owning side of the relation if necessary
        if ($organisationSubscription->getOrganisation() !== $this) {
            $organisationSubscription->setOrganisation($this);
        }

        $this->organisationSubscription = $organisationSubscription;

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
     * @return Collection<int, Employe>
     */
    public function getEmployes(): Collection
    {
        return $this->employes;
    }

    public function addEmploye(Employe $employe): static
    {
        if (!$this->employes->contains($employe)) {
            $this->employes->add($employe);
            $employe->addOrganisation($this);
        }

        return $this;
    }

    public function removeEmploye(Employe $employe): static
    {
        if ($this->employes->removeElement($employe)) {
            $employe->removeOrganisation($this);
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

    public function toArray(): array
    {
        return [
            // 'id' => $this->getId(),
            'id' => $this->getUuid(),
            'name' => $this->getName(),
        ];
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
}
