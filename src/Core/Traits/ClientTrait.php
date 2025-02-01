<?php

namespace App\Core\Traits;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Event\Client\ClientGetEvent as newEvent;

trait ClientTrait {

    public function __construct() {
        parent::setGuardAction('client', 'getClient');
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