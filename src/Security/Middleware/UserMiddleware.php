<?php

namespace App\Security\Middleware;

use App\Entity\Admin;
use App\Entity\User;
use App\Entity\UserOrganisation;
use App\Model\AuthenticateUser;
use App\Service\User\UserOrganisationManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserMiddleware extends Voter
{
    // these strings are just invented: you can use anything
    const ACCESS = 'user_access';
    const UPDATE = 'user_update';
    const DELETE = 'user_delete';

    public const VOTERS = [
        self::ACCESS,
        self::UPDATE,
        self::DELETE,
    ];

    public function __construct(
        private $container,
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, self::VOTERS)) {
            return false;
        }

        $user = $subject['user'] ?? null;
        if (!$user instanceof User) {
            return false;
        }

        $authenticateUser = $subject['authenticateUser'] ?? null;
        if (!$authenticateUser instanceof AuthenticateUser || !$authenticateUser->isAuthenticate()) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $authenticateUser = $subject['authenticateUser'] ?? $token->getUser();
        if ($authenticateUser->isSuperAdmin()) {
            return true;
        }

        // you know $subject is a User object, thanks to `supports()`
        /** @var User $user */
        $user = $subject['user'];

        return match($attribute) {
            self::ACCESS => $this->canAccess($user, $authenticateUser, $subject),
            // self::UPDATE => $this->canUpdate($user, $authenticateUser, $subject),
            // self::DELETE => $this->canDelete($user, $authenticateUser, $subject),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canAccess(User $user, AuthenticateUser $authenticateUser, array $data = []): bool
    {
        if ($authenticateUser->getId() === $user->getId()) {
            return true;
        }elseif ($authenticateUser->getId() !== $user->getId() && $authenticateUser->isAdmin()) {
            // Si l'utilisateur connecté est un admin, exemple le gérant de l'organisation. il peut accéder à tous CES utilisateurs (dans son organisation)
            // On check de vérif s'il est dans la même organisation
            if (!isset($data['userOrganisation']) || !$data['userOrganisation'] instanceof UserOrganisation) {
                return false;
            }
        }else {
            return false;
        }
    }

    // private function canUpdate(User $user, AuthenticateUser $authenticateUser): bool
    // {
    //     // Spécification de la logique métier pour update ?  
    //     return $authenticateUser->getId() === $user->getId();
    // }

    // private function canDelete(User $user, AuthenticateUser $authenticateUser): bool
    // {
    //     // Spécification de la logique métier pour delete ?  
    //     return $authenticateUser->getId() === $user->getId();
    // }
}