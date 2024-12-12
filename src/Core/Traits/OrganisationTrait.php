<?php

namespace App\Core\Traits;

use App\Entity\Organisation;
use App\Event\Organisation\OrganisationGetEvent as newEvent;

trait OrganisationTrait {
    public function getOrganisation(array $data): Organisation
    {
        if (!isset($data['idOrganisation'])) {
            throw new \Exception('organisation.id.required');
        }

        $event = new newEvent($data);
        parent::dispatchEvent($event);

        if ($event->hasError()) {
            throw new \Exception($event->getErrors());
        }

        return $event->getOrganisation();
    }
}