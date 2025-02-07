<?php

namespace App\Service\Employe;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\EmployeTrait;
use App\Entity\EmployeOrganisation;
use Symfony\Bundle\SecurityBundle\Security;

class EmployeOrganisationManager extends AbstractCoreService
{
    use EmployeTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Employe.Organisation',
            'entity' => EmployeOrganisation::class,
            'security' => $security,
        ]);
    }
    // TODO : Access Employe, récupération des organisations
}