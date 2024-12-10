<?php

namespace App\Controller\Core;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCoreRelationnalController extends AbstractController
{
    public $manager;
    public $entity;
    public function __construct($manager, $entity)
    {
        $this->manager = $manager;
        $this->entity = $entity;
    }

    #[Route('', methods: ['GET', 'POST', 'DELETE'])] 
    public function index($idRelated, Request $request): JsonResponse
    {
        switch ($request->getMethod()) {
            case 'GET':
                $data = $request->query->all();
                if ($idRelated) {
                    $data['id'.ucfirst($this->entity)] = $idRelated;
                }
                $response = $this->manager->search($data);
                break;
            case 'POST':
                $data = $request->request->all();
                if ($idRelated) {
                    $data['id'.ucfirst($this->entity)] = $idRelated;
                }
                $response = $this->manager->create($data);
                break;
            case 'DELETE':
                $data = $request->request->all();
                if ($idRelated) {
                    $data['id'.ucfirst($this->entity)] = $idRelated;
                }
                $response = $this->manager->remove($data);
                break;
            default:
                return $this->json([], JsonResponse::HTTP_BAD_REQUEST);
                break;
        }
        return $this->json($response, $response['success'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', methods: ['GET', 'POST', 'DELETE'], requirements: ['id' => '\d+'])]
    public function get($idRelated, $id, Request $request): JsonResponse
    {
        switch ($request->getMethod()) {
            case 'GET':
                $data = $request->query->all();
                if ($idRelated) {
                    $data['id'.ucfirst($this->entity)] = $idRelated;
                }
                $response = $this->manager->get($id, $request->query->all());
                break;
            case 'POST':
                $data = $request->request->all();
                if ($idRelated) {
                    $data['id'.ucfirst($this->entity)] = $idRelated;
                }
                $response = $this->manager->update($id, $data);
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