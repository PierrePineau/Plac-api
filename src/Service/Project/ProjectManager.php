<?php

namespace App\Service\Project;

use App\Core\Service\AbstractCoreService;
use App\Entity\Project;

class ProjectManager extends AbstractCoreService
{
    private $passwordHash;
    public function __construct($container, $entityManager)
    {
        $this->passwordHash = $passwordHash;
        parent::__construct($container, $entityManager, [
            'code' => 'Project',
            'entity' => Project::class,
        ]);
    }
}