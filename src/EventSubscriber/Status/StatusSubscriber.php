<?php
namespace App\EventSubscriber\Status;

use App\Event\Organisation\OrganisationCreateEvent;
use App\Event\Project\ProjectCreateEvent;
use App\Service\Organisation\OrganisationProjectManager;
use App\Service\Organisation\OrganisationStatusManager;
use App\Service\Status\StatusManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusSubscriber implements EventSubscriberInterface
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
                ['onOrganisationCreate', 10],
            ],
            ProjectCreateEvent::class => [
                ['onProjectCreate', 10],
            ],
        ];
    }

    public function onOrganisationCreate(OrganisationCreateEvent $event): OrganisationCreateEvent
    {
        // On crée les status par défaut
        try {
            $organisation = $event->getOrganisation();
            if ($organisation) {
                $orgStatusManager = $this->container->get(OrganisationStatusManager::class);

                $status = $orgStatusManager->generateDefault([
                    'organisation' => $organisation,
                    'flush' => true,
                ]);

                $event->addSubscriber('StatusSubscriber', 'OK');
            }

            return $event;

        } catch (\Throwable $th) {
            //throw $th;
            $event->setError($th->getMessage());
            $event->addSubscriber('StatusSubscriber', 'K O');
            $event->stopPropagation();

            return $event;
        }
        
        return $event;
    }

    public function onProjectCreate(ProjectCreateEvent $event): ProjectCreateEvent
    {
        try {
            // On associe le status par défaut au projet
            $project = $event->getProject();
            $data = $event->getData();
            if ($project) {
                $statusManager = $this->container->get(StatusManager::class);
                $status = $statusManager->getOneStatus([
                    'organisation' => $data['organisation'],
                    'type' => StatusManager::TYPE_PROJECT,
                    'action' => StatusManager::ACTION_DEFAULT,
                ]);

                if ($status) {
                    $project->setStatus($status);
                }
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