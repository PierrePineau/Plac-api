<?php

namespace App\Security\Middleware;

use App\Entity\Organisation;
use App\Entity\User;
use App\Entity\UserOrganisation;
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
        $user = $subject['user'] ?? null;

        if (!$organisation instanceof Organisation) {
            return false;
        }

        if (!$user instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_SUPER_ADMIN'])) {
            return true;
        }

        $user = $subject['user'];
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        $organisation = $subject['organisation'];
        if (!$organisation instanceof Organisation) {
            // the organisation must exist; if not, deny access
            return false;
        }

        $userOrganisation = $subject['userOrganisation'];
        if (!$userOrganisation instanceof UserOrganisation) {
            // the userOrganisation must exist; if not, deny access
            return false;
        }
        // you know $subject is a User object, thanks to `supports()`
        /** @var User $user */
        $user = $subject['user'];

        return match($attribute) {
            self::ACCESS => $this->canAccess([
                'userOrganisation' => $userOrganisation,
            ]),
            // self::UPDATE => $this->canUpdate($data),
            // self::DELETE => $this->canDelete($data),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canAccess(array $data): bool
    {
        $userOrganisation = $data['userOrganisation'];
        // Si userOrganisation n'existe pas, l'utilisateur ne peut pas accéder à l'organisation
        if (!$userOrganisation instanceof UserOrganisation) {
            return false;
        }
        return true;
    }
}