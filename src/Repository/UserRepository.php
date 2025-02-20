<?php

namespace App\Repository;

use App\Entity\User;
use App\Core\Repository\AbstractCoreRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends AbstractCoreRepository implements PasswordUpgraderInterface
{
    private const SCOPES = [
        'google' => 'google_id',
    ];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class, [
            'alias' => 'u',
        ]);
    }

    public function loadUserByIdentifierAndPayload(string $identifier, array $payload = []): ?User
    {
        if (isset($payload['oauth']) && isset(self::SCOPES[$payload['oauth']])) {
            // On le connecte via Google
            $scopeKey = self::SCOPES[$payload['oauth']];
            $identifier = $payload[$scopeKey];
            $query = $this->createQueryBuilder('u')
                ->andWhere('u.'.$scopeKey.' = :scopeIdentifer')
                ->setParameter('scopeIdentifer', $identifier);
        }else {
            $query = $this->createQueryBuilder('u')
                ->andWhere('u.email = :identifier AND u.deleted = 0 AND u.email IS NOT NULL');
        }
        return $query
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    private function searchAdmin(array $search = [], bool $countMode = false)
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

    public function search(array $search = [], bool $countMode = false)
    {
        if (isset($search['isSuperAdmin']) && $search['isSuperAdmin']) {
            return $this->searchAdmin($search, $countMode);
        }else{
            return $this->searchByOrganisation($search, $countMode);
        }
    }

    public function searchByOrganisation(array $search = [], bool $countMode = false)
    {
        $settings = $this->configureSearch($search);
        $idOrganisation = $this->getIdOrganisation($search);

        $query = $this->createNewQueryBuilder()
            ->leftJoin("{$this->alias}.userOrganisations", "rel")
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
