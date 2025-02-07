<?php

namespace App\Service\Project;

use App\Entity\Project;
use App\Core\Service\AbstractCoreService;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Project',
            'entity' => Project::class,
        ]);
    }

    public function _create(array $data)
    {
        $project = new Project();
        $project->setName($data['name']);
        // $project->setDescription($data['description']);
        // $project->setStartDate(new \DateTime($data['startDate']));
        // $project->setEndDate(new \DateTime($data['endDate']));

        $this->em->persist($project);
        $this->isValid($project);

        return $project;
    }

    public function _update($id, array $data)
    {
        $element = $this->_get($id);

        $this->setData(
            $element,
            [
                'name' => [
                    'required' => true,
                    'nullable' => true,
                ],
            ],
            $data
        );

        $this->em->persist($element);
        $this->isValid($element);

        return $element;
    }
}