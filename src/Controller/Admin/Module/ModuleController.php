<?php

namespace App\Controller\Admin\Module;

use App\Entity\Module;
use App\Service\Module\ModuleManager;
use App\Controller\Core\AbstractCoreController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/admin/modules')]
#[OA\Tag(name: 'Admin.Module')]
#[Security(name: 'JWT')]
class ModuleController extends AbstractCoreController
{
    public function __construct(ModuleManager $manager)
    {
        parent::__construct($manager, Module::class);
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
    )]
    #[Route('/{id}', methods: ['GET', 'POST', 'DELETE'], requirements: ['id' => '[a-z0-9-]+'])]
    public function get($id, Request $request): JsonResponse
    {
        return parent::_get($id, $request);
    }
}
