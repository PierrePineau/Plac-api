<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
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
            'code' => 'Organisation.Project',
            'entity' => OrganisationProject::class,
            'security' => $security,
            'elementManagerClass' => ProjectClientManager::class,
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

    public function _create(array $data)
    {
        return $this->_add($data);
    }

    public function _add(array $data)
    {
        // On ajoute une ou plusieurs notes à un projet
        $orgProject = $this->_get($data['idProject']);
        $project = $orgProject->getProject();

        $ids = $data['ids'] ?? [];
        if (isset($data['id'])) {
            $ids[] = $data['id'];
        }

        // On récupère par ids qui ne sont pas déjà liées au projet
        $ClientManager = $this->container->get(ClientManager::class);
        $clients = $ClientManager->_search([
            'organisation' => $data['organisation'],
            'ids' => $data['ids'],
            'excludeIdsProject' => [$project->getId()],
        ]);

        $elementManager = $this->getElementManager();
        $elementManager->_add([
            'project' => $project,
            'clients' => $clients,
        ]);
    }

    public function _delete($id, array $data = [])
    {
        $data['ids'] = [$id];
        $this->_remove($data);
    }

    public function _remove(array $data)
    {
        // On va chercher le projet par l'organisation
        $orgProject = $this->_get($data['idProject']);
        // $project = $orgProject->getProject();

        // Les ids correspondent aux ids des relations à supprimer
        $ids = $data['ids'] ?? [];
        if (isset($data['id'])) {
            $ids[] = $data['id'];
        }

        $projectNoteManager = $this->getElementManager();
        $projectNoteManager->_remove([
            // 'project' => $project,
            'ids' => $ids,
        ]);
    }
}