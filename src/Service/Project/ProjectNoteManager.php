<?php

namespace App\Service\Project;

use App\Core\Service\AbstractCoreService;
use App\Entity\ProjectNote;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectNoteManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Project.Note',
            'entity' => ProjectNote::class,
            'security' => $security,
        ]);
    }

    public function _add(array $data)
    {
        $project = $data['project'];
        $notes = $data['notes'];
        
        foreach ($notes as $note) {
            $projectNote = new ProjectNote();
            $projectNote->setProject($project);
            $projectNote->setNote($note);

            $this->em->persist($projectNote);
            $this->isValid($projectNote);
        }
    }

    public function _remove(array $data)
    {
        $project = $data['project'];
        $projectNotes = $this->findBy([
            'project' => $project->getId(),
            'note' => $data['ids'],
        ]);
        foreach ($data['projectNotes'] as $projectNotes) {
            $this->em->remove($projectNotes);
        }
    }
}