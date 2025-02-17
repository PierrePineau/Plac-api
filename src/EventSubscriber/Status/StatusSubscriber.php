<?php
namespace App\EventSubscriber\Status;

use App\Event\Organisation\OrganisationCreateEvent;
use App\Service\Organisation\OrganisationProjectManager;
use App\Service\Organisation\OrganisationStatusManager;
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
                ]);
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