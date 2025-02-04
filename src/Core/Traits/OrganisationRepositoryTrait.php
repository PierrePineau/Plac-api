<?php

namespace App\Core\Traits;

use App\Entity\Organisation;

trait OrganisationRepositoryTrait {

    public function getIdOrganisation(array $data): mixed
    {
        if (isset($data['idOrganisation']) && $data['idOrganisation'] != null) {
            return $data['idOrganisation'];
        }elseif (isset($data['organisation']) && $data['organisation'] instanceof Organisation) {
            return $data['organisation']->getId();
        }else{
            throw new \Exception('organisation.required');
        }
    }
}