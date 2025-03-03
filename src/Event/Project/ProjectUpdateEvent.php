<?php

namespace App\Event\Project;

use App\Core\Event\AbstractCoreEvent;
use App\Entity\Project;

final class ProjectUpdateEvent extends AbstractCoreEvent
{
    private ?Project $project = null;
    
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->project = $data['project'] ?? null;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }
}