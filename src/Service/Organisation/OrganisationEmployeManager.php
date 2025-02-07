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
        ]);
    }

    public function _search(array $filters = []): array
    {
        $manager = $this->container->get(EmployeManager::class);
        return $manager->_search($filters);
    }

    public function _get($id, array $filters = []): mixed
    {
        $element = $this->findOneByAccess([
            'id' => $id,
            'organisation' => $filters['organisation'],
        ]);
        return $element;
    }

    public function _create(array $data)
    {
        $organisation = $data['organisation'];

        $manager = $this->container->get(EmployeManager::class);
        $employe = $manager->_create($data);

        $employeOrg = new EmployeOrganisation();
        $employeOrg->setEmploye($employe);
        $employeOrg->setOrganisation($organisation);

        $this->em->persist($employeOrg);
        $this->isValid($employeOrg);

        return $employe;
    }

    public function _update($id, array $data)
    {
        $employeOrg = $this->_get($id, [
            'idOrganisation' => $data['organisation']->getId(),
        ]);

        $manager = $this->container->get(EmployeManager::class);
        $employe = $manager->_update($employeOrg->getEmploye(), $data);

        $this->em->persist($employeOrg);
        $this->isValid($employeOrg);

        return $employe;
    }

    public function _delete($id, array $filters = [])
    {
        $employeOrg = $this->_get($id, [
            'idOrganisation' => $filters['organisation']->getId(),
        ]);

        $manager = $this->container->get(EmployeManager::class);
        return $manager->_delete($employeOrg->getEmploye());
    }
}