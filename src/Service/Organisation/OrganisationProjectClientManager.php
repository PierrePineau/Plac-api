<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\OrganisationClient;
use App\Entity\OrganisationProject;
use App\Service\Client\ClientManager;
use App\Service\Note\NoteManager;
use App\Service\Project\ProjectClientManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationProjectClientManager extends AbstractCoreService
{
    use OrganisationTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'code' => 'Organisation.Client',
            'entity' => OrganisationClient::class,
            'elementManagerClass' => ProjectClientManager::class,
            'guardActions' => [
                'organisation' => 'getOrganisation',
            ],
        ]);
    }

    public function _search(array $filters = []): array
    {
        $manager = $this->getElementManager();
        $filters['by'] = 'project';
        return $manager->_search($filters);
    }

    public function _create(array $data)
    {
        return $this->_add($data);
    }

    public function _add(array $data)
    {
        $organisationProjectManager = $this->container->get(OrganisationProjectManager::class);
        $orgProject = $organisationProjectManager->_get($data['idProject'], $data);
        $project = $orgProject->getProject();

        $ids = $data['ids'] ?? [];
        if (isset($data['id'])) {
            $ids[] = $data['id'];
        }

        // On récupère par ids qui ne sont pas déjà liées au projet
        $organisationClientManager = $this->container->get(OrganisationClientManager::class);
        $clients = $organisationClientManager->_search([
            'organisation' => $data['organisation'],
            'ids' => $data['ids'],
            'limit' => 100,
        ]);

        $elementManager = $this->getElementManager();
        $elementManager->_add([
            'by' => 'project',
            'project' => $project,
            'clients' => $clients,
        ]);

        $this->em->flush();
    }

    public function _delete($id, array $data = [])
    {
        $data['ids'] = [$id];
        $this->_remove($data);
    }

    public function _remove(array $data)
    {
        $organisationProjectManager = $this->container->get(OrganisationProjectManager::class);
        $orgProject = $organisationProjectManager->_get($data['idProject'], $data);
        // $project = $orgProject->getProject();

        // Les ids correspondent aux ids des relations à supprimer
        $ids = $data['ids'] ?? [];
        if (isset($data['id'])) {
            $ids[] = $data['id'];
        }

        $organisationClientManager = $this->container->get(OrganisationClientManager::class);
        $clients = $organisationClientManager->_search([
            'organisation' => $data['organisation'],
            'ids' => $data['ids'],
            'limit' => 100,
        ]);

        $elementManager = $this->getElementManager();
        $elementManager->_remove([
            'by' => 'project',
            'ids' => $orgProject->getProject(),
            'clients' => $clients,
        ]);
    }
}