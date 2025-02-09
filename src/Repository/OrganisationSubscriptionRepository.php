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
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationSubscription::class);
    }

    public function createAccessQueryBuilder(array $data)
    {
        $idOrganisation = $this->getIdOrganisation($data);
        return $this->createNewQueryBuilder()
            ->innerJoin("{$this->alias}.organisationSubscriptions", 'rel')
            ->andWhere('rel.organisation = :organisation')
            ->setParameter('organisation', $idOrganisation);
    }

    public function findByAccess($data): array
    {
        return $this->createAccessQueryBuilder($data)
            ->getQuery()
            ->getResult();
    }

    public function findOneByAccess($data): ?OrganisationSubscription
    {
        $id = $data['idSubscription'];
        return $this->createAccessQueryBuilder($data)
            ->setParameter('id', $id)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
