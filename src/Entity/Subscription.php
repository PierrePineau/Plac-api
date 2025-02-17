<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, OrganisationSubscription>
     */
    #[ORM\OneToMany(targetEntity: OrganisationSubscription::class, mappedBy: 'subscription')]
    private Collection $organisationSubscriptions;

    public function __construct()
    {
        $this->organisationSubscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, OrganisationSubscription>
     */
    public function getOrganisationSubscriptions(): Collection
    {
        return $this->organisationSubscriptions;
    }

    public function addOrganisationSubscription(OrganisationSubscription $organisationSubscription): static
    {
        if (!$this->organisationSubscriptions->contains($organisationSubscription)) {
            $this->organisationSubscriptions->add($organisationSubscription);
            $organisationSubscription->setSubscription($this);
        }

        return $this;
    }

    public function removeOrganisationSubscription(OrganisationSubscription $organisationSubscription): static
    {
        if ($this->organisationSubscriptions->removeElement($organisationSubscription)) {
            // set the owning side to null (unless already changed)
            if ($organisationSubscription->getSubscription() === $this) {
                $organisationSubscription->setSubscription(null);
            }
        }

        return $this;
    }
}
