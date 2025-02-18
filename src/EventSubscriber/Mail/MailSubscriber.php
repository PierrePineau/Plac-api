<?php
namespace App\EventSubscriber\Mail;

use App\Event\Client\UserCreateEvent;
use App\Event\Organisation\OrganisationGetEvent;
use App\Model\Mail;
use App\Service\Mail\MailManager;
use App\Service\Organisation\OrganisationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailSubscriber implements EventSubscriberInterface
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
            UserCreateEvent::class => [
                ['onCreateUser', 1],
            ],
        ];
    }

    public function onCreateUser(UserCreateEvent $event): UserCreateEvent
    {
        try {
            // On envoie un email pour que l'utilisateur vérifie son compte
            $user = $event->getUser();
            if ($user && $user->isEmailVerified() == false) {
                $mailManager = $this->container->get(MailManager::class);

                $mail = new Mail();
                $mail->setTo([
                    'name' => $user->getFullName(),
                    'email' => $user->getEmail()
                ]);
                $resp = $mailManager->send($mail, [
                    'template' => MailManager::TEMPLATE_USER_ACTIVATION,
                    'data' => [
                        'user' => $user,
                        'url' => 'https://gestion-plac.fr/verify'
                    ]
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