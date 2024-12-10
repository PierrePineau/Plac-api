<?php

namespace App\Core\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entity>
 *
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entity[]    findAll()
 * @method Entity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractCoreRelationnalRepository extends ServiceEntityRepository
{
    private $entityClass;
    public $alias;
    /**
     * @param class-string $entityClass
     */
    public function __construct(ManagerRegistry $registry, $entityClass, array $options = [])
    {
        parent::__construct($registry, $entityClass);
        $this->entityClass = parent::getEntityName();
        $this->alias = isset($options['alias']) ? $options['alias'] : 'e';

        if (isset($options['identifers']) ) {
            $this->relationnalIdentifier = $options['identifers'];
        }else{
            throw new \Exception("Identifiers need to be defined", 1);
        }
    }

    public function createNewQueryBuilder()
    {
        return $this->createQueryBuilder($this->alias);
    }

    // Sub function to create query by filters
    // private function byFilters(array $filters)
    // {
    //     $query = $this->createNewQueryBuilder();
    //     foreach ($filters as $key => $value) {
    //         if (is_array($value)) {
    //             $query = $query->andWhere("{$this->alias}.{$key} IN (:tab{$key})")
    //                 ->setParameter("tab{$key}", $value);
    //         }else{
    //             $query = $query->andWhere("{$this->alias}.{$key} = :{$key}")
    //                 ->setParameter($key, $value);
    //         }
    //     }
    //     return $query;
    // }

    // Sub function to create JSON object from array
    public function getObjectFromArray($array): string
    {
        // $object = 'JSON_OBJECT(';
        $object = '';
        $i = 0;
        $nbKeys = count($array);
        foreach($array as $key => $value){
            $object .= "JSON_QUOTE('".$key."'), ".$value;
            if($i < $nbKeys - 1){
                $object .= ', ';
            }
            $i++;
        }
        // $object .= ')';
        return $object;
    }

    // Sub function to create SELECT from array
    public function getSelectFromArray($array): string
    {
        $select = '';
        $i = 0;
        $nbKeys = count($array);
        foreach($array as $key => $value){
            $select .= $value . " AS ".$key;
            if($i < $nbKeys - 1){
                $select .= ', ';
            }
            $i++;
        }
        // $select .= ')';
        return $select;
    }

    public function decodeJson(?string $json = ''): ?array
    {
        if (!$json) {
            $json = '';
        }
        return json_decode(str_replace('\"', '', $json), true);
    }

    public function findByIds(array $ids): ?array
    {
        $query = $this->createNewQueryBuilder()
            ->andWhere("{$this->alias}.id IN (:ids)")
            ->setParameter('ids', $ids);
        return $query->getQuery()
            ->getResult();
    }

    public function search(array $search = [], bool $countMode = false)
    {
        $page = isset($search['page']) && $search['page'] > 0 ? $search['page'] : 1;
        $limit = isset($search['limit']) && $search['limit'] > 0 ? $search['limit'] : 10;
        $offset = ($page - 1) * $limit;
        // $order = (isset($search['order']) && $search['order'] == 'ASC') ? 'ASC' : 'DESC';

        // Ajouoter un element ocnfigurable pour le tri sur le abstract repository
        $query = $this->createNewQueryBuilder();

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
