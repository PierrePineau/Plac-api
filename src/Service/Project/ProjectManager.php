<?php

namespace App\Service\Project;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
use App\Entity\Project;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectManager extends AbstractCoreService
{
    use OrganisationTrait;

    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Project',
            'entity' => Project::class,
        ]);
    }

    // Pour gérer un project il faut que soit défini une organisation
    // Le middleware permet de vérifier si l'organisation est bien défini et si l'utilisateur a les droits
    public function guardMiddleware(array $data): array
    {
        $organisation = $this->getOrganisation($data);

        $data['organisation'] = $organisation;

        return $data;
    }

    public function _create(array $data)
    {
        $organisation = $data['organisation'];

        $project = new Project();
        $project->addOrganisationProject($organisation);
        $project->setName($data['name']);
        // $project->setDescription($data['description']);
        // $project->setStartDate(new \DateTime($data['startDate']));
        // $project->setEndDate(new \DateTime($data['endDate']));

        $this->em->persist($project);
        $this->isValid($project);

        return $project;
    }
}