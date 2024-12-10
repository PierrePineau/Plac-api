<?php

namespace App\Repository;

use App\Entity\OrganisationSubscription;
use App\Core\Repository\AbstractCoreRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganisationSubscription>
 */
class OrganisationSubscriptionRepository extends AbstractCoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationSubscription::class);
    }
}
