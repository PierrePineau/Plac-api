<?php
namespace App\EventSubscriber\Client;

use App\Event\Client\ClientGetEvent as Event;
use App\Service\Client\ClientManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientSubscriber implements EventSubscriberInterface
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
            ClientGetEvent::class => [
                ['getElement', 10],
                ['middleware', 9]
            ],
        ];
    }

    public function getElement(Event $event): Event
    {
        // On récupère l'utilisateur
        try {
            $client = $event->getClient();
            if (!$client) {
                $clientManager = $this->container->get(ClientManager::class);
                $data = $event->getData();
                $client = $clientManager->find($data['idClient']);

                $event->setClient($client);
            }

            return $event;

        } catch (\Throwable $th) {
            //throw $th;
            $event->setError($th->getMessage());
            $event->stopPropagation();

            throw new \Exception($th->getMessage(), $th->getCode());
            
            return $event;
        }
        
        return $event;
    }

    public function middleware(UserGetEvent $event): UserGetEvent
    {
        // On vérifie si l'utilisateur connecté est le même que celui que l'on veut modifier
        // Ou si l'utilisateur connecté est un admin
        $manager = $this->container->get(ClientManager::class);
        $manager->middleware([
            'client' => $event->getClient(),
            'organisation' => $event->getOrganisation(),
        ]);
        return $event;
    }
}