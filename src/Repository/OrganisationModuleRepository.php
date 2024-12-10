<?php

namespace App\Repository;

use App\Entity\OrganisationModule;
use App\Core\Repository\AbstractCoreRelationnalRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganisationModule>
 */
class OrganisationModuleRepository extends AbstractCoreRelationnalRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationModule::class, [
            'alias' => 'om',
            'identifiers' => ['organisation', 'module'],
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

        $organisationObj = $this->getObjectFromArray([
            'id' => 'o.id',
            'uuid' => 'o.uuid',
            'name' => 'o.name',
        ]);
        $moduleObj = $this->getObjectFromArray([
            'id' => 'm.id',
            'reference' => 'm.reference',
            'name' => 'm.name',
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
                    JSON_OBJECT({$organisationObj}) AS organisation,
                    JSON_OBJECT({$moduleObj}) AS module
                ")
                ->leftJoin("{$this->alias}.organisation", 'o')
                ->leftJoin("{$this->alias}.module", 'm')
                ->setMaxResults($limit)
                ->setFirstResult($offset);

            $results = $query->getQuery()
            ->getResult();

            return array_map(function($result) {
                $result['organisation'] = $this->decodeJson($result['organisation']);
                $result['module'] = $this->decodeJson($result['module']);
                return $result;
            }, $results);
        }else{
            $query = $query->select("COUNT({$this->alias}.id)");
            return $query->getQuery()
                ->getSingleScalarResult();
        }
    }
}
