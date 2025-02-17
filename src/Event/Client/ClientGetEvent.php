<?php

namespace App\Event\Client;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Core\Event\AbstractCoreEvent;

final class ClientGetEvent extends AbstractCoreEvent
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

    public function setOrganisation(Organisation $organisation): void
    {
        $this->organisation = $organisation;
    }
}