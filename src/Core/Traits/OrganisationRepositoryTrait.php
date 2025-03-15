<?php

namespace App\Core\Traits;

use App\Entity\Organisation;
use Doctrine\ORM\Mapping\Entity;

trait OrganisationRepositoryTrait {

    public function getIdOrganisation(array $data): mixed
    {
        if (isset($data['organisation']) && $data['organisation'] instanceof Organisation) {
            return $data['organisation']->getId();
        } else {
            throw new \Exception('organisation.required');
        }
        // if (isset($data['idOrganisation']) && $data['idOrganisation'] != null) {
        //     return $data['idOrganisation'];
        // }elseif (isset($data['organisation']) && $data['organisation'] instanceof Organisation) {
        //     return $data['organisation']->getId();
        // }else{
        //     throw new \Exception('organisation.required');
        // }
    }

    public function createAccessQueryBuilder(array $data)
    {
        if (!$this->accessRelation) {
            throw new \Exception("Access relation need to be defined", 1);
        }
        $idOrganisation = $this->getIdOrganisation($data);
        $relation = $this->alias . '.' . $this->accessRelation;
        return $this->createNewQueryBuilder()
            ->innerJoin("{$relation}", 'rel')
            ->andWhere('rel.organisation = :organisation')
            ->setParameter('organisation', $idOrganisation);
    }

    public function findByAccess($data): array
    {
        return $this->createAccessQueryBuilder($data)
            ->getQuery()
            ->getResult();
    }

    public function findOneByAccess($data)
    {
        return $this->createAccessQueryBuilder($data)
            ->andWhere("{$this->alias}.uuid = :id")
            ->setParameter('id', $data['id'])
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}