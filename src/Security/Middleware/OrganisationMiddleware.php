<?php

namespace App\Security\Middleware;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrganisationMiddleware extends Voter
{
    // these strings are just invented: you can use anything
    const ACCESS = 'organisation_access';
    const UPDATE = 'organisation_update';
    const DELETE = 'organisation_delete';

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

        $organisation = $subject['organisation'] ?? null;

        if (!$organisation instanceof Organisation) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $userConnected = $token->getUser();

        if ($this->accessDecisionManager->decide($token, ['ROLE_SUPER_ADMIN'])) {
            return true;
        }

        if (!$userConnected instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a User object, thanks to `supports()`
        /** @var User $user */
        $user = $subject['user'];

        return match($attribute) {
            // self::ACCESS => $this->canAccess($user, $userConnected),
            // self::UPDATE => $this->canUpdate($user, $userConnected),
            // self::DELETE => $this->canDelete($user, $userConnected),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    // private function canAccess(User $user, User $userConnected): bool
    // {
    //     return $userConnected === $user;
    // }

    // private function canUpdate(User $user, User $userConnected): bool
    // {
    //     // Spécification de la logique métier pour update ?  
    //     return $userConnected === $user;
    // }

    // private function canDelete(User $user, User $userConnected): bool
    // {
    //     // Spécification de la logique métier pour delete ?  
    //     return $userConnected === $user;
    // }
}