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
            'entity' => OrganisationClient::class,
            'security' => $security,
        ]);
    }

    public function _search(array $filters = []): array
    {
        $manager = $this->container->get(ClientManager::class);
        return $manager->_search($filters);
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

        $orgClient = new OrganisationClient();
        $orgClient->setClient($client);
        $orgClient->setOrganisation($organisation);

        $this->em->persist($orgClient);
        $this->isValid($orgClient);

        return $client;
    }

    public function _update($id, array $data)
    {
        $orgClient = $this->_get($id, [
            'idOrganisation' => $data['organisation']->getId(),
        ]);

        $clientManager = $this->container->get(ClientManager::class);
        $client = $clientManager->_update($orgClient->getClient(), $data);

        $this->em->persist($orgClient);
        $this->isValid($orgClient);

        return $client;
    }

    public function _delete($id, array $filters = [])
    {
        $orgClient = $this->_get($id, [
            'idOrganisation' => $filters['organisation']->getId(),
        ]);

        $clientManager = $this->container->get(ClientManager::class);
        return $clientManager->_delete($orgClient->getClient());
    }
}