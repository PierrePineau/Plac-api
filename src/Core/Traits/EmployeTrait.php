<?php

namespace App\Core\Traits;

use App\Entity\Employe;
use App\Service\Employe\EmployeManager;

trait EmployeTrait {

    public function __construct() {
        $this->setGuardAction('employe', 'getEmploye');
    }

    public function getEmploye(array $data): Employe
    {
        $manager = $this->container->get(EmployeManager::class);
        return $manager->_get($data['idEmploye']);
    }
}