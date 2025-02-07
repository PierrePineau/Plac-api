<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use App\Entity\OrganisationClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganisationClient>
 */
class OrganisationClientRepository extends AbstractCoreRepository
{
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationClient::class);
    }

    public function createAccessQueryBuilder(array $data)
    {
        $idOrganisation = $this->getIdOrganisation($data);
        return $this->createNewQueryBuilder()
            ->innerJoin("{$this->alias}.organisationClients", 'rel')
            ->andWhere('rel.organisation = :organisation')
            ->setParameter('organisation', $idOrganisation);
    }

    public function findByAccess($data): array
    {
        return $this->createAccessQueryBuilder($data)
            ->getQuery()
            ->getResult();
    }

    public function findOneByAccess($data): ?OrganisationClient
    {
        $id = $data['idClient'];
        return $this->createAccessQueryBuilder($data)
            ->setParameter('id', $id)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
