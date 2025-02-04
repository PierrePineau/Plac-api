<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\ClientTrait;
use App\Core\Traits\OrganisationTrait;
use App\Entity\Client;
use App\Entity\OrganisationClient;
use App\Service\Client\ClientManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationClientManager extends AbstractCoreService
{
    use OrganisationTrait;

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Organisation.Client',
            'entity' => Client::class,
            'security' => $security,
        ]);
    }

    public function _get($id, array $filters = []): mixed
    {
        $element = $this->findOneByAccess([
            'id' => $id,
            'organisation' => $filters['organisation'],
        ]);
        return $element;
    }

    public function _create(array $data)
    {
        $organisation = $data['organisation'];

        $clientManager = $this->container->get(ClientManager::class);
        $client = $clientManager->_create($data);

        $organisationClient = new OrganisationClient();
        $organisationClient->setClient($client);
        $organisationClient->setOrganisation($organisation);

        $this->em->persist($organisationClient);
        $this->isValid($organisationClient);

        return $organisationClient;
    }

    public function _update($id, array $data)
    {
        $organisationClient = $this->_get($id, [
            'idOrganisation' => $data['organisation']->getId(),
        ]);

        $clientManager = $this->container->get(ClientManager::class);
        $client = $clientManager->_update($organisationClient->getClient()->getId(), $data);

        $organisationClient->setClient($client);

        $this->em->persist($organisationClient);
        $this->isValid($organisationClient);

        return $organisationClient;
    }

    public function _delete($id, array $filters = [])
    {
        $client = $this->_get($id, [
            'idOrganisation' => $filters['organisation']->getId(),
        ]);

        $clientManager = $this->container->get(ClientManager::class);
        $clientManager->_delete($client);

        return $client;
    }
}