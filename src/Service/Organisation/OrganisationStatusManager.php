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
            'security' => $security,
            'code' => 'Organisation.Status',
            'entity' => OrganisationStatus::class,
            'elementManagerClass' => StatusManager::class,
        ]);
    }

    public function generateDefault(array $data = [])
    {
        $statusManager = $this->getElementManager();

        $organisation = $data['organisation'];

        $organisationStatuses = $organisation->getOrganisationStatuses();
        
        if (count($organisationStatuses) > (count(StatusManager::DEFAULT_STATUS[StatusManager::TYPE_PROJECT]) + count(StatusManager::DEFAULT_STATUS[StatusManager::TYPE_TASK]))) {
            return $organisation;
        }
        
        // Les status par défaut pour chaque type
        $projectStatuses = $statusManager->generateDefault([
            'type' => StatusManager::TYPE_PROJECT,
        ]);

        // Les status par défaut pour chaque type
        $taskStatuses = $statusManager->generateDefault([
            'type' => StatusManager::TYPE_TASK,
        ]);

        $organisation = $data['organisation'];

        foreach ($projectStatuses as $projectStatus) {
            $orgElement = new OrganisationStatus();
            $orgElement->setStatus($projectStatus);
            $orgElement->setOrganisation($organisation);

            $this->em->persist($orgElement);
            $this->isValid($orgElement);
        }

        foreach ($taskStatuses as $taskStatus) {
            $orgElement = new OrganisationStatus();
            $orgElement->setStatus($taskStatus);
            $orgElement->setOrganisation($organisation);

            $this->em->persist($orgElement);
            $this->isValid($orgElement);
        }

        return $organisation;
    }

    // On récupère un status d'une organisation par action et type
    public function getOneStatus(array $options)
    {
        $manager = $this->getElementManager();
        return $manager->getOneStatus($options);
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
}