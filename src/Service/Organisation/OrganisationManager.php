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
        $user = $this->getUser();
        $organisation = $data['organisation'];

        $grantData = [
            'user' => $user,
            'organisation' => $organisation,
        ];

        // Ou si l'utilisateur connecté est un admin
        if ($user->isAuthenticate() && !$user->isSuperAdmin()) {
            $grantData['userOrganisation'] = $this->em->getRepository(UserOrganisation::class)->findOneBy([
                'user' => $user->getId(),
                'organisation' => $organisation->getId(),
            ]);
        }
        if (!$this->security->isGranted(OrganisationMiddleware::ACCESS, $data)) {
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

    public function _update($id, array $data)
    {
        $organisation = $this->_get($id);

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