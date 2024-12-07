<?php

namespace App\Core\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// #[Route('/api/shop/products')]
// #[OA\Tag(name: 'Shop.Product')]
abstract class AbstractCoreController extends AbstractController
{
    public $manager;
    public $entity;
    /**
     * @param class-string $entity
     */
    public function __construct($manager, $entity)
    {
        $this->manager = $manager;
    }

    #[Route('', methods: ['GET', 'POST'])] 
    public function index(Request $request): JsonResponse
    {
        switch ($request->getMethod()) {
            case 'GET':
                $filters = $request->query->all();
                $response = $this->manager->list($filters);
                break;
            case 'POST':
                $response = $this->manager->create($request->request->all());
                break;
            default:
                return $this->json([], JsonResponse::HTTP_BAD_REQUEST);
                break;
        }
        return $this->json($response, $response['success'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', methods: ['GET', 'POST', 'DELETE'], requirements: ['id' => '\d+'])]
    public function get($id, Request $request): JsonResponse
    {
        switch ($request->getMethod()) {
            case 'GET':
                $response = $this->manager->get($id, $request->query->all());
                break;
            case 'POST':
                $response = $this->manager->update($id, $request->request->all());
                break;
            case 'DELETE':
                $response = $this->manager->delete($id);
                break;
            default:
                return $this->json([], JsonResponse::HTTP_BAD_REQUEST);
                break;
        }
        return $this->json($response, $response['success'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_BAD_REQUEST);
    }
}