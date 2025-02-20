<?php

namespace App\Controller\App\Organisation\User;

use App\Controller\Core\AbstractCoreController;
use App\Entity\User;
use App\Service\Organisation\OrganisationUserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA; 
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/app/organisations/{idOrganisation}/users', requirements: ['idOrganisation' => '[a-z0-9-]+'])]
#[OA\Tag(name: 'Organisation.User')]
#[Security(name: 'JWT')]
class OrganisationUserController extends AbstractCoreController
{
    public function __construct(OrganisationUserManager $manager)
    {
        parent::__construct($manager, User::class);
    }

    #[OA\Get(
        summary: 'List of members (Employees + Admins)',
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
        summary: 'Create new member (Employee)',
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
        summary: 'Get one member (Employee)',
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
        summary: 'Update one member (Employee)',
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
        summary: 'Delete one member (Employee)',
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
    public function get($idOrganisation, $uuid, Request $request): JsonResponse
    {
        return parent::_get($uuid, $request);
    }
}
