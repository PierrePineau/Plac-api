<?php

namespace App\Controller\App\Organisation\Project;

use App\Controller\Core\AbstractCoreController;
use App\Entity\Project;
use App\Service\Organisation\OrganisationProjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA; 
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/app/organisations/{idOrganisation}/projects', requirements: ['idOrganisation' => '[a-z0-9-]+'])]
#[OA\Tag(name: 'Organisation.Project')]
#[Security(name: 'JWT')]
class OrganisationProjectController extends AbstractCoreController
{
    public function __construct(OrganisationProjectManager $manager)
    {
        parent::__construct($manager, Project::class);
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
    #[Route('/{uuid}', methods: ['GET', 'POST', 'DELETE'], requirements: ['uuid' => '[a-z0-9-]+'])]
    public function get($idOrganisation, $id, Request $request): JsonResponse
    {
        return parent::_get($id, $request);
    }
}
