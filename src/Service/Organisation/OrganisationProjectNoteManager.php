<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\OrganisationProject;
use App\Entity\ProjectNote;
use App\Service\Note\NoteManager;
use App\Service\Project\ProjectNoteManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationProjectNoteManager extends AbstractCoreService
{
    use OrganisationTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'code' => 'Organisation.Project',
            'entity' => OrganisationProject::class,
            'elementManagerClass' => ProjectNoteManager::class,
            'guardActions' => [
                'organisation' => 'getOrganisation',
            ],
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

        // On récupère les notes par ids qui ne sont pas déjà liées au projet
        $noteManager = $this->container->get(NoteManager::class);
        $notes = $noteManager->_search([
            'organisation' => $data['organisation'],
            'ids' => $data['ids'],
        ]);

        $projectNoteManager = $this->getElementManager();
        $projectNoteManager->_add([
            'project' => $project,
            'notes' => $notes,
        ]);
    }

    public function _delete($id, array $data = [])
    {
        $data['ids'] = [$id];
        $this->_remove($data);
    }

    public function _remove(array $data)
    {
        $orgProject = $this->_get($data['organisationProject']);
        $project = $orgProject->getProject();

        $ids = $data['ids'] ?? [];
        if (isset($data['id'])) {
            $ids[] = $data['id'];
        }

        $projectNoteManager = $this->getElementManager();
        $projectNoteManager->_remove([
            'project' => $project,
            'ids' => $ids,
        ]);
    }
}