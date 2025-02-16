<?php

namespace App\Security\Middleware;

use App\Entity\Admin;
use App\Entity\User;
use App\Model\AuthenticateUser;
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

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $userConnected = $subject['userConnected'] ?? $token->getUser();

        if ($this->accessDecisionManager->decide($token, ['ROLE_SUPER_ADMIN'])) {
            return true;
        }

        if (!$userConnected instanceof AuthenticateUser || !$userConnected->isAuthenticate()) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($userConnected->isSuperAdmin()) {
            return true;
        }

        // you know $subject is a User object, thanks to `supports()`
        /** @var User $user */
        $user = $subject['user'];

        return match($attribute) {
            self::ACCESS => $this->canAccess($user, $userConnected),
            self::UPDATE => $this->canUpdate($user, $userConnected),
            self::DELETE => $this->canDelete($user, $userConnected),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canAccess(User $user, AuthenticateUser $userConnected): bool
    {
        return $userConnected->getId() === $user->getId();
    }

    private function canUpdate(User $user, AuthenticateUser $userConnected): bool
    {
        // Spécification de la logique métier pour update ?  
        return $userConnected->getId() === $user->getId();
    }

    private function canDelete(User $user, AuthenticateUser $userConnected): bool
    {
        // Spécification de la logique métier pour delete ?  
        return $userConnected->getId() === $user->getId();
    }
}