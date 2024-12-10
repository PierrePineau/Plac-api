<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Entity\Organisation;
use App\Entity\OrganisationModule;
use App\Service\Module\ModuleManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationModuleManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Organisation.Module',
            'entity' => Organisation::class,
            'security' => $security,
        ]);
    }

    public function _search(array $filters = []): array
    {
        if (!isset($filters['idOrganisation'])) {
            throw new \Exception($this->ELEMENT.'.organisation.required');
        }

        return parent::_search($filters);
    }

    public function _add(array $data)
    {
        if (!isset($filters['idOrganisation'])) {
            throw new \Exception($this->ELEMENT.'.organisation.required');
        }

        $organisationManager = $this->container->get(OrganisationManager::class);
        $moduleManager = $this->container->get(ModuleManager::class);
        $organisation = $organisationManager->find($filters['idOrganisation']);
        if (!$organisation) {
            throw new \Exception($organisationManager->ELEMENT_NOT_FOUND);
        }

        $organisationModules = $this->findBy([
            'organisation' => $organisation->getId(),
        ]);

        // On retire les anciens modules
        foreach ($organisationModules as $organisationModule) {
            $this->em->remove($organisationModule);
        }
        
        $newModules = $moduleManager->findBy([
            'reference' => $data['modules'],
        ]);

        foreach ($newModules as $module) {
            $newOrganisationModule = new OrganisationModule();
            $newOrganisationModule->setOrganisation($organisation);
            $newOrganisationModule->setModule($module);
            $newOrganisationModule->setEnable(true);
            $this->em->persist($newOrganisationModule);
        }
    }

    public function _delete($id, array $data = [])
    {
        $this->_remove([
            'ids' => [$id],
        ]);
    }

    public function _remove(array $data)
    {
        $elements = $this->findByIds($data['ids']);
        foreach ($elements as $element) {
            $this->em->remove($element);
        }
    }
}