<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Entity\Organisation;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Organisation',
            'entity' => Organisation::class,
            'security' => $security,
        ]);
    }
}