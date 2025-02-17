<?php

namespace App\Core\Traits;

use App\Entity\User;
use App\Service\User\UserManager;

trait UserTrait {

    public function __construct() {
        $this->setGuardAction('user', 'getCustomer');
    }

    public function getCustomer(array $data): User
    {
        $userManager = $this->container->get(UserManager::class);
        $user = $userManager->_get($data['idUser']);

        $userManager->middleware([
            'user' => $user,
        ]);

        return $user;
    }
}