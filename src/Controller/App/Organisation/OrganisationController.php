<?php

namespace App\Controller\App\Organisation;

use App\Service\Organisation\OrganisationManager;
use App\Controller\Core\AbstractCoreController;
use App\Entity\Organisation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/app/organisations')]
#[OA\Tag(name: 'Organisation')]
#[Security(name: 'JWT')]
class OrganisationController extends AbstractCoreController
{
    public function __construct(OrganisationManager $manager)
    {
        parent::__construct($manager);
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
                    ref: new Model(type: Organisation::class, groups: ['update'])
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
