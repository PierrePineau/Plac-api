<?php

namespace App\EventListener;

use App\Entity\Admin;
use App\Entity\User;
use App\Service\User\UserOrganisationManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTAuthenticationSuccessListener
{
	public function __construct(
		private $container
	) {}
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
			return $event;
		}

		// Une fois l'authentification réussie, on récupère l'utilisateur pour renvoyer ses informations
		if ($user instanceof User) {
			// $data = array_merge($data, $user->getInfos());
			$data['user'] = $user->getInfos();

			$organisation = $this->container->get(UserOrganisationManager::class)->getOneOrganisationsByUser([
				'idUser' => $user->getId()
			]);

			$data['organisation'] = $organisation ? $organisation->getInfos() : null;
		}

		if ($user instanceof Admin) {
			// $data = array_merge($data, $user->getInfos());
			$data['user'] = $user->getInfos();
		}

		$event->setData($data);
	}
}