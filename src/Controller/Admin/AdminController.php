<?php

namespace App\Controller\Admin;

use App\Controller\Core\AbstractCoreController;
use App\Service\Admin\AdminManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Json;

#[Route('/api/admin')]
#[OA\Tag(name: 'Admin')]
#[Security(name: 'JWT')]
class AdminController extends AbstractCoreController
{
    public function __construct(AdminManager $manager)
    {
        parent::__construct($manager, Admin::class);
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
        ],
    )]
    #[OA\Post(
        summary: 'Create new',
        responses:
        [
            '201' => new OA\Response(
                response: 201,
                description: '',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/response'
                ) 
            )
        ],
    )]
    #[Route('', methods: ['GET', 'POST'])] 
    public function index(Request $request): JsonResponse
    {
        return $this->json([], JsonResponse::HTTP_UNAUTHORIZED);
        // return parent::index($request);
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
    #[OA\Post(
        summary: 'Update one',
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
    #[OA\Delete(
        summary: 'Delete',
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
    #[Route('/{id}', methods: ['GET', 'POST', 'DELETE'], requirements: ['id' => '\d+'])]
    public function get($id, Request $request): JsonResponse
    {
        // parent::get($id, $request);
        return $this->json([], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
