<?php

namespace App\Service\Client;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\ClientTrait;
use App\Core\Traits\OrganisationTrait;
use App\Entity\Client;
use Symfony\Bundle\SecurityBundle\Security;

class ClientManager extends AbstractCoreService
{
    use OrganisationTrait;
    // use ClientTrait;

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Client',
            'entity' => Client::class,
            'security' => $security,
        ]);
    }

    public function _create(array $data)
    {
        $client = new Client();

        $this->setData(
            $client,
            [
                'firstname' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'lastname' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'email' => [
                    'required' => false,
                    'nullable' => true,
                ],
            ],
            $data
        );

        $this->em->persist($client);
        $this->isValid($client);

        return $client;
    }

    public function _update($id, array $data)
    {
        $client = $this->getClient([
            'idClient' => $id,
            'organisation' => $data['organisation'],
        ]);

        $this->setData(
            $client,
            [
                'firstname' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'lastname' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'email' => [
                    'required' => false,
                    'nullable' => true,
                ],
                'archived' => [
                    'required' => false,
                    'type' => 'boolean',
                ],
            ],
            $data
        );

        $this->em->persist($client);
        $this->isValid($client);

        return $client;
    }

    public function _delete($id, array $data = []) 
    {
        $client = $this->getClient([
            'idClient' => $id,
            'organisation' => $data['organisation'],
        ]);

        $client->setDeleted(true);

        // TODO : Supprimer les relations avec le client ? ou alors archiver aussi ces projets ?

        $this->em->persist($client);
        $this->isValid($client);

        return $client;
    }
}