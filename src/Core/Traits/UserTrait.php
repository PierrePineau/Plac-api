<?php

namespace App\Core\Traits;

use App\Entity\User;
use App\Event\User\UserGetEvent as newEvent;

trait UserTrait {
    public function getCustomer(array $data): User
    {
        if (!isset($data['idUser'])) {
            throw new \Exception('user.id.required');
        }
        $event = new newEvent($data);
        parent::dispatchEvent($event);

        if ($event->hasError()) {
            throw new \Exception($event->getErrors());
        }

        return $event->getUser();
    }
}