<?php

namespace App\Core\Traits;

use App\Entity\Organisation;
use App\Service\Organisation\OrganisationManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $manager = $this->getElementManager();
        $element = $manager->findOneByAccess([
            'id' => $id,
            'organisation' => $filters['organisation'],
        ]);
        if (!$element) {
            throw new NotFoundHttpException($this->ELEMENT_NOT_FOUND);
        }
        return $element;
    }

    public function _updateOrganisationElement($id, array $data)
    {
        $element = $this->_get($id, [
            'organisation' => $data['organisation'],
        ]);

        $manager = $this->getElementManager();
        $element = $manager->_update($element, $data);

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }

    public function _deleteOrganisationElement($id, array $filters = [])
    {
        $element = $this->_get($id, [
            'organisation' => $filters['organisation'],
        ]);

        $manager = $this->getElementManager();
        return $manager->_delete($element);
    }
}