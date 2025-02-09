<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use App\Entity\EmployeOrganisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeOrganisation>
 */
class EmployeOrganisationRepository extends AbstractCoreRepository
{
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeOrganisation::class);
    }

    public function createAccessQueryBuilder(array $data)
    {
        $idOrganisation = $this->getIdOrganisation($data);
        return $this->createNewQueryBuilder()
            ->innerJoin("{$this->alias}.employeOrganisations", 'rel')
            ->andWhere('rel.organisation = :organisation')
            ->setParameter('organisation', $idOrganisation);
    }

    public function findByAccess($data): array
    {
        return $this->createAccessQueryBuilder($data)
            ->getQuery()
            ->getResult();
    }

    public function findOneByAccess($data): ?EmployeOrganisation
    {
        $id = $data['idEmploye'];
        return $this->createAccessQueryBuilder($data)
            ->setParameter('id', $id)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
