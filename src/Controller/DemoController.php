<?php

namespace App\Controller;

use App\Service\AppManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api')]
#[OA\Tag(name: 'default')]
class DemoController extends AbstractController
{
    #[OA\Get(
        summary: 'Demo route',
        description: 'Route for verify if the API is working',
        responses:
            [
                '200' => new OA\Response(
                    response: 200,
                    description: 'Return a message',
                    content: new OA\JsonContent(
                        type: 'object',
                        properties: [
                            'message' => new OA\Property(
                                type: 'string',
                                property: 'message',
                                example: 'Hello World!'
                            )
                        ],
                        example: [
                            'message' => 'Hello World!'
                        ]
                    )   
                )
            ]
    )]
    #[Route('/demo', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // $ip = $request->getClientIp();
        // dd($ip);
        return $this->json([
            'message' => "Hello World!",
        ]);
    }
}
