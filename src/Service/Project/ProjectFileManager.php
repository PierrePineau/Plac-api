<?php

namespace App\Service\Project;

use App\Core\Service\AbstractCoreService;
use App\Entity\ProjectFile;
use App\Service\File\FileManager;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectFileManager extends AbstractCoreService
{
    public const BY_FILE = 'file';
    public const BY_PROJECT = 'project';
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Project.File',
            'entity' => ProjectFile::class,
            'security' => $security,
        ]);
    }

    public function _search(array $filters = []): array
    {
        $fileManager = $this->container->get(FileManager::class);
        return $fileManager->_search($filters);
    }

    public function _add(array $data)
    {
        $by = $data['by'] ?? self::BY_FILE; // par default on ajoute les fichiers aux projets

        if ($by === self::BY_PROJECT) {
            $project = $data['project'];
            $projectFiles = $this->findBy([
                'project' => $project->getId(),
            ]);
            $existingFiles = [];
            foreach ($projectFiles as $projectFile) {
                $existingFiles[] = $projectFile->getFile()->getId();
            }

            $files = $data['files'];
            
            foreach ($files as $file) {
                if (!in_array($file->getId(), $existingFiles)) {
                    $projectFile = new ProjectFile();
                    $projectFile->setProject($project);
                    $projectFile->setFile($file);
                    $this->em->persist($projectFile);
                }
            }
        }elseif ($by === self::BY_FILE) {
            $file = $data['file'];
            $projectFiles = $this->findBy([
                'file' => $file->getId(),
            ]);
            $existingProjects = [];
            foreach ($projectFiles as $projectFile) {
                $existingProjects[] = $projectFile->getProject()->getId();
            }

            $projects = $data['projects'];
            
            foreach ($projects as $project) {
                if (!in_array($project->getId(), $existingProjects)) {
                    $projectFile = new ProjectFile();
                    $projectFile->setProject($project);
                    $projectFile->setFile($file);
                    $this->em->persist($projectFile);
                }
            }
        }
        
    }

    public function _remove(array $data)
    {
        $projectFiles = $this->findBy([
            'id' => $$data['ids'],
        ]);
        foreach ($data['projectFiles'] as $projectFiles) {
            $this->em->remove($projectFiles);
        }
    }
}