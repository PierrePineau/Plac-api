<?php

namespace App\Event\Organisation;

use App\Core\Event\AbstractCoreEvent;
use App\Entity\Organisation;

final class OrganisationGetEvent extends AbstractCoreEvent
{
    private ?Organisation $organisation;
    
    public function __construct(array $data = [])
    {   
        parent::__construct($data);
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