<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use App\Entity\ProjectClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectClient>
 */
class ProjectClientRepository extends AbstractCoreRepository
{
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectClient::class);
    }

    public function search(array $search = [], bool $countMode = false)
    {
        $settings = $this->configureSearch($search);
        $idOrganisation = $this->getIdOrganisation($search);
        $idsProject = $search['idsProject'] ?? [];
        $idsClient = $search['idsClient'] ?? [];
        $by = $search['by'] ?? 'project';

        $query = $this->createNewQueryBuilder()
            ->andWhere("rel.organisation = :idOrganisation")
            ->setParameter('idOrganisation', $idOrganisation);

        if ($by == 'project') {
            $idProject = $search['idProject'];
            $query = $query
                ->leftJoin("{$this->alias}.project", "p")
                ->leftJoin("p.organisationProjects", "rel")
                ->andWhere("p.uuid = :idProject")
                ->setParameter('idProject', $idProject);

            // if (!empty($idsClient)) {
            //     $query = $query
            //         ->leftJoin("rel.projectClients", "projectClient")
            //         ->leftJoin("projectClient.project", "project")
            //         ->andWhere("project.uuid IN (:idsProject)")
            //         ->setParameter('idsProject', $idsProject);
            // }
        }else{
            $idClient = $search['idClient'];
            $query = $query
                ->leftJoin("{$this->alias}.client", "c")
                ->leftJoin("c.organisationClients", "rel")
                ->andWhere("c.uuid = :idClient")
                ->setParameter('idClient', $idClient);
        
            // if (!empty($idsProject)) {
            //     $query = $query
            //         ->leftJoin("rel.projectClients", "projectClient")
            //         ->leftJoin("projectClient.project", "project")
            //         ->andWhere("project.uuid IN (:idsProject)")
            //         ->setParameter('idsProject', $idsProject);
            // }
        }

        if (isset($search['search']) && $search['search'] != '') {
            $query = $query
                ->andWhere("c.firstName LIKE :search OR c.lastName LIKE :search OR c.email LIKE :search OR c.phone LIKE :search")
                ->setParameter('search', "%{$search['search']}%");
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
