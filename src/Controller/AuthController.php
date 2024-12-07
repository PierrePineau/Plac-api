<?php

namespace App\Controller;

use GuzzleHttp\Psr7\Response;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/api', name: 'api_auth_')]
#[OA\Tag(name: 'Auth')]
class AuthController extends AbstractController
{
    public const SCOPES = [
        'google' => [],
    ];

    #[OA\Get(
        summary: 'Logout',
    )]
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): JsonResponse
    {
        return $this->json([]);
    }


    #[OA\Post(
        summary: 'Login with oauth',
    )]
    #[Route('/oauth/login',name:"oauth_login", methods: ['GET','POST'])]
    public function oauthLogin(): JsonResponse
    {
        return $this->json([]);
    }

    #[OA\Post(
        summary: 'Login with for oauth',
    )]
    #[Route('/oauth/connect/{service}',name:"oauth_connect", methods: ['GET'])]
    public function oauthConnect(string $service, ClientRegistry $clientRegistry): RedirectResponse
    {
        if (!array_key_exists($service, self::SCOPES)) {
            throw $this->createNotFoundException();
        }

        return $clientRegistry->getClient($service)->redirect(self::SCOPES[$service]);
    }

    #[OA\Post(
        summary: 'Login check for oauth',
    )]
    #[Route('/oauth/check/{service}', name:"oauth_login_check", methods: ['GET', 'POST'])]
    public function oauthCheck(string $service, ClientRegistry $clientRegistry): Response
    {
        if (!array_key_exists($service, self::SCOPES)) {
            throw $this->createNotFoundException();
        }

        // L'autentification est géré par le firewall avec un JWT
        return new Response(200);
    }
}
