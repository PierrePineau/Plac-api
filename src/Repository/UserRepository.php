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
                ->andWhere('u.email = :identifier OR u.uuid = :identifier');
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
}
