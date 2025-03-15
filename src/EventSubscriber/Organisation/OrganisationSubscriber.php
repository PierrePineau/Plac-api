<?php
namespace App\EventSubscriber\Organisation;

use App\Event\User\UserCreateEvent;
use App\Event\Organisation\OrganisationCreateEvent;
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
            OrganisationCreateEvent::class => [
                ['onOrganisationCreate', 15],
            ],
            UserCreateEvent::class => [
                ['onUserCreate', 5],
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

    public function onOrganisationCreate(OrganisationCreateEvent $event): OrganisationCreateEvent
    {
        try {
            // Logic ?
            $event->addSubscriber('OrganisationSubscriber', 'OK');
            return $event;
        } catch (\Throwable $th) {
            //throw $th;
            $event->setError($th->getMessage());
            $event->stopPropagation();
            return $event;
        }
        return $event;
    }

    public function onUserCreate(UserCreateEvent $event): UserCreateEvent
    {
        try {
            // On créer une organisation pour l'utilisateur
            $user = $event->getUser();
            // On check si l'utilisateur a une organisation
            // $organisation = 
            // getOneOrganisationsByUser
            // $organisation = $user->getUserOrganisations()->first();
            if ($user) {
                // >Créer ou récupérer l'organisation de l'utilisateur
                $organisation = $this->container->get(UserOrganisationManager::class)->getOneOrganisationsByUser([
                    'idUser' => $user->getId(),
                    'user' => $user,
                    'name' => $user->getFullName() . ' Organisation',
                    'createIfNotExist' => true,
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