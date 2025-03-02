<?php

namespace App\Entity;

use App\Repository\UserOrganisationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserOrganisationRepository::class)]
class UserOrganisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userOrganisations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userOrganisations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): static
    {
        $this->organisation = $organisation;

        return $this;
    }

    public function toArray(string $kind = 'default'): array
    {
        return [
            'id' => $this->getId(),
            'user' => $this->getUser()->toArray(),
            'organisation' => $this->getOrganisation()->toArray(),
        ];
    }
}
