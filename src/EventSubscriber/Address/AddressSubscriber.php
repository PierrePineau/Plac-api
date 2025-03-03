<?php
namespace App\EventSubscriber\Address;

use App\Event\Organisation\OrganisationCreateEvent;
use App\Event\Project\ProjectCreateEvent;
use App\Event\Project\ProjectUpdateEvent;
use App\Service\Organisation\OrganisationStatusManager;
use App\Service\Status\StatusManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddressSubscriber implements EventSubscriberInterface
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
            ProjectUpdateEvent::class => [
                ['onProjectUpdate', 10],
            ],
        ];
    }

    public function onProjectUpdate(ProjectUpdateEvent $event): ProjectUpdateEvent
    {
        try {
            // On associe le status par défaut au projet
            $project = $event->getProject();
            $data = $event->getData();
            $idStatus = $data['idStatus'];
            // if ($project && $idStatus) {
            //     $statusManager = $this->container->get(StatusManager::class);
            //     $status = $statusManager->getOneStatusById([
            //         'id' => $idStatus,
            //         'type' => StatusManager::TYPE_PROJECT,
            //         'organisation' => $data['organisation'],
            //     ]);

            //     if ($status) {
            //         $project->setStatus($status);
            //     }
            // }
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