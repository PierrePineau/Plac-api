<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Entity\Organisation;
use App\Entity\UserOrganisation;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Organisation',
            'entity' => Organisation::class,
            'security' => $security,
        ]);
    }

    public function middleware(array $data): mixed
    {
        $organisation = $data['organisation'];
        $user = $this->getUser();

        $userOrganisation = $this->em->getRepository(UserOrganisation::class)->findOneBy([
            'user' => $user->getId(),
            'organisation' => $organisation->getId(),
        ]);
        // On vérifie si l'utilisateur connecté à accès à l'organisation
        if (!$userOrganisation && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new \Exception($this->ELEMENT.'.not_allowed', 423);
        }
        return $data;
    }

    public function _create(array $data)
    {
        $organisation = new Organisation();

        $this->setData(
            $organisation,
            [
                'name' => [
                    'required' => true,
                    'nullable' => false,
                ]
            ],
            $data
        );

        $this->em->persist($organisation);
        $this->isValid($organisation);

        return $organisation;
    }
}