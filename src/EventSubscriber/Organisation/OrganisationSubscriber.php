<?php
namespace App\EventSubscriber\Organisation;

use App\Event\Client\UserCreateEvent;
use App\Event\Organisation\OrganisationGetEvent;
use App\Service\Organisation\OrganisationManager;
use App\Service\User\UserOrganisationManager;
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
            UserCreateEvent::class => [
                ['onUserCreate', 10],
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

    public function onUserCreate(UserCreateEvent $event): UserCreateEvent
    {
        try {
            // On créer une organisation pour l'utilisateur
            $user = $event->getUser();
            // On check si l'utilisateur a une organisation
            $organisation = $user->getUserOrganisations()->first();
            if (!$organisation) {
                $organisationManager = $this->container->get(UserOrganisationManager::class);
                $resp = $organisationManager->create([
                    'name' => $user->getFullName() . ' Organisation',
                ]);

                if ($resp['success']) {
                    $organisation = $resp['data'];
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