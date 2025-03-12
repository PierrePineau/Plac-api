<?php

namespace App\Controller\Public;

use App\Entity\Plan;
use App\Service\Plan\PlanManager;
use App\Controller\Core\AbstractCoreController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/access')]
#[OA\Tag(name: 'Public.Access')]
class AccessController extends AbstractCoreController
{
    public function __construct(PlanManager $manager)
    {
        parent::__construct($manager, Plan::class);
    }

    #[OA\Get(
        summary: 'Get one',
        responses:
        [
            '200' => new OA\Response(
                response: 200,
                description: '',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/response'
                )
            )
        ],
    )]
    #[Route('?{token}', methods: ['GET'], requirements: ['token' => '[a-z0-9-]+'])]
    public function get($token, Request $request): JsonResponse
    {
        return parent::_get($token, $request);
    }
}
