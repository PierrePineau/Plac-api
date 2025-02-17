<?php

namespace App\Event\Project;

use App\Core\Event\AbstractCoreEvent;
use App\Entity\Organisation;
use App\Entity\Project;

final class ProjectGetEvent extends AbstractCoreEvent
{
    private ?Project $project = null;
    private ?Organisation $organisation = null;
    
    public function __construct(array $data = [])
    {   
        parent::__construct($data);
        $this->organisation = $data['organisation'];
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): void
    {
        $this->organisation = $organisation;
    }
}