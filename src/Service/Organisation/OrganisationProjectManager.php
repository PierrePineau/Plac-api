<?php

namespace App\Service\Organisation;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\OrganisationProject;
use App\Service\Project\ProjectManager;
use Symfony\Bundle\SecurityBundle\Security;

class OrganisationProjectManager extends AbstractCoreService
{
    use OrganisationTrait;
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'code' => 'Organisation.Project',
            'entity' => OrganisationProject::class,
            'elementManagerClass' => ProjectManager::class,
            'guardActions' => [
                'organisation' => 'getOrganisation',
            ],
        ]);
    }

    public function generateDefault(array $data = [])
    {
        $organisation = $data['organisation'];

        $this->_create([
            'organisation' => $organisation,
            'name' => 'Nouveau projet',
            'description' => 'La description de votre projet',
        ]);
    }

    public function _search(array $filters = []): array
    {
        $manager = $this->getElementManager();
        return $manager->_search($filters);
    }

    public function _get($id, array $filters = []): mixed
    {
        return $this->_getOrganisationElement($id, $filters);
    }

    public function _update($id, array $data)
    {
        return $this->_updateOrganisationElement($id, $data);
    }

    public function _delete($id, array $filters = [])
    {
        return $this->_deleteOrganisationElement($id, $filters);
    }

    public function guardMiddleware(array $data): array
    {
        foreach ($this->guardActions as $key => $actions) {
            $data[$key] = $this->$actions($data);
        }
        return $data;
    }

    public function _create(array $data)
    {
        $organisation = $data['organisation'];

        $manager = $this->getElementManager();
        $element = $manager->_create($data);

        $orgElement = new OrganisationProject();
        $orgElement->setProject($element);
        $orgElement->setOrganisation($organisation);

        $this->em->persist($orgElement);
        $this->isValid($orgElement);

        return $element;
    }

    public function setStatus(array $data)
    {
        try {
            $data = $this->guardMiddleware($data);
            $orgProject = $this->_get($data['idProject'], [
                'idOrganisation' => $data['organisation']->getId(),
            ]);

            $orgStatusManager = $this->container->get(OrganisationStatusManager::class);
            $orgStatus = $orgStatusManager->_get($data['idStatus'], [
                'idOrganisation' => $data['organisation']->getId(),
            ]);

            $project = $orgProject->getProject();
            $project->setStatus($orgStatus->getStatus());

            $this->em->persist($project);
            $this->isValid($project);

            $this->em->flush();

            return $this->messenger->newResponse(
                [
                    'success' => true,
                    'message' => $this->ELEMENT_UPDATED,
                    'code' => 200,
                    'data' => $project->toArray()
                ]
            );
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }
}