<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use App\Entity\Note;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends AbstractCoreRepository
{
    private $accessRelation;
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
        $this->accessRelation = 'organisationNotes';
    }

    public function search(array $search = [], bool $countMode = false)
    {
        $settings = $this->configureSearch($search);
        $idOrganisation = $this->getIdOrganisation($search);

        $query = $this->createNewQueryBuilder()
            ->leftJoin("{$this->alias}.organisationNotes", "rel")
            ->andWhere("rel.organisation = :idOrganisation")
            ->setParameter('idOrganisation', $idOrganisation);

        if (isset($search['search']) && $search['search'] != '') {
            // $query = $query
            //     ->andWhere("{$this->alias}.firstName LIKE :search OR {$this->alias}.lastName LIKE :search OR {$this->alias}.email LIKE :search OR {$this->alias}.phone LIKE :search")
            //     ->setParameter('search', "%{$search['search']}%");
        }

        if (isset($search['ids']) && count($search['ids']) > 0) {
            $query = $query
                ->andWhere("{$this->alias}.id IN (:ids)")
                ->setParameter('ids', $search['ids']);
        }

        if (!empty($idsProjects)) {
            $query = $query
                ->leftJoin("{$this->alias}.projectNotes", "pn")
                ->leftJoin("pn.project", "p")
                ->andWhere("p.uuid IN (:idsProjects)")
                ->setParameter('idsProjects', $idsProjects);
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
