<?php

namespace App\Controller;

use App\Core\Utils\Messenger;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/oauth', name: 'api_oauth_')]
#[OA\Tag(name: 'OAuth')]
class OAuthController extends AbstractController
{
    public const SCOPES = [
        'google' => [],
    ];

    #[OA\Post(
        summary: 'Login with oauth',
    )]
    #[Route('/login',name:"login", methods: ['GET','POST'])]
    public function oauthLogin(): JsonResponse
    {
        return $this->json([]);
    }

    #[OA\Post(
        summary: 'Connect with one oauth service',
    )]
    #[Route('/connect/{service}',name:"connect", methods: ['GET'])]
    public function oauthConnect(string $service, ClientRegistry $clientRegistry, Messenger $messenger): JsonResponse
    {
        if (!array_key_exists($service, self::SCOPES)) {
            // throw $this->createNotFoundException();
            return $this->json(
                $messenger->newResponse([
                    'success' => false,
                    'message' => 'oauth.error',
                    'code' => Response::HTTP_NOT_FOUND,
                    'data' => ['service' => $service]
                ]),
                Response::HTTP_NOT_FOUND
            );
        }
        $content = $clientRegistry->getClient($service)->redirect(self::SCOPES[$service], []);
        if (!is_array($content)) {
            $content = [$content];
        }
        $resp = $messenger->newResponse([
            'success' => true,
            'message' => 'oauth.redirect',
            'code' => Response::HTTP_OK,
            'data' => $content
        ]);
        return $this->json(
            $resp,
            Response::HTTP_OK
        );
    }

    #[OA\Post(
        summary: 'Login check for oauth',
    )]
    #[Route('/check/{service}', name:"login_check", methods: ['POST'])]
    public function oauthCheck(string $service, ClientRegistry $clientRegistry, Messenger $messenger): JsonResponse
    {
        if (!array_key_exists($service, self::SCOPES)) {
            // throw $this->createNotFoundException();
            return $this->json(
                $messenger->newResponse([
                    'success' => false,
                    'message' => 'oauth.error',
                    'code' => Response::HTTP_NOT_FOUND,
                    'data' => ['service' => $service]
                ]),
                Response::HTTP_NOT_FOUND
            );
        }

        // L'autentification est géré par le firewall avec un JWT
        return $this->json([]);
    }
}
