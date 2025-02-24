<?php
namespace App\EventSubscriber\Mail;

use App\Event\Client\UserCreateEvent;
use App\Model\Mail;
use App\Service\Mail\MailManager;
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
            $data = $event->getData();
            $authenticateUser = $data['authenticateUser'];
            // On check que ce n'est pas un admin qui a créé le compte
            if ($authenticateUser->isAdmin()) {
                // On set l'email comme vérifié
                // $user->setEmailVerified(true);
                return $event;
            }
            if ($user && $user->isEmailVerified() == false) {
                $mailManager = $this->container->get(MailManager::class);

                $mail = new Mail();
                $mail->addDestinataire([
                    'name' => $user->getFullName(),
                    'email' => $user->getEmail()
                ]);
                $mail->setSubject(MailManager::SUBJECT_USER_ACTIVATION);
                $resp = $mailManager->send($mail, [
                    'template' => MailManager::TEMPLATE_USER_ACTIVATION,
                    'data' => [
                        'user' => $user,
                        'url' => 'https://gestion-plac.fr/verify',
                        'subject' => $mail->getSubject() // Pour le render twig
                    ]
                ]);
            }

            return $event;
        } catch (\Throwable $th) {
            //throw $th;
            throw new \Exception($th->getMessage(), 400, $th);
            $event->setError($th->getMessage());
            
            $event->stopPropagation();

            return $event;
        }
        
        return $event;
    }
}