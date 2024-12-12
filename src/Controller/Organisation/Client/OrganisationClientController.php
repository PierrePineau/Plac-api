<?php

namespace App\Controller\Organisation\Client;

use App\Controller\Core\AbstractCoreController;
use App\Service\Organisation\OrganisationClientManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
}
