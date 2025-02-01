<?php
namespace App\EventSubscriber\Project;

use App\Event\Project\ProjectGetEvent;
use App\Service\Project\ProjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectSubscriber implements EventSubscriberInterface
{
    private $container;
    public function __construct($container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            ProjectGetEvent::class => [
                ['onProjectGetEvent', 10],
            ],
        ];
    }

    public function onProjectGetEvent(ProjectGetEvent $event): ProjectGetEvent
    {
        // On récupère project
        try {
            $project = $event->getProject();
            if (!$project) {
                $projectManager = $this->container->get(ProjectManager::class);
                $data = $event->getData();
                if (!isset($data['idProject'])) {
                    throw new \Exception($projectManager::ELEMENT.'.id.required');
                }
                $project = $projectManager->findOneByAccess($data);

                $event->setProject($project);
            }

            return $event;

        } catch (\Throwable $th) {
            //throw $th;
            $event->setError($th->getMessage());
            $event->stopPropagation();

            return $event;
        }
        
        return $event;
    }
}