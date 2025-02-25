<?php

namespace App\Service\Client;

use App\Entity\Client;
use App\Core\Service\AbstractCoreService;
use Symfony\Bundle\SecurityBundle\Security;

class ClientManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'uuid',
            'code' => 'Client',
            'entity' => Client::class,
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
        $client = $this->_get($id);

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
                'phone' => [
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
}