<?php

namespace App\Core\Traits;

use App\Entity\Organisation;
use App\Service\Organisation\OrganisationManager;

trait OrganisationTrait {

    public function __construct() {
        $this->setGuardAction('organisation', 'getOrganisation');
    }

    public function getOrganisation(array $data): Organisation
    {
        $manager = $this->container->get(OrganisationManager::class);
        return $manager->_get($data['idOrganisation']);
    }
}