<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTAuthenticationSuccessListener
{
	/**
	 * @param AuthenticationSuccessEvent $event
	 *
	 * @return void
	 */
	public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
	{
        $data = $event->getData();
		$user = $event->getUser();

		if (!$user instanceof UserInterface) {
			return;
		}

		// Une fois l'authentification réussie, on récupère l'utilisateur pour renvoyer ses informations
		if ($user instanceof User) {
			$data['user'] = $user->getInfos();
		}

		$event->setData($data);
	}
}