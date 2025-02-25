<?php

namespace App\Repository;

use App\Entity\Client;
use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use Doctrine\Persistence\ManagerRegistry;

class ClientRepository extends AbstractCoreRepository
{
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function search(array $search = [], bool $countMode = false)
    {
        $settings = $this->configureSearch($search);
        $idOrganisation = $this->getIdOrganisation($search);
        $idsProject = $search['idsProject'] ?? [];

        if (isset($search['idProject']) && $search['idProject'] != '') {
            $idsProject[] = $search['idProject'];
        }

        $query = $this->createNewQueryBuilder()
            ->leftJoin("{$this->alias}.organisationClients", "rel")
            ->andWhere("rel.organisation = :idOrganisation")
            ->setParameter('idOrganisation', $idOrganisation);

        if (isset($search['search']) && $search['search'] != '') {
            $query = $query
                ->andWhere("{$this->alias}.firstName LIKE :search OR {$this->alias}.lastName LIKE :search OR {$this->alias}.email LIKE :search OR {$this->alias}.phone LIKE :search")
                ->setParameter('search', "%{$search['search']}%");
        }

        if (isset($search['ids']) && count($search['ids']) > 0) {
            $query = $query
                ->andWhere("{$this->alias}.uuid IN (:ids)")
                ->setParameter('ids', $search['ids']);
        }

        if (!$countMode) {
            $query = $query
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
