<?php

namespace App\Event\User;

use App\Core\Event\AbstractCoreEvent;
use App\Entity\User;
use App\Event\AbstractDemoEvent;

final class UserGetEvent extends AbstractDemoEvent
{
    private ?User $user = null;
    
    public function __construct(array $data = [])
    {   
        parent::__construct($data);
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