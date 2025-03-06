<?php

namespace App\Controller;

use App\Core\Utils\Messenger;
use App\Service\User\UserManager;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
    #[Route('/connect/{service}', name:"connect", methods: ['POST'])]
    public function oauthConnect(string $service, Request $request, ClientRegistry $clientRegistry, Messenger $messenger): JsonResponse
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
        
        $data = [];
        if ($request->headers->get('Content-Type') === 'application/json') {
            try {
                $json = $request->getContent();
                $data = json_decode($json, true) ?? [];
            } catch (\Throwable $th) {
                //throw $th;
                $data = [];
            }
        }

        // $returnUrl = null;
        // if (isset($data['return_url']) && is_string($data['return_url'])) {
        //     $returnUrl = $data['return_url'] ?? null;
        // } else {
        //     $returnUrl = $request->query->get('return_url') ?? null;
        // }
        
        $content = $clientRegistry->getClient($service)->redirect(self::SCOPES[$service], [
        ]);

        
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

    #[OA\Get(
        summary: 'Login check for oauth',
    )]
    #[Route('/check/{service}', name:"login_check", methods: ['GET'])]
    public function oauthCheck(string $service, Request $request, ClientRegistry $clientRegistry, Messenger $messenger, UserManager $userManager): Response
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

        $client = $clientRegistry->getClient($service);
        $accessToken = $client->getAccessToken();
        $user = $client->fetchUserFromToken($accessToken);
        // $user = $client->fetchUser();

        $resp = $userManager->oauth([
            'provider' => $service,
            'oauthUser' => $user,
        ]);

        $isDemo = $request->query->get('demo') ?? false;
        if (in_array($isDemo, ['true', true])) {
            // On fait une redirection vers la page demandée avec les données de l'utilisateur
            return $this->json(
                [
                    'success' => true,
                    'message' => 'oauth.success',
                    'code' => Response::HTTP_OK,
                    'data' => $resp,
                ],
                Response::HTTP_OK
            );
        }else{
            $returnUrl = $_ENV['OAUTH_RETURN_URL'];
            $returnUrl += '?oauth=' . $service;
            return new RedirectResponse($returnUrl, 302, [
                'data' => json_encode($resp),
            ]);
        }
    }
}
