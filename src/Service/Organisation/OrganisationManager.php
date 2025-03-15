<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Entity\Organisation;
use App\Entity\UserOrganisation;
use App\Security\Middleware\OrganisationMiddleware;
use App\Core\Exception\DeniedException;
use App\Event\Organisation\OrganisationCreateEvent;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'uuid',
            'code' => 'Organisation',
            'entity' => Organisation::class,
        ]);
    }

    public function middleware(array $data): mixed
    {
        $authenticateUser = $this->getUser();
        $organisation = $data['organisation'];
        $grantData = [
            'authenticateUser' => $authenticateUser,
            'organisation' => $organisation,
        ];

        // Ou si l'utilisateur connecté est un admin
        if ($authenticateUser->isAuthenticate() && !$authenticateUser->isSuperAdmin()) {
            $grantData['userOrganisation'] = $this->em->getRepository(UserOrganisation::class)->findOneBy([
                'user' => $authenticateUser->getId(),
                'organisation' => $organisation->getId(),
            ]);
            $data['userOrganisation'] = $grantData['userOrganisation'];
        }
        if (!$this->security->isGranted(OrganisationMiddleware::ACCESS, $grantData)) {
            throw new DeniedException();
        }
        return $data;
    }

    public function _get($id, array $filters = []): mixed
    {
        $element = parent::_get($id, $filters);
        $this->middleware([
            'organisation' => $element,
        ]);
        return $element;
    }

    public function _create(array $data)
    {
        $organisation = new Organisation();

        $now = new \DateTime('now');
        // On ajoute 30 jours à la date actuelle
        $organisation->setFreeTrialEndAt($now->modify('+30 days'));

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

        // Envoie email activation du compte par exemple
        $newEvent = new OrganisationCreateEvent([
            'organisation' => $organisation,
            'authenticateUser' => $this->getUser(),
        ]);
        // Send Event
        $this->dispatchEvent($newEvent);

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