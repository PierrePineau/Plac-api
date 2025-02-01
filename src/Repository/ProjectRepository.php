<?php

namespace App\Repository;

use App\Entity\Project;
use App\Core\Repository\AbstractCoreRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends AbstractCoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findOneByAccess($data): ?Project
    {
        $organisation = $data['organisation'];
        $id = $data['idProject'];
        return $this->createQueryBuilder('p')
            ->innerJoin('p.organisationProjects', 'po')
            ->andWhere('p.id = :id')
            ->andWhere('po.organisation = :organisation')
            ->setParameter('organisation', $organisation->getId())
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
