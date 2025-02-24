<?php

namespace App\Repository;

use App\Entity\Project;
use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends AbstractCoreRepository
{
    private $accessRelation;
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
        $this->accessRelation = 'organisationProjects';
    }

    public function search(array $search = [], bool $countMode = false)
    {
        $settings = $this->configureSearch($search);
        $idOrganisation = $this->getIdOrganisation($search);

        $query = $this->createNewQueryBuilder();

        $query = $this->createNewQueryBuilder()
            ->leftJoin("{$this->alias}.organisationProjects", "rel")
            ->andWhere("rel.organisation = :idOrganisation")
            ->setParameter('idOrganisation', $idOrganisation);

        if (isset($search['search']) && $search['search'] != '') {
            // $query = $query
            //     ->andWhere("{$this->alias}.firstName LIKE :search OR {$this->alias}.lastName LIKE :search OR {$this->alias}.email LIKE :search OR {$this->alias}.phone LIKE :search")
            //     ->setParameter('search', "%{$search['search']}%");
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
