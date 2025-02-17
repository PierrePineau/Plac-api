<?php

namespace App\Controller\Public;

use App\Entity\Plan;
use App\Service\Plan\PlanManager;
use App\Controller\Core\AbstractCoreController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/plans')]
#[OA\Tag(name: 'Public.Plan')]
class PlanController extends AbstractCoreController
{
    public function __construct(PlanManager $manager)
    {
        parent::__construct($manager, Plan::class);
    }

    #[OA\Get(
        summary: 'List of',
        parameters: [
            new OA\Parameter(
                ref: '#/components/parameters/page',
            ),
            new OA\Parameter(
                ref: '#/components/parameters/search',
            ),
            new OA\Parameter(
                ref: '#/components/parameters/order',
            ),
            new OA\Parameter(
                ref: '#/components/parameters/limit',
            ),
        ],
        responses:
        [
            '200' => new OA\Response(
                response: 200,
                description: '',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/list'
                ) 
            )
        ]
    )]
    #[Route('', methods: ['GET'])] 
    public function index(Request $request): JsonResponse
    {
        return parent::_index($request);
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
    #[Route('/{id}', methods: ['GET'], requirements: ['id' => '[a-z0-9-]+'])]
    public function get($id, Request $request): JsonResponse
    {
        return parent::_get($id, $request);
    }
}
