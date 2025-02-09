<?php

namespace App\Service\Project;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\ProjectNote;
use App\Service\Note\NoteManager;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectNoteManager extends AbstractCoreService
{
    use OrganisationTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Project.Note',
            'entity' => ProjectNote::class,
            'security' => $security,
        ]);
    }

    public function _get($id, array $filters = []): mixed
    {
        $element = $this->findOneByAccess([
            'id' => $id,
            'organisation' => $filters['organisation'],
        ]);
        return $element;
    }

    public function _search(array $filters = []): array
    {
        return parent::_search($filters);
    }

    public function _add(array $data)
    {
        $project = $data['project'];
        $noteManager = $this->container->get(NoteManager::class);
        $note = $noteManager->_create($data);

        $projectNote = new ProjectNote();
        $projectNote->setNote($note);
        $projectNote->setProject($project);

        $this->em->persist($projectNote);
        $this->isValid($projectNote);

        return $projectNote;
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
            $element->setNote(null);
            $element->setOrganisation(null);
            $this->em->remove($element);
        }
    }
}