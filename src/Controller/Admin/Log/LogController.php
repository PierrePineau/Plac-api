<?php

namespace App\Controller\Admin\Log;

use App\Controller\Core\AbstractCoreController;
use App\Core\Utils\Messenger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/logs')]
#[OA\Tag(name: 'Logs')]
#[Security(name: 'JWT')]
class LogController extends AbstractController
{
    #[OA\Get(
        summary: 'List of logs',
        parameters: [
        ],
        responses:
        [
        ],
    )]
    #[Route('/{path}', methods: ['GET'])] 
    public function index($path, Request $request, Messenger $messenger): JsonResponse
    {
        try {
            if (in_array($path, ['dev.log', 'dev_test.log'])) {
                return $this->json($messenger->newResponse([
                    'success' => true,
                    'data' => [
                        'logs' => file_get_contents($this->getParameter('kernel.logs_dir') . '/' . $path),
                    ],
                ]));
            }
            return $this->json($messenger->newResponse([
                'success' => false,
                'code' => 'file.not_found',
            ]));
        } catch (\Throwable $th) {
            //throw $th;

            return $this->json($messenger->newResponse([
                'success' => false,
                'code' => 'file.error',
                'message' => $th->getMessage(),
            ]));
        }
        
    }
}
