<?php

namespace App\Core\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
abstract class AbstractCoreRepository extends ServiceEntityRepository
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

    // public function findOneById($id)
    // {
    //     return $this->createNewQueryBuilder()
    //         ->andWhere("{$this->alias}.id = :id")
    //         ->setParameter('id', $id)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }

    // public function findOneByFilters(array $filters)
    // {
    //     $query = $this->byFilters( $filters);
    //     return $query->getQuery()
    //         ->setMaxResults(1)
    //         ->getOneOrNullResult();
    // }

    // public function findByFilters(array $filters): ?array
    // {
    //     $query = $this->byFilters($filters);
    //     return $query->getQuery()
    //         ->getResult();
    // }
    public function configureSearch(array $search = [])
    {
        $page = isset($search['page']) && $search['page'] > 0 ? $search['page'] : 1;
        $limit = isset($search['limit']) && $search['limit'] > 0 ? $search['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $order = (isset($search['order']) && $search['order'] == 'ASC') ? 'ASC' : 'DESC';
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset,
            'order' => $order,
        ];
    }

    public function search(array $search = [], bool $countMode = false)
    {
        $settings = $this->configureSearch($search);
        // Ajouoter un element ocnfigurable pour le tri sur le abstract repository
        $query = $this->createNewQueryBuilder();

        // if (isset($search['search']) && $search['search'] != '') {
            
        // }

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
