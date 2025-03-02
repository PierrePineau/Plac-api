<?php

namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
class Module
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["default"])]
    private ?int $id = null;

    #[Groups(["default"])]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $reference = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column]
    private ?bool $enabled = false;

    /**
     * @var Collection<int, OrganisationModule>
     */
    #[ORM\OneToMany(targetEntity: OrganisationModule::class, mappedBy: 'module')]
    private Collection $organisationModules;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column]
    private ?int $position = null;

    public function __construct()
    {
        $this->organisationModules = new ArrayCollection();
        $this->enabled = false;
    }

    public function getId(): ?int
    {
        return $this->id;
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
    
    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

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
            $organisationModule->setModule($this);
        }

        return $this;
    }

    public function removeOrganisationModule(OrganisationModule $organisationModule): static
    {
        if ($this->organisationModules->removeElement($organisationModule)) {
            // set the owning side to null (unless already changed)
            if ($organisationModule->getModule() === $this) {
                $organisationModule->setModule(null);
            }
        }

        return $this;
    }

    public function toArray(string $kind = 'default'): array
    {
        return [
            'id' => $this->getId(),
            'reference' => $this->getReference(),
            'name' => $this->getName(),
            'position' => $this->getPosition(),
            'enabled' => $this->isEnabled(),
        ];
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
