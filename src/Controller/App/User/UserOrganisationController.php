<?php

namespace App\Controller\App\User;

use App\Controller\Core\AbstractCoreController;
use App\Service\User\UserOrganisationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/app/users/{idUser}/organisations', requirements: ['idUser' => '[a-z0-9-]+'])]
#[OA\Tag(name: 'User.Organisation')]
#[Security(name: 'JWT')]
class UserOrganisationController extends AbstractCoreController
{
    public function __construct(UserOrganisationManager $manager)
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
    public function index($idUser, Request $request): JsonResponse
    {
        return parent::_index($request);
    }
}
