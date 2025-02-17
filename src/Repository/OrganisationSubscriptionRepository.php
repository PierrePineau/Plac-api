<?php

namespace App\Repository;

use App\Entity\OrganisationSubscription;
use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganisationSubscription>
 */
class OrganisationSubscriptionRepository extends AbstractCoreRepository
{
    private $accessRelation;
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationSubscription::class);
        $this->accessRelation = 'organisationSubscriptions';
    }
}
