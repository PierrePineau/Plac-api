<?php

namespace App\Service\User;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\UserOrganisation;
use App\Service\Organisation\OrganisationManager;
use Symfony\Bundle\SecurityBundle\Security;

class UserOrganisationManager extends AbstractCoreService
{
    use OrganisationTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'User.Organisation',
            'entity' => UserOrganisation::class,
            'security' => $security,
        ]);
    }

    public function _get($id, array $filters = []): mixed
    {
        $user = $this->getCustomer([
            'idUser' => $filters['idUser'],
        ]);
        $organisation = $this->getOrganisation([
            'idOrganisation' => $id,
        ]);

        return $organisation;
    }

    public function _create(array $data)
    {
        $user = $this->getCustomer([
            'idUser' => $data['idUser'],
        ]);

        // On vérifie que l'utilisateur n'a pas déjà une organisation
        $userOrganisation = $this->findOneBy([
            'user' => $user->getId(),
        ]);

        if ($userOrganisation) {
            $this->errorException($this->ELEMENT_ALREADY_EXISTS);
            // throw new \Exception($this->ELEMENT_ALREADY_EXISTS, 400);
        }

        $organisationManager = $this->container->get(OrganisationManager::class);
        $organisation = $organisationManager->_create($data);

        $userOrganisation = new UserOrganisation();
        $userOrganisation->setUser($user);
        $userOrganisation->setOrganisation($organisation);

        $this->em->persist($userOrganisation);
        $this->isValid($userOrganisation);

        return $userOrganisation;
    }
}