<?php

namespace App\Service\Client;

use App\Core\Service\AbstractCoreService;
use App\Entity\Client;

class ClientManager extends AbstractCoreService
{
    private $passwordHash;
    public function __construct($container, $entityManager)
    {
        $this->passwordHash = $passwordHash;
        parent::__construct($container, $entityManager, [
            'code' => 'Client',
            'entity' => Client::class,
        ]);
    }
}