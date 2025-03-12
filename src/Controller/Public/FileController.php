<?php

namespace App\Controller\Public;

use App\Controller\Core\AbstractCoreController;
use App\Entity\File;
use App\Service\File\FileManager;
use App\Service\Organisation\OrganisationFileManager;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA; 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

#[Route('/api/organisations/{idOrganisation}/files', requirements: ['idOrganisation' => '[a-z0-9-]+'])]
#[OA\Tag(name: 'Public.File')]
class FileController extends AbstractCoreController
{
    public function __construct(FileManager $manager)
    {
        parent::__construct($manager);
    }

    #[OA\Get(
        summary: 'Get one',
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
    #[Route('/{url}', methods: ['GET'])]
    public function get($url, Request $request): Response
    {
        $data = [];
        $data = array_merge($data, $request->attributes->get('_route_params') ?? []);
        $data = array_merge($data, $request->query->all());
        $response = $this->manager->get($url, $data);

        if ($response['success'] && $response['data']['content']) {
            $file = $response['data']['content'];
            $fileObj = $response['data']['file'];
            $response = new Response($file['Body']->getContents());
            $response->headers->set('Content-Type', $file['ContentType']);
            $response->headers->set('Content-Length', $file['ContentLength']);
            $response->headers->set('Content-Disposition', 'inline; filename="' . $fileObj['name'] . '"');
            $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
            $response->setPublic();
            $response->setMaxAge(600);
            return $response;
        }else{
            return new JsonResponse($response, $response['success'] ? JsonResponse::HTTP_OK : JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}