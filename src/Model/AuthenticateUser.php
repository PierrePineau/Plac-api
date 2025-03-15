<?php

namespace App\Model;

use App\Entity\Admin;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticateUser implements UserInterface
{
    private $id;
    private $email;
    private $roles = [];
    private $type;

    public function __construct($user)
    {
        if ($user && ($user instanceof User || $user instanceof Admin)) {
            $this->id = $user->getId();
            $this->email = $user->getEmail();
            $this->roles = $user->getRoles();
            // $this->ref = 
            $this->type = $user instanceof Admin ? 'admin' : 'user';
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials(): void
    {
        // Logic to erase the user credentials
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function isAuthenticate(): bool
    {
        return !empty($this->id);
    }

    public function isEmploye(): bool
    {
        // Un employé est un utilisateur qui possède le rôle ROLE_EMPLOYE
        return in_array('ROLE_EMPLOYE', $this->roles);
    }

    public function isOnlyUser(): bool
    {
        return in_array('ROLE_USER', $this->roles) && count($this->roles) === 1;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles) || in_array('ROLE_SUPER_ADMIN', $this->roles);
    }

    public function isSuperAdmin(): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $this->roles) && $this->type === 'admin';
    }

}