<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\ClientTrait;
use App\Core\Traits\OrganisationTrait;
use App\Core\Traits\UserTrait;
use App\Entity\OrganisationClient;
use App\Service\Organisation\OrganisationManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationClientManager extends AbstractCoreService
{
    use OrganisationTrait;
    use ClientTrait;

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Organisation.Client',
            'entity' => OrganisationClient::class,
            'security' => $security,
        ]);
    }

    public function _get($id, array $filters = []): mixed
    {
        $client = $this->getClient($filters);

        // $element = $this->findOneBy([
        //     'user' => $this->getUser()->getId(),
        //     'organisation' => $organisation->getId(),
        // ]);
        
        return $client;
    }

    public function _create(array $data)
    {
        $organisation = $data['organisation'];

        $clientManager = $this->container->get(ClientManager::class);
        $client = $client->_create($data);

        $organisationClient = new OrganisationClient();
        $organisationClient->setClient($client);
        $organisationClient->setOrganisation($organisation);

        $this->em->persist($organisationClient);
        $this->isValid($organisationClient);

        return $organisationClient;
    }
}