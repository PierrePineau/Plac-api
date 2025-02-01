<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Entity\Organisation;
use App\Entity\UserOrganisation;
use App\Security\Middleware\OrganisationMiddleware;
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
        // $user = $data['user'];
        $user = $this->getUser();
        $organisation = $data['organisation'];
        // On vérifie si l'utilisateur connecté est le même que celui que l'on veut modifier
        // Ou si l'utilisateur connecté est un admin
        if (!$this->security->isGranted(OrganisationMiddleware::ACCESS, [
            'user' => $user,
            'organisation' => $organisation,
            'userOrganisation' => $this->em->getRepository(UserOrganisation::class)->findOneBy([
                'user' => $user->getId(),
                'organisation' => $organisation->getId(),
            ]),
        ])) {
            $this->deniedException();
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