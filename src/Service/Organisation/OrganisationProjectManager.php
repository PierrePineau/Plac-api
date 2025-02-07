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
            'code' => 'Organisation.Project',
            'entity' => OrganisationProject::class,
            'security' => $security,
        ]);
    }

    public function _search(array $filters = []): array
    {
        $manager = $this->container->get(ProjectManager::class);
        return $manager->_search($filters);
    }

    public function _get($id, array $filters = []): mixed
    {
        $element = $this->findOneByAccess([
            'id' => $id,
            'organisation' => $filters['organisation'],
        ]);
        return $element;
    }

    public function _create(array $data)
    {
        $organisation = $data['organisation'];

        $manager = $this->container->get(ProjectManager::class);
        $project = $manager->_create($data);

        $orgProject = new OrganisationProject();
        $orgProject->setProject($project);
        $orgProject->setOrganisation($organisation);

        $this->em->persist($orgProject);
        $this->isValid($orgProject);

        return $project;
    }

    public function _update($id, array $data)
    {
        $project = $this->_get($id, [
            'idOrganisation' => $data['organisation']->getId(),
        ]);
        $orgProject = $project->getOrganisationProject();

        $manager = $this->container->get(ProjectManager::class);
        $project = $manager->_update($orgProject->getProject(), $data);

        $this->em->persist($orgProject);
        $this->isValid($orgProject);

        return $project;
    }

    public function _delete($id, array $filters = [])
    {
        $project = $this->_get($id, [
            'idOrganisation' => $filters['organisation']->getId(),
        ]);

        $manager = $this->container->get(ProjectManager::class);
        return $manager->_delete($project);
    }
}