<?php

namespace App\Service\Project;

use App\Core\Service\AbstractCoreService;
use App\Entity\ProjectNote;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectNoteManager extends AbstractCoreService
{
    public const BY_NOTE = 'note';
    public const BY_PROJECT = 'project';

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
        if ($data['by'] === self::BY_NOTE) {
            $projects = $data['projects'];
            $note = $data['note'];

            $ids = array_map(function($project) {
                return $project->getId();
            }, $projects);

            // On récupère ceux qui existente déjà
            $projectNotes = $this->findBy([
                'project' => $ids,
                'note' => $note->getId(),
            ]);
            
            foreach ($projects as $project) {
                // Vérifier si la relation existe déjà
                $existingProjectNote = array_filter($projectNotes, function($projectNote) use ($project, $note) {
                    return $projectNote->getProject()->getId() === $project->getId() && $projectNote->getNote()->getId() === $note->getId();
                });

                if (empty($existingProjectNote)) {
                    $projectNote = new ProjectNote();
                    $projectNote->setProject($project);
                    $projectNote->setNote($note);

                    $this->em->persist($projectNote);
                    $this->isValid($projectNote);
                }
            }
        } else {
            $project = $data['project'];
            $notes = $data['notes'];

            $ids = array_map(function($project) {
                return $project->getId();
            }, $notes);

            // On récupère ceux qui existente déjà
            $projectNotes = $this->findBy([
                'note' => $ids,
                'project' => $project->getId(),
            ]);
            
            foreach ($notes as $note) {
                // Vérifier si la relation existe déjà
                $existingProjectNote = array_filter($projectNotes, function($projectNote) use ($project, $note) {
                    return $projectNote->getProject()->getId() === $project->getId() && $projectNote->getNote()->getId() === $note->getId();
                });
                
                if (empty($existingProjectNote)) {
                    $projectNote = new ProjectNote();
                    $projectNote->setProject($project);
                    $projectNote->setNote($note);

                    $this->em->persist($projectNote);
                    $this->isValid($projectNote);
                }
            }
        }
    }

    public function _remove(array $data)
    {
        if ($data['by'] === self::BY_NOTE) {
            $project = $data['project'];
            $projectNotes = $this->findBy([
                'project' => $project->getId(),
                'note' => $data['ids'],
            ]);
        } else {
            $note = $data['note'];
            $projectNotes = $this->findBy([
                'note' => $note->getId(),
                'project' => $data['ids'],
            ]);
        }
        
        foreach ($projectNotes as $projectNotes) {
            $this->em->remove($projectNotes);
        }
    }
}