<?php

namespace App\Controller\App\User;

use App\Entity\User;
use App\Service\User\UserManager;
use App\Controller\Core\AbstractCoreController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/app/users')]
#[OA\Tag(name: 'User')]
#[Security(name: 'JWT')]
class UserController extends AbstractCoreController
{
    public function __construct(UserManager $manager)
    {
        parent::__construct($manager, User::class);
    }

    #[OA\Post(
        summary: 'Create new',
        requestBody: new OA\RequestBody(
            description: '',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    ref: new Model(type: User::class, groups: ['create'])
                )
            )
        ),
        responses:
        [
            '201' => new OA\Response(
                response: 201,
                description: '',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/response'
                ) 
            )
        ]
    )]
    #[Route('', methods: ['POST'])] 
    public function index(Request $request): JsonResponse
    {
        return parent::_index($request);
    }

    #[OA\Get(
        summary: 'Get current user',
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
    #[Route('/me', methods: ['GET'])]
    public function me(Request $request, UserManager $manager): JsonResponse
    {
        $response = $manager->me($request->query->all());
        return $this->json($response, $response['success'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_BAD_REQUEST);
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
        ]
    )]
    #[OA\Post(
        summary: 'Update one',
        requestBody: new OA\RequestBody(
            description: '',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    ref: new Model(type: User::class, groups: ['update'])
                )
            )
        ),
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
        ]
    )]
    #[IsGranted('ROLE_USER', statusCode: 423)]
    #[Route('/{uuid}', methods: ['GET', 'POST', 'DELETE'], requirements: ['id' => '[a-z0-9-]+'])]
    public function get($id, Request $request): JsonResponse
    {
        return parent::_get($id, $request);
    }
}
