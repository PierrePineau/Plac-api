<?php

namespace App\Controller\Checkout;

use App\Controller\Core\AbstractCoreController;
use App\Service\Checkout\CheckoutManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/checkout')]
#[OA\Tag(name: 'Checkout')]
class CheckoutController extends AbstractCoreController
{
    public function __construct(CheckoutManager $manager)
    {
        parent::__construct($manager);
    }

    #[OA\Post(
        summary: 'Get new session',
        description: 'Permet de créer une nouvelle session (idOrganisation => UUID de l\'organisation)',
        requestBody: new OA\RequestBody(
            description: '',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        'success_url' => new OA\Property(
                            type: 'string',
                            property: 'success_url',
                            description: 'Success url after payment',
                            example: 'http://localhost:3000/checkout/success'
                        ),
                        'cancel_url' => new OA\Property(
                            type: 'string',
                            property: 'cancel_url',
                            description: 'cancel url after payment',
                            example: 'http://localhost:3000/checkout/cancel'
                        ),
                        'idPlan' => new OA\Property(
                            type: 'integer',
                            property: 'idPlan',
                            description: 'Plan id',
                            example: 7
                        ),
                        'idOrganisation' => new OA\Property(
                            type: 'string',
                            property: 'idOrganisation',
                            description: 'Organisation uuid',
                            example: '0194f754-d195-7c52-8782-ec8690e97948'
                        ),
                    ]
                )
            )
        ),
    )]
    #[Security(name: 'JWT')]
    #[Route('/session', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        // $data = $request->attributes->all();
        $data = [];
        $data = array_merge($data, $request->request->all());
        $response = $this->manager->create($data);

        return $this->json($response, $response['success'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_BAD_REQUEST);
    }

    #[OA\Post(
        summary: 'Webhook called by the payment provider',
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
    #[Route('/webhook', methods: ['POST'])]
    public function webhook(Request $request): JsonResponse
    {
        // $data = $request->attributes->all();
        $data = [];
        $data = array_merge($data, $request->request->all());
        $response = $this->manager->webhook($data);

        return $this->json($response, $response['success'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_BAD_REQUEST);
    }
}
