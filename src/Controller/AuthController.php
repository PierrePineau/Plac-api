<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;

#[Route('/api')]
#[OA\Tag(name: 'Auth')]
class AuthController extends AbstractController
{
    #[OA\Get(
        summary: 'Logout',
    )]
    #[Route('/logout', methods: ['GET'])]
    public function logout(): JsonResponse
    {
        return $this->json([]);
    }
}