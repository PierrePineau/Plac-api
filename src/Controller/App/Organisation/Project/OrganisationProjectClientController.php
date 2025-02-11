<?php

namespace App\Controller\App\Organisation\Project;

use App\Controller\Core\AbstractCoreController;
use App\Entity\Project;
use App\Service\Organisation\OrganisationProjectClientManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA; 
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/app/organisations/{idOrganisation}/projects/{idProject}/clients', requirements: ['idOrganisation' => '[a-z0-9-]+', 'idProject' => '[a-z0-9-]+'])]
#[OA\Tag(name: 'Organisation.Project')]
#[Security(name: 'JWT')]
class OrganisationProjectNoteController extends AbstractCoreController
{
    public function __construct(OrganisationProjectClientManager $manager)
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
        summary: 'Add',
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
    #[Route('', methods: ['GET', 'POST', 'DELETE'])]
    public function index($idOrganisation, $idProject, Request $request): JsonResponse
    {
        return parent::_index($request);
    }
}
