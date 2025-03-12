<?php
namespace App\EventSubscriber\Project;

use App\Entity\Organisation;
use App\Event\Organisation\OrganisationCreateEvent;
use App\Event\Project\ProjectCreateEvent;
use App\Event\Project\ProjectGetEvent;
use App\Service\Organisation\OrganisationManager;
use App\Service\Organisation\OrganisationProjectManager;
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
            OrganisationCreateEvent::class => [
                ['onOrganisationCreate', 1],
            ],
            ProjectGetEvent::class => [
                ['onProjectGetEvent', 10],
            ],
        ];
    }

    public function onOrganisationCreate(OrganisationCreateEvent $event): OrganisationCreateEvent
    {
        // On récupère organisation qui est en train d'être créée
        // On crée un premier projet
        try {
            $organisation = $event->getOrganisation();
            if ($organisation) {
                $orgProjectManager = $this->container->get(OrganisationProjectManager::class);

                $project = $orgProjectManager->generateDefault([
                    'organisation' => $organisation,
                    'flush' => true,
                ]);

                $event->addSubscriber('ProjectSubscriber', 'OK');
            }
            return $event;
        } catch (\Throwable $th) {
            //throw $th;
            $event->setError($th->getMessage());
            $event->addSubscriber('ProjectSubscriber', 'K O');
            $event->stopPropagation();

            return $event;
        }
        
        return $event;
    }

    public function onProjectGetEvent(ProjectGetEvent $event): ProjectGetEvent
    {
        // // On récupère project
        // try {
        //     $project = $event->getProject();
        //     if (!$project) {
        //         $projectManager = $this->container->get(ProjectManager::class);
        //         $data = $event->getData();
        //         if (!isset($data['idProject'])) {
        //             throw new \Exception($projectManager::ELEMENT.'.id.required');
        //         }
        //         $project = $projectManager->findOneByAccess($data);

        //         $event->setProject($project);
        //     }

        //     return $event;

        // } catch (\Throwable $th) {
        //     //throw $th;
        //     $event->setError($th->getMessage());
        //     $event->stopPropagation();

        //     return $event;
        // }
        
        return $event;
    }
}