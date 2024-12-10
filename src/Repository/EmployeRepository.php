<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Entity\Employe;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Employe>
 */
class EmployeRepository extends AbstractCoreRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Employe) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
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
