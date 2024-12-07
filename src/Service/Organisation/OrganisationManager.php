<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Entity\Organisation;

class OrganisationManager extends AbstractCoreService
{
    private $passwordHash;
    public function __construct($container, $entityManager)
    {
        $this->passwordHash = $passwordHash;
        parent::__construct($container, $entityManager, [
            'code' => 'Organisation',
            'entity' => Organisation::class,
        ]);
    }
}