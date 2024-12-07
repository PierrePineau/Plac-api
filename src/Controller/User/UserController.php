<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Service\User\UserManager;
use App\Core\Core\Controller\AbstractCoreController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/users')]
#[OA\Tag(name: 'User')]
class UserController extends AbstractCoreController
{
    public function __construct(UserManager $manager)
    {
        parent::__construct($manager, User::class);
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
        security: [
            [
                'JWT' => [],
            ]
        ]
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
        security: [
            [
                'JWT' => [],
            ]
        ]
    )]
    #[Route('', methods: ['GET', 'POST'])] 
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
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
        security: [
            [
                'JWT' => [],
            ]
        ]
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
        security: [
            [
                'JWT' => [],
            ]
        ]
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
        security: [
            [
                'JWT' => [],
            ]
        ]
    )]
    #[Route('/{id}', methods: ['GET', 'POST', 'DELETE'], requirements: ['id' => '\d+'])]
    public function get($id, Request $request): JsonResponse
    {
        parent::get($id, $request);
    }
}
