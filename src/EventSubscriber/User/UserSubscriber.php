<?php
namespace App\EventSubscriber\User;

use App\Event\User\UserGetEvent;
use App\Service\User\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
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
            UserGetEvent::class => [
                ['onUserGetEvent', 10],
                ['middleware', 9]
            ],
        ];
    }

    public function onUserGetEvent(UserGetEvent $event): UserGetEvent
    {
        // On récupère l'utilisateur
        try {
            $user = $event->getUser();
            if (!$user) {
                $userManager = $this->container->get(UserManager::class);
                $data = $event->getData();
                if (!isset($data['idUser'])) {
                    throw new \Exception($userManager::ELEMENT.'.id.required', );
                }
                $user = $userManager->find($data['idUser']);

                $event->setUser($user);
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
        $userManager = $this->container->get(UserManager::class);
        $userManager->middleware([
            'user' => $event->getUser()
        ]);
        return $event;
    }
}