<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use App\Entity\OrganisationStatus;
use Doctrine\Persistence\ManagerRegistry;

class OrganisationStatusRepository extends AbstractCoreRepository
{
    private $accessRelation;
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationStatus::class);
        $this->accessRelation = 'organisationStatus';
    }
}
