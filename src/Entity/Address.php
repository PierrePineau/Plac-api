<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
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
    private ?string $country = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $state = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $postcode = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $street = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $compl = null;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Groups(["default"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'address')]
    private Collection $projects;

    /**
     * @var Collection<int, Client>
     */
    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'address')]
    private Collection $clients;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->uuid = Uuid::v7()->toRfc4122();
        $this->projects = new ArrayCollection();
        $this->clients = new ArrayCollection();
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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): static
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getCompl(): ?string
    {
        return $this->compl;
    }

    public function setCompl(?string $compl): static
    {
        $this->compl = $compl;

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

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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
            $project->setAddress($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getAddress() === $this) {
                $project->setAddress(null);
            }
        }

        return $this;
    }

    public function toArray(string $kind = 'default'): array
    {
        return [
            'id' => $this->getUuid(),
            'country' => $this->getCountry(),
            'state' => $this->getState(),   
            'city' => $this->getCity(),
            'postcode' => $this->getPostcode(),
            'street' => $this->getStreet(),
            'compl' => $this->getCompl(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setAddress($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getAddress() === $this) {
                $client->setAddress(null);
            }
        }

        return $this;
    }
}
