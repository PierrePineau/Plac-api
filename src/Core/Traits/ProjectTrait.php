<?php

namespace App\Core\Traits;

use App\Entity\Project;
use App\Event\Project\ProjectGetEvent as newEvent;

trait ProjectTrait {

    public function __construct() {
        parent::setGuardAction('project', 'getProject');
    }

    public function getProject(array $data): Project
    {
        $event = new newEvent($data);
        if (isset($data['project']) && $data['project'] instanceof Project) {
            $event->setProject($data['project']);
        }
        parent::dispatchEvent($event);

        if ($event->hasError()) {
            throw new \Exception($event->getErrors());
        }

        return $event->getProject();
    }
}