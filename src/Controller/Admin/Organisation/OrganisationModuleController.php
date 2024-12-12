<?php

namespace App\Controller\Admin\Organisation;

use App\Entity\OrganisationModule;
use App\Service\Organisation\OrganisationModuleManager;
use App\Controller\Core\AbstractCoreController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/admin/organisations/{idOrganisation}/modules', requirements: ['idOrganisation' => '[a-z0-9-]+'])]
#[OA\Tag(name: 'Admin.Organisation')]
#[Security(name: 'JWT')]
class OrganisationModuleController extends AbstractCoreController
{
    public function __construct(OrganisationModuleManager $manager)
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
        ]
    )]
    #[OA\Post(
        summary: 'Add multiple modules to an organisation',
        description: 'Warning: This will remove all previous modules',
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
    public function index($idOrganisation, Request $request): JsonResponse
    {
        return parent::_index($request);
    }

    // #[OA\Get(
    //     summary: 'Get one',
    //     responses:
    //     [
    //         '200' => new OA\Response(
    //             response: 200,
    //             description: '',
    //             content: new OA\JsonContent(
    //                 ref: '#/components/schemas/response'
    //             )
    //         )
    //     ],
    // )]
    // #[OA\Post(
    //     summary: 'Update one',
    //     responses:
    //     [
    //         '200' => new OA\Response(
    //             response: 200,
    //             description: '',
    //             content: new OA\JsonContent(
    //                 ref: '#/components/schemas/response'
    //             )
    //         )
    //     ]
    // )]
    // #[OA\Delete(
    //     summary: 'Delete',
    //     responses:
    //     [
    //         '200' => new OA\Response(
    //             response: 200,
    //             description: '',
    //             content: new OA\JsonContent(
    //                 ref: '#/components/schemas/response'
    //             )
    //         )
    //     ],
    // )]
    // #[Route('/{uuid}', methods: ['GET', 'POST', 'DELETE'], requirements: ['uuid' => '[a-z0-9-]+'])]
    // public function get($idOrganisation, $id, Request $request): JsonResponse
    // {
    //     return parent::_get($id, $request);
    // }
}
