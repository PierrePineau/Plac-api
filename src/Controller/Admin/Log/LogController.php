<?php

namespace App\Controller\Admin\Log;

use App\Core\Utils\Messenger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/admin/logs')]
#[OA\Tag(name: 'Logs')]
#[Security(name: 'JWT')]
class LogController extends AbstractController
{
    #[OA\Get(
        summary: 'List of logs',
        parameters: [
            new OA\Parameter(
                name: 'path',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['dev.log', 'dev_test.log', 'prod.log']
                ),
                description: 'The log file to retrieve'
            )
        ],
    )]
    #[Route('/{path}', methods: ['GET'])] 
    public function index($path, Request $request, Messenger $messenger): JsonResponse
    {
        try {
            $log = null;
            if (in_array($path, ['dev.log', 'dev_test.log', 'prod.log'])) {
                $log = $this->getParameter('kernel.logs_dir') . '/' . $path;
                if (!file_exists($log)) {
                    $log = null;
                }
                $resp = $messenger->newResponse([
                    'success' => true,
                    'code' => 'log.found',
                    'message' => 'Log found',
                    'data' => file_get_contents($log),
                ]);
            }else{
                $resp = $messenger->newResponse([
                    'success' => false,
                    'code' => 'log.not_found',
                    'message' => 'Log not found',
                ]);
            }
            return $this->json($resp, $resp['success'] ? 200 : 404);
        } catch (\Throwable $th) {
            return $this->json([$messenger->newResponse([
                'success' => false,
                'code' => 'file.error',
                'message' => $th->getMessage(),
            ])], 500);
        }
    }
}
