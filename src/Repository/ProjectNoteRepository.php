<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRelationnalRepository;
use App\Core\Repository\AbstractCoreRepository;
use App\Entity\ProjectNote;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectNote>
 */
class ProjectNoteRepository extends AbstractCoreRelationnalRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectNote::class, [
            'alias' => 'pn',
            'identifiers' => ['project', 'note'],
        ]);
    }

    public function search(array $search = [], bool $countMode = false)
    {
        $page = isset($search['page']) && $search['page'] > 0 ? $search['page'] : 1;
        $limit = isset($search['limit']) && $search['limit'] > 0 ? $search['limit'] : 10;
        $offset = ($page - 1) * $limit;
        // $order = (isset($search['order']) && $search['order'] == 'ASC') ? 'ASC' : 'DESC';

        // Ajouoter un element ocnfigurable pour le tri sur le abstract repository
        $query = $this->createNewQueryBuilder();

        $projectObj = $this->getObjectFromArray([
            'id' => 'p.id',
            'uuid' => 'p.uuid',
            'name' => 'p.name',
            'updatedAt' => 'p.updatedAt',
        ]);
        $noteObj = $this->getObjectFromArray([
            'id' => 'n.id',
            'uuid' => 'n.uuid',
            'name' => 'n.name',
            'updatedAt' => 'n.updatedAt',
        ]);
        $query = $this->createQueryBuilder($this->alias);
        // On vérifie qu'au moins l'un des identifiers est présent
        if (isset($this->relationnalIdentifier)) {
            $i = 0;
            foreach ($this->relationnalIdentifier as $identifier) {
                $keyIdentifier = ucfirst($identifier);
                if (isset($search['id'.$keyIdentifier]))
                {
                    $query = $query->andWhere("{$this->alias}.{$identifier} = :{$identifier}")
                            ->setParameter($identifier, $search[$identifier]);
                    $i++;
                }elseif (isset($search['ids'.$keyIdentifier])) {
                    $query = $query->andWhere("{$this->alias}.{$identifier} IN (:tab{$identifier})")
                    ->setParameter("tab{$identifier}", $search[$identifier]);
                }
            }
            if ($i == 0) {
                throw new \Exception("At least one identifier is required", 1);
            }
        }else{
            throw new \Exception("Identifiers need to be defined", 1);
        }

        // if (isset($search['search']) && $search['search'] != '') {
            
        // }

        if (!$countMode) {
            $query = $query
                ->select("
                    {$this->alias}.id,
                    JSON_OBJECT({$projectObj}) AS project,
                    JSON_OBJECT({$noteObj}) AS note
                ")
                ->leftJoin("{$this->alias}.project", 'p')
                ->leftJoin("{$this->alias}.note", 'n')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $results = $query->getQuery()
            ->getResult();

            return array_map(function($result) {
                $result['project'] = $this->decodeJson($result['project']);
                $result['note'] = $this->decodeJson($result['note']);
                return $result;
            }, $results);
        }else{
            $query = $query->select("COUNT({$this->alias}.id)");
            return $query->getQuery()
                ->getSingleScalarResult();
        }
    }
}
