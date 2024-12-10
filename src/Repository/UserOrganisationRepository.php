<?php

namespace App\Repository;

use App\Entity\UserOrganisation;
use App\Core\Repository\AbstractCoreRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserOrganisationRepository extends AbstractCoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserOrganisation::class);
    }
}
