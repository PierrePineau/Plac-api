<?php

namespace App\Controller\App\Organisation\Client;

use App\Controller\Core\AbstractCoreController;
use App\Entity\Client;
use App\Service\Organisation\OrganisationClientManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/app/organisations/{idOrganisation}/clients', requirements: ['idOrganisation' => '[a-z0-9-]+'])]
#[OA\Tag(name: 'Organisation.Client')]
#[Security(name: 'JWT')]
class OrganisationClientController extends AbstractCoreController
{
    public function __construct(OrganisationClientManager $manager)
    {
        parent::__construct($manager);
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
        requestBody: new OA\RequestBody(
            description: '',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    ref: new Model(type: Client::class, groups: ['create'])
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
        ],
    )]
    #[Route('', methods: ['GET', 'POST'])] 
    public function index($idOrganisation, Request $request): JsonResponse
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
                    ref: new Model(type: Client::class, groups: ['update'])
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
    #[Route('/{uuid}', methods: ['GET', 'POST', 'DELETE'], requirements: ['uuid' => '[a-z0-9-]+'])]
    public function get($uuid, Request $request): JsonResponse
    {
        return parent::_get($uuid, $request);
    }
}
