<?php

namespace App\Service\Note;

use App\Entity\Note;
use App\Core\Service\AbstractCoreService;
use App\Service\Project\ProjectManager;
use App\Service\Project\ProjectNoteManager;
use Symfony\Bundle\SecurityBundle\Security;

class NoteManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'uuid',
            'code' => 'Note',
            'entity' => Note::class,
        ]);
    }

    public function _create(array $data)
    {
        $element = new Note();
        $this->setData(
            $element,
            [
                'name' => [
                    'nullable' => false,
                ],
                'content' => [
                    'nullable' => true,
                ],
            ],
            $data
        );

        $this->em->persist($element);
        $this->isValid($element);

        if ($data['idProject'] || $data['idsProject']) {
            $ids = $data['idsProject'] ?? [$data['idProject']];
            $projectNoteManager = $this->container->get(ProjectNoteManager::class);
            $projectManager = $this->container->get(ProjectManager::class);
            
            $projectNoteManager->_add([
                'by' => ProjectNoteManager::BY_NOTE,
                'projects' => $projectManager->findByIds($ids), // uuids
                'note' => $element,
            ]);
        }

        return $element;
    }

    public function _update($id, array $data)
    {
        $element = $this->_get($id);

        $this->setData(
            $element,
            [
                'name' => [
                    'nullable' => false,
                ],
                'content' => [
                    'nullable' => true,
                ],
            ],
            $data
        );

        $this->em->persist($element);
        $this->isValid($element);

        if ($data['idProject'] || $data['idsProject']) {
            $ids = $data['idsProject'] ?? [$data['idProject']];
            $projectNoteManager = $this->container->get(ProjectNoteManager::class);
            $projectManager = $this->container->get(ProjectManager::class);
            
            $projectNoteManager->_add([
                'by' => ProjectNoteManager::BY_NOTE,
                'projects' => $projectManager->findByIds($ids), // uuids
                'note' => $element,
            ]);
        }

        $element->setUpdatedAt(new \DateTime());
        $this->em->persist($element);

        return $element;
    }
}