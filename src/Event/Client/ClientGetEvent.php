<?php

namespace App\Event\Client;

use App\Core\Event\AbstractCoreEvent;
use App\Entity\Client;
use App\Event\AbstractDemoEvent;

final class ClientGetEvent extends AbstractDemoEvent
{
    private ?Client $client = null;
    private ?Organisation $organisation = null;
    
    public function __construct(array $data = [])
    {   
        parent::__construct($data);
        $this->organisation = $data['organisation'];
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }
}