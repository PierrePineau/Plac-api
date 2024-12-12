<?php

namespace App\Core\Traits;

use App\Entity\Client;
use App\Event\User\UserGetEvent as newEvent;

trait ClientTrait {
    public function getClient(array $data): User
    {
        if (isset($data['client']) && $data['client'] instanceof Client) {
            return $data['client'];
        }
        if (!isset($data['idClient'])) {
            throw new \Exception('client.id.required', 400);
        }
        $event = new newEvent($data);
        parent::dispatchEvent($event);

        if ($event->hasError()) {
            throw new \Exception($event->getErrors());
        }

        return $event->getClient();
    }
}