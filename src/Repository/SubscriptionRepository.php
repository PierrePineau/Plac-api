<?php

namespace App\Repository;

use App\Entity\Subscription;
use App\Core\Repository\AbstractCoreRepository;
use Doctrine\Persistence\ManagerRegistry;

class SubscriptionRepository extends AbstractCoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }
}
