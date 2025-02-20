<?php

namespace App\Service\User;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\UserTrait;
use App\Entity\Organisation;
use App\Entity\UserOrganisation;
use App\Service\Organisation\OrganisationManager;
use ErrorException;
use Symfony\Bundle\SecurityBundle\Security;

class UserOrganisationManager extends AbstractCoreService
{
    use UserTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'uuid',
            'code' => 'User.Organisation',
            'entity' => UserOrganisation::class,
        ]);
    }

    public function getOrganisationsByUser(array $data)
    {
        // On récupère les organisation non deleted
        $userOrganisations = $this->repo->getUserOrganisationsByUser([
            'idUser' => $data['idUser'],
        ]);

        $org = [];
        foreach ($userOrganisations as $userOrganisation) {
            $org[] = $userOrganisation->getOrganisation();
        }

        return $org;
    }

    public function getOneOrganisationsByUser(array $data): ?Organisation
    {
        // On récupère les organisation non deleted
        $userOrganisation = $this->repo->getOneUserOrganisationsByUser([
            'idUser' => $data['idUser'],
        ]);

        return $userOrganisation ? $userOrganisation->getOrganisation() : null;
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

        // Un utilisateur ne peut avoir qu'une seule organisation
        if ($userOrganisation) {
            throw new ErrorException($this->ELEMENT_ALREADY_EXISTS);
            // throw new \Exception($this->ELEMENT_ALREADY_EXISTS, 400);
        }

        $organisationManager = $this->container->get(OrganisationManager::class);
        $organisation = $organisationManager->_create($data);

        $userOrganisation = new UserOrganisation();
        $userOrganisation->setUser($user);
        $userOrganisation->setOrganisation($organisation);

        $organisation->setOwner($user);

        $this->em->persist($userOrganisation);
        $this->isValid($userOrganisation);

        return $organisation;
    }
}