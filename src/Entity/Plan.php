<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = '';

    /**
     * @var Collection<int, Module>
     */
    #[ORM\ManyToMany(targetEntity: Module::class)]
    private Collection $modules;

    #[ORM\Column]
    private ?bool $custom = false;

    #[ORM\Column(nullable: true)]
    private ?float $monthlyPrice = null;

    #[ORM\Column(nullable: true)]
    private ?float $annualPrice = null;

    #[ORM\Column(nullable: true)]
    private ?float $maxDevices = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column]
    private ?bool $enabled = false;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
        $this->custom = false;
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
     * @return Collection<int, Module>
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(Module $module): static
    {
        if (!$this->modules->contains($module)) {
            $this->modules->add($module);
        }

        return $this;
    }

    public function removeModule(Module $module): static
    {
        $this->modules->removeElement($module);

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

    public function isCustom(): ?bool
    {
        return $this->custom;
    }

    public function setCustom(bool $custom): static
    {
        $this->custom = $custom;

        return $this;
    }

    public function getMonthlyPrice(): ?float
    {
        return $this->monthlyPrice;
    }

    public function setMonthlyPrice(?float $monthlyPrice): static
    {
        $this->monthlyPrice = $monthlyPrice;

        return $this;
    }

    public function getAnnualPrice(): ?float
    {
        return $this->annualPrice;
    }

    public function setAnnualPrice(?float $annualPrice): static
    {
        $this->annualPrice = $annualPrice;

        return $this;
    }

    public function getMaxDevices(): ?float
    {
        return $this->maxDevices;
    }

    public function setMaxDevices(?float $maxDevices): static
    {
        $this->maxDevices = $maxDevices;

        return $this;
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

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function toArray(string $kind = 'default'): array
    {
        return [
            'id' => $this->getId(),
            'reference' => $this->getReference(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'monthlyPrice' => $this->getMonthlyPrice(),
            'annualPrice' => $this->getAnnualPrice(),
            'maxDevices' => $this->getMaxDevices(),
            'position' => $this->getPosition(),
            'modules' => $this->getModules()->map(fn (Module $module) => $module->toArray())->toArray(),
            'enabled' => $this->isEnabled(),
        ];
    }
}
