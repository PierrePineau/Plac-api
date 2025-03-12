<?php

namespace App\Event\User;

use App\Core\Event\AbstractCoreEvent;
use App\Entity\User;

final class UserCreateEvent extends AbstractCoreEvent
{
    private ?User $user = null;
    
    public function __construct(array $data = [])
    {   
        parent::__construct($data);
        $this->user = $data['user'] ?? null;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}