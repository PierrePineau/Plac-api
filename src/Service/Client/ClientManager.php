<?php

namespace App\Service\Client;

use App\Core\Service\AbstractCoreService;
use App\Entity\Client;
use Symfony\Bundle\SecurityBundle\Security;

class ClientManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Client',
            'entity' => Client::class,
            'security' => $security,
        ]);
    }
}