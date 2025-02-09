<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\OrganisationProject;
use App\Service\Project\ProjectManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationProjectManager extends AbstractCoreService
{
    use OrganisationTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Organisation.Project',
            'entity' => OrganisationProject::class,
            'security' => $security,
            'elementManagerClass' => ProjectManager::class,
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

        $orgElement = new OrganisationProject();
        $orgElement->setProject($element);
        $orgElement->setOrganisation($organisation);

        $this->em->persist($orgElement);
        $this->isValid($orgElement);

        return $element;
    }
}