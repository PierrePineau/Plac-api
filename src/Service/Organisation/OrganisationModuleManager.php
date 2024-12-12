<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\OrganisationModule;
use App\Service\Module\ModuleManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationModuleManager extends AbstractCoreService
{
    use OrganisationTrait;

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'code' => 'Organisation.Module',
            'entity' => OrganisationModule::class,
            'security' => $security,
        ]);
    }

    // Pour gérer un project il faut que soit défini une organisation
    // Le middleware permet de vérifier si l'organisation est bien défini et si l'utilisateur a les droits
    public function guardMiddleware(array $data): array
    {
        $organisation = $this->getOrganisation($data);

        $data['organisation'] = $organisation;

        return $data;
    }

    public function _search(array $filters = []): array
    {
        return parent::_search($filters);
    }

    public function _add(array $data)
    {
        $moduleManager = $this->container->get(ModuleManager::class);
        $organisation = $data['organisation'];

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

    public function _remove(array $data)
    {
        $elements = $this->findByIds($data['ids']);
        foreach ($elements as $element) {
            $this->em->remove($element);
        }
    }
}