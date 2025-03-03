<?php
namespace App\EventSubscriber\Address;

use App\Event\Organisation\OrganisationCreateEvent;
use App\Event\Project\ProjectCreateEvent;
use App\Event\Project\ProjectUpdateEvent;
use App\Service\Address\AddressManager;
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
            $dataAddress = $data['address'] ?? null;
            if ($project && $dataAddress) {
                $AddressManager = $this->container->get(AddressManager::class);
                $idAddress = $dataAddress['id'] ?? null;
                $address = $AddressManager->_update($idAddress, $dataAddress);

                if ($address) {
                    $project->setAddress($address);
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