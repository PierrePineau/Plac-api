<?php

namespace App\Service\Organisation;

use App\Core\Exception\DeniedException;
use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\UserOrganisation;
use App\Security\Middleware\UserMiddleware;
use App\Service\User\UserManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationUserManager extends AbstractCoreService
{
    use OrganisationTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'uuid',
            'code' => 'Organisation.User',
            'entity' => UserOrganisation::class,
            'elementManagerClass' => UserManager::class,
            'guardActions' => [
                'organisation' => 'getOrganisation',
            ],
        ]);
    }

    public function middleware(array $data): mixed
    {
        $authenticateUser = $this->getUser();
        $organisation = $data['organisation'];
        $member = $this->_getOrganisationElement($data['idMember'], [
            'organisation' => $data['organisation'],
        ]);
        $grantData = [
            'user' => $member,
            'authenticateUser' => $authenticateUser,
            'organisation' => $organisation,
            'userOrganisation' => $data['userOrganisation'] ?? null,
        ];

        // Ou si l'utilisateur connecté est un admin
        if ($authenticateUser->isAuthenticate() && !$authenticateUser->isSuperAdmin()) {
            if (!empty($data['userOrganisation'])) {
                $grantData['userOrganisation'] = $this->em->getRepository(UserOrganisation::class)->findOneBy([
                    'user' => $member->getId(),
                    'organisation' => $organisation->getId(),
                ]);
                $data['userOrganisation'] = $grantData['userOrganisation'];
            }
        }
        if (!$this->security->isGranted(UserMiddleware::ACCESS, $grantData)) {
            throw new DeniedException();
        }
        return $data;
    }

    public function _create(array $data)
    {
        // Permet de créer un employé pour une organisation
        $organisation = $data['organisation'];

        $manager = $this->getElementManager();
        $employe = $manager->_createUser($data);
        $employe->setRoles([UserManager::ROLE_EMPLOYE]);

        $orgElement = new UserOrganisation();
        $orgElement->setUser($employe);
        $orgElement->setOrganisation($organisation);

        $this->em->persist($orgElement);
        $this->isValid($orgElement);

        return $employe;
    }

    public function _search(array $filters = []): array
    {
        $manager = $this->getElementManager();
        return $manager->_search($filters);
    }

    public function _get($id, array $filters = []): mixed
    {
        return $this->_getOrganisationElement($id, $filters);
    }

    public function _update($id, array $data)
    {
        return $this->_updateOrganisationElement($id, $data);
    }

    public function _delete($id, array $filters = [])
    {
        return $this->_deleteOrganisationElement($id, $filters);
    }
}