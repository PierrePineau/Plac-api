<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<File>
 */
class FileRepository extends AbstractCoreRepository
{
    private $accessRelation;
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
        $this->accessRelation = 'organisationFiles';
    }

    public function search(array $search = [], bool $countMode = false)
    {
        $settings = $this->configureSearch($search);
        $idOrganisation = $this->getIdOrganisation($search);
        $idProject = $search['idProject'] ?? null;
        $idsProjects = $search['idsProjects'] ?? [];
        $types = $search['types'] ?? [];
        if (isset($search['type']) && $search['type'] != '') {
            $types[] = $search['type'];
        }
        if ($idProject) {
            $idsProjects[] = $idProject;
        }

        $query = $this->createNewQueryBuilder()
            ->leftJoin("{$this->alias}.{$this->accessRelation}", "rel")
            ->andWhere("rel.organisation = :idOrganisation")
            ->setParameter('idOrganisation', $idOrganisation);

        if (isset($search['search']) && $search['search'] != '') {
            $query = $query
                ->andWhere("{$this->alias}.name LIKE :search OR {$this->alias}.ext LIKE :search")
                ->setParameter('search', "%{$search['search']}%");
        }

        if (!empty($idsProjects)) {
            $query = $query
                ->leftJoin("{$this->alias}.projectFiles", "pf")
                ->leftJoin("pf.project", "p")
                ->andWhere("p.uuid IN (:idsProjects)")
                ->setParameter('idsProjects', $idsProjects);
        }

        if (!empty($types)) {
            $query = $query
                ->andWhere("{$this->alias}.type IN (:types)")
                ->setParameter('types', $types);
        }

        if (!$countMode) {
            $query = $query
                ->addOrderBy("{$this->alias}.createdAt", "DESC")
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
