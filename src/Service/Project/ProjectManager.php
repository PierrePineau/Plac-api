<?php

namespace App\Service\Project;

use App\Core\Service\AbstractCoreService;
use App\Entity\Project;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Project',
            'entity' => Project::class,
        ]);
    }
}