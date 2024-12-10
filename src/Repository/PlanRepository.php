<?php

namespace App\Repository;

use App\Entity\Plan;
use App\Core\Repository\AbstractCoreRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlanRepository extends AbstractCoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plan::class);
    }
}
