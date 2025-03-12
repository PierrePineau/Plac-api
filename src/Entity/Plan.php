<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
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
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = '';

    /**
     * @var Collection<int, Module>
     */
    #[ORM\ManyToMany(targetEntity: Module::class)]
    private Collection $modules;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column]
    private ?bool $custom = false;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(nullable: true)]
    private ?float $maxDevices = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column]
    private ?int $position = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column]
    private ?bool $enabled = false;

    /**
     * @var Collection<int, Subscription>
     */
    #[ORM\OneToMany(targetEntity: Subscription::class, mappedBy: 'plan')]
    private Collection $subscriptions;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeId = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[Groups(["default", "create", "update"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $renewalFrequency = null;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
        $this->custom = false;
        $this->enabled = false;
        $this->subscriptions = new ArrayCollection();
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
            'maxDevices' => $this->getMaxDevices(),
            'position' => $this->getPosition(),
            'modules' => $this->getModules()->map(fn (Module $module) => $module->toArray())->toArray(),
            'enabled' => $this->isEnabled(),
        ];
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
            $subscription->setPlan($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getPlan() === $this) {
                $subscription->setPlan(null);
            }
        }

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    public function setStripeId(?string $stripeId): static
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getRenewalFrequency(): ?string
    {
        return $this->renewalFrequency;
    }

    public function setRenewalFrequency(?string $renewalFrequency): static
    {
        $this->renewalFrequency = $renewalFrequency;

        return $this;
    }
}
