<?php

namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
class Module
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $enable = false;

    /**
     * @var Collection<int, OrganisationModule>
     */
    #[ORM\OneToMany(targetEntity: OrganisationModule::class, mappedBy: 'module')]
    private Collection $organisationModules;

    public function __construct()
    {
        $this->organisationModules = new ArrayCollection();
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

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

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

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'reference' => $this->getReference(),
            'name' => $this->getName(),
            'enable' => $this->isEnable(),
        ];
    }
}
