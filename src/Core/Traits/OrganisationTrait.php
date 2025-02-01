<?php

namespace App\Core\Traits;

use App\Entity\Organisation;
use App\Event\Organisation\OrganisationGetEvent as newEvent;

trait OrganisationTrait {

    public function __construct() {
        parent::setGuardAction('organisation', 'getOrganisation');
    }

    public function getOrganisation(array $data): Organisation
    {
        $event = new newEvent($data);
        if (isset($data['organisation']) && $data['organisation'] instanceof Organisation) {
            $event->setOrganisation($data['organisation']);
        }
        parent::dispatchEvent($event);

        if ($event->hasError()) {
            throw new \Exception($event->getErrors(true));
        }

        return $event->getOrganisation();
    }
}