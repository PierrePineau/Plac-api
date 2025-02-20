<?php

namespace App\Core\Traits;

use App\Entity\Organisation;
use App\Service\Organisation\OrganisationManager;

trait OrganisationTrait {

    // public function __construct() {
    //     $this->setGuardAction('organisation', 'getOrganisation');
    // }

    public function getOrganisation(array $data): Organisation
    {
        $manager = $this->container->get(OrganisationManager::class);
        return $manager->_get($data['idOrganisation']);
    }

    public function _getOrganisationElement($id, array $filters = []): mixed
    {
        $orgElement = $this->findOneByAccess([
            'id' => $id,
            'organisation' => $filters['organisation'],
        ]);
        return $orgElement;
    }

    public function _updateOrganisationElement($id, array $data)
    {
        $orgElement = $this->_get($id, [
            'idOrganisation' => $data['organisation']->getId(),
        ]);

        $manager = $this->getElementManager();
        $element = $manager->_update($orgElement->getElement(), $data);

        $this->em->persist($orgElement);
        $this->isValid($orgElement);

        return $element;
    }

    public function _deleteOrganisationElement($id, array $filters = [])
    {
        $orgElement = $this->_get($id, [
            'idOrganisation' => $filters['organisation']->getId(),
        ]);

        $manager = $this->getElementManager();
        return $manager->_delete($orgElement->getElement());
    }
}