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

#[Route('/api', name: 'api_')]
#[OA\Tag(name: 'Auth')]
class AuthController extends AbstractController
{
    #[OA\Get(
        summary: 'Logout',
    )]
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): JsonResponse
    {
        return $this->json([]);
    }
}
