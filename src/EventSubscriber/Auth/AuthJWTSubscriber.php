<?php
namespace App\EventSubscriber\Auth;

use App\Entity\Admin;
use App\Entity\User;
use App\Service\User\UserOrganisationManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthJWTSubscriber implements EventSubscriberInterface
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
            JWTCreatedEvent::class => [
                ['onJWTCreatedEvent', 1],
            ],
            AuthenticationSuccessEvent::class => [
                ['onAuthenticationSuccessEvent', 1],
            ],
        ];
    }

    public function onJWTCreatedEvent(JWTCreatedEvent $event): JWTCreatedEvent
    {
        $user = $event->getUser();
		$payload = $event->getData();
		// $domain = null;
        // if ($user instanceof User) {
		// 	$siteCode = $this->requestStack->getCurrentRequest()->headers->get('X-API-SITE-CODE');
		// 	if (strpos($siteCode, '.') !== false) {
		// 		$dataSiteCodes = explode('.', $siteCode);
		// 		$siteCode = $dataSiteCodes[0];
		// 		$domain = $dataSiteCodes[1] ? $dataSiteCodes[1] : null;
		// 	}
        //     $site = $this->repoSite->findOneBy(['code' => $siteCode]);

        //     $dataSite = $site->getInfosSite($domain);
        //     // array to string
        //     // $dataSite = json_encode($dataSite);
        //     $payload['site_code'] = $siteCode;
        //     $payload['site'] = $dataSite;
		// 	$payload['id'] = $user->getId();
		// 	if ($domain) {
		// 		$payload['domain'] = $domain;
		// 	}
        // }else if ($user instanceof Customer){
		// 	$siteCode = $this->requestStack->getCurrentRequest()->headers->get('X-API-SITE-CODE');
		// 	if (strpos($siteCode, '.') !== false) {
		// 		$dataSiteCodes = explode('.', $siteCode);
		// 		$siteCode = $dataSiteCodes[0];
		// 		$domain = $dataSiteCodes[1] ? $dataSiteCodes[1] : null;
		// 	}
        //     $payload['site_code'] = $siteCode;
		// 	if ($domain) {
		// 		$payload['domain'] = $domain;
		// 	}
		// 	$payload['id'] = $user->getId();
		// 	$payload['username'] = $user->getEmail();
		// 	$payload['firstname'] = $user->getFirstname();
		// 	$payload['lastname'] = $user->getLastname();

        //     // ON NE RECUPERE PAS LE TOKEN DU PANIER, des actions sont faites sur le panier avant de générer le token/récupérer le token
		// 	// $payload['cartToken'] = $user->getCart()[0]->getToken();
		// }elseif ($user instanceof Admin) {
        //     $payload['id'] = $user->getId();
        // }
		$event->setData($payload);

        return $event;
    }

    public function onAuthenticationSuccessEvent(AuthenticationSuccessEvent $event): AuthenticationSuccessEvent
    {
        $data = $event->getData();
		$user = $event->getUser();
		// var_dump('lorem');

		if (!$user instanceof UserInterface) {
			return $event;
		}

		// Une fois l'authentification réussie, on récupère l'utilisateur pour renvoyer ses informations
		if ($user instanceof User) {
			$data = array_merge($data, $user->getInfos());

			$organisation = $this->container->get(UserOrganisationManager::class)->getOneOrganisationsByUser([
				'idUser' => $user->getId()
			]);

			$data['organisation'] = $organisation ? $organisation->getInfos() : null;
		}

		if ($user instanceof Admin) {
			$data = array_merge($data, $user->getInfos());
		}

		$event->setData($data);
	}
}