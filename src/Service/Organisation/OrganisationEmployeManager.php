<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\EmployeOrganisation;
use App\Service\Employe\EmployeManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationEmployeManager extends AbstractCoreService
{
    use OrganisationTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Organisation.Employe',
            'entity' => EmployeOrganisation::class,
            'security' => $security,
            'elementManagerClass' => EmployeManager::class,
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

        $employeOrg = new EmployeOrganisation();
        $employeOrg->setEmploye($element);
        $employeOrg->setOrganisation($organisation);

        $this->em->persist($employeOrg);
        $this->isValid($employeOrg);

        return $element;
    }
}