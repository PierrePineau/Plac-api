<?php
namespace App\EventSubscriber\Organisation;

use App\Event\Organisation\OrganisationGetEvent;
use App\Service\Organisation\OrganisationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrganisationSubscriber implements EventSubscriberInterface
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
            OrganisationGetEvent::class => ['onOrganisationGetEvent', 10],
        ];
    }

    public function onOrganisationGetEvent(OrganisationGetEvent $event): OrganisationGetEvent
    {
        // On récupère l'organisation
        try {
            $organisation = $event->getOrganisation();
            if (!$organisation) {
                $organisationManager = $this->container->get(OrganisationManager::class);
                $data = $event->getData();
                if (!isset($data['idOrganisation'])) {
                    throw new \Exception($organisationManager::ELEMENT.'.id.required');
                }
                $organisation = $organisationManager->find($data['idOrganisation']);

                $event->setOrganisation($organisation);
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