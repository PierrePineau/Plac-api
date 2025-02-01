<?php
namespace App\EventSubscriber\Client;

use App\Event\Client\ClientGetEvent;
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
                ['onClientGetEvent', 10],
            ],
        ];
    }

    public function onClientGetEvent(ClientGetEvent $event): ClientGetEvent
    {
        // On récupère client
        try {
            $client = $event->getClient();
            if (!$client) {
                $clientManager = $this->container->get(ClientManager::class);
                $data = $event->getData();
                if (!isset($data['idClient'])) {
                    throw new \Exception($clientManager::ELEMENT.'.id.required');
                }
                $client = $clientManager->findOneByAccess($data);

                $event->setClient($client);
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