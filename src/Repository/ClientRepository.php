<?php

namespace App\Repository;

use App\Entity\Client;
use App\Core\Repository\AbstractCoreRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClientRepository extends AbstractCoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function search(array $search = [], bool $countMode = false)
    {
        $page = isset($search['page']) && $search['page'] > 0 ? $search['page'] : 1;
        $limit = isset($search['limit']) && $search['limit'] > 0 ? $search['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $idsOrganisation = isset($search['idsOrganisation']) ? $search['idsOrganisation'] : [];
        if (isset($search['idOrganisation'])) {
            $idsOrganisation[] = $search['idOrganisation'];
        }

        if (empty($idsOrganisation)) {
            throw new \Exception('idsOrganisation.required');
        }
        // $order = (isset($search['order']) && $search['order'] == 'ASC') ? 'ASC' : 'DESC';

        // Ajouoter un element ocnfigurable pour le tri sur le abstract repository
        $query = $this->createNewQueryBuilder()
            ->join("{$this->alias}.organisations", "o")
            ->andWhere("o.uuid IN (:idsOrganisation)")
            ->setParameter('idsOrganisation', $idsOrganisation);

        if (isset($search['search']) && $search['search'] != '') {
            $query = $query
                ->andWhere("{$this->alias}.firstName LIKE :search OR {$this->alias}.lastName LIKE :search OR {$this->alias}.email LIKE :search OR {$this->alias}.phone LIKE :search")
                ->setParameter('search', "%{$search['search']}%");
        }

        if (!$countMode) {
            $query = $query
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            return $query->getQuery()
                ->getResult();
        }else{
            $query = $query->select("COUNT({$this->alias}.id)");
            return $query->getQuery()
                ->getSingleScalarResult();
        }
    }
}
