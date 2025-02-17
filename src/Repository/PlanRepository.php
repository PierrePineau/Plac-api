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

    public function search(array $search = [], bool $countMode = false)
    {
        $settings = $this->configureSearch($search);
        $query = $this->createNewQueryBuilder();

        if (isset($search['search']) && $search['search'] != '') {
            $query = $query
                ->andWhere("{$this->alias}.name LIKE :search OR {$this->alias}.reference LIKE :search")
                ->setParameter('search', "%{$search['search']}%");
        }
        if (isset($search['isSuperAdmin']) && $search['isSuperAdmin']) {
        }else{
            $query = $query
                ->andWhere("{$this->alias}.enabled = 1");
        }

        if (!$countMode) {
            $query = $query
                ->orderBy("{$this->alias}.position", "ASC")
                ->setMaxResults($settings['limit'])
                ->setFirstResult($settings['offset']);

            return $query->getQuery()
                ->getResult();
        }else{
            $query = $query->select("COUNT({$this->alias}.id)");
            return $query->getQuery()
                ->getSingleScalarResult();
        }
    }
}
