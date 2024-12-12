<?php

namespace App\Service\User;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\User;
use App\Entity\UserOrganisation;
use Symfony\Bundle\SecurityBundle\Security;

class UserOrganisationManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'User.Organisation',
            'entity' => UserOrganisation::class,
            'security' => $security,
        ]);
    }
}