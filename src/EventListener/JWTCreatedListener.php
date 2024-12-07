<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
	/**
	 * @var RequestStack
	 */
	private $requestStack;

    private $repoSite;

	/**
	 * @param RequestStack $requestStack
	 */
	public function __construct(RequestStack $requestStack, $entityManager)
	{
		$this->requestStack = $requestStack;
        $this->repoSite = $entityManager->getRepository(Site::class);
	}

	/**
	 * @param JWTCreatedEvent $event
	 *
	 * @return void
	 */
	public function onJWTCreated(JWTCreatedEvent $event)
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
	}
}