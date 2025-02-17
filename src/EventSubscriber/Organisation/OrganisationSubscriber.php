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
            OrganisationGetEvent::class => [
                ['onOrganisationGetEvent', 10],
                ['middleware', 9]
            ],
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
                $organisation = $organisationManager->findOneByAccess($data);

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

    public function middleware(OrganisationGetEvent $event): OrganisationGetEvent
    {
        // On vérifie si l'utilisateur connecté à accès à l'organisation
        $organisationManager = $this->container->get(OrganisationManager::class);
        $organisationManager->middleware([
            'organisation' => $event->getOrganisation()
        ]);
        return $event;
    }
}