<?php

namespace App\Repository;

use App\Entity\Status;
use App\Core\Repository\AbstractCoreRepository;
use Doctrine\Persistence\ManagerRegistry;

class StatusRepository extends AbstractCoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    public function findOneByOrganisationByActionByType(array $option): array
    {
        $organisation = $option['organisation'];
        $action = $option['action'];
        $type = $option['type'];
        return $this->createQueryBuilder('s')
            ->leftJoin('s.organisationStatuses', 'os')
            ->andWhere('os.organisation = :organisation')
            ->setParameter('organisation', $organisation->getId())
            ->andWhere('s.action = :action')
            ->setParameter('action', $action)
            ->andWhere('s.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
}