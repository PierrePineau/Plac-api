<?php

namespace App\Repository;

use App\Entity\UserOrganisation;
use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use Doctrine\Persistence\ManagerRegistry;

class UserOrganisationRepository extends AbstractCoreRepository
{
    private $accessRelation;
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserOrganisation::class);
        $this->accessRelation = 'userOrganisations';
    }

    public function getUserOrganisationsByUser($data)
    {
        return $this->createNewQueryBuilder($data)
            ->leftJoin('e.organisation', 'o')
            ->andWhere('o.deleted = :deleted')
            ->andWhere('e.user = :user')
            ->setParameter('deleted', false)
            ->setParameter('user', $data['idUser'])
            ->getQuery()
            ->getResult();
    }

    public function getOneUserOrganisationsByUser($data)
    {
        return $this->createNewQueryBuilder($data)
            ->leftJoin('e.organisation', 'o')
            ->andWhere('o.deleted = :deleted')
            ->andWhere('e.user = :user')
            ->setParameter('deleted', false)
            ->setParameter('user', $data['idUser'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


}
