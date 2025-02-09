<?php

namespace App\Service\Organisation;

use App\Entity\OrganisationStatus;
use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Service\Status\StatusManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationStatusManager extends AbstractCoreService
{
    use OrganisationTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Organisation.Status',
            'entity' => OrganisationStatus::class,
            'security' => $security,
            'elementManagerClass' => StatusManager::class,
        ]);
    }

    public function _search(array $filters = []): array
    {
        $manager = $this->getElementManager();
        return $manager->_search($filters);
    }

    public function _get($id, array $filters = []): mixed
    {
        return $this->_getOrganisationElement($id, $filters);
    }

    public function _update($id, array $data)
    {
        return $this->_updateOrganisationElement($id, $data);
    }

    public function _delete($id, array $filters = [])
    {
        return $this->_deleteOrganisationElement($id, $filters);
    }

    public function _create(array $data)
    {
        $organisation = $data['organisation'];

        $manager = $this->getElementManager();
        $element = $manager->_create($data);

        $orgElement = new OrganisationStatus();
        $orgElement->setStatus($element);
        $orgElement->setOrganisation($organisation);

        $this->em->persist($orgElement);
        $this->isValid($orgElement);

        return $element;
    }

    // public function setProjectStatus(array $data)
    // {
    //     try {
    //         $data = $this->guardMiddleware($data);
    //         $orgStatus = $this->_get($data['idStatus'], [
    //             'idOrganisation' => $data['organisation']->getId(),
    //         ]);

    //         return $this->messenger->newResponse(
    //             [
    //                 'success' => true,
    //                 'message' => $search['total'] > 0 ? $this->ELEMENT_FOUND : $this->ELEMENT_NOT_FOUND,
    //                 'code' => 200,
    //                 'data' => $search
    //             ]
    //         );
    //     } catch (\Throwable $th) {
    //         return $this->messenger->errorResponse($th);
    //     }
        
        
    //     $this->em->persist($orgProject);
    //     $this->isValid($orgProject);

    //     return $project;
    // }
}