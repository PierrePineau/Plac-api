<?php

namespace App\Controller\Core;

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
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_USER = 'ROLE_USER';
    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function _index(Request $request): JsonResponse
    {
        $data = $request->attributes->all();
        switch ($request->getMethod()) {
            case 'GET':
                $data = array_merge($data, $request->query->all());
                $response = $this->manager->search($data);
                break;
            case 'POST':
                $data = array_merge($data, $request->request->all());
                $response = $this->manager->create($data);
                break;
            case 'DELETE':
                $data = array_merge($data, $request->request->all());
                $response = $this->manager->remove($data);
                break;
            default:
                return $this->json([], JsonResponse::HTTP_BAD_REQUEST);
                break;
        }
        return $this->json($response, $response['success'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_BAD_REQUEST);
    }

    public function _get($id, Request $request): JsonResponse
    {
        $data = $request->attributes->all();
        switch ($request->getMethod()) {
            case 'GET':
                $data = array_merge($data, $request->query->all());
                $response = $this->manager->get($id, $data);
                break;
            case 'POST':
                $data = array_merge($data, $request->request->all());
                $response = $this->manager->update($id, $data);
                break;
            case 'DELETE':
                $data = array_merge($data, $request->request->all());
                $response = $this->manager->delete($id, $data);
                break;
            default:
                return $this->json([], JsonResponse::HTTP_BAD_REQUEST);
                break;
        }
        return $this->json($response, $response['success'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_BAD_REQUEST);
    }
}