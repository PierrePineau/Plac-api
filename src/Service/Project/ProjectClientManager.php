<?php

namespace App\Service\Project;

use App\Core\Service\AbstractCoreService;
use App\Entity\ProjectClient;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectClientManager extends AbstractCoreService
{
    public function __construct($container, $entityManager, Security $security)
    {
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'Project.Client',
            'entity' => ProjectClient::class,
            'security' => $security,
        ]);
    }

    public function _add(array $data)
    {
        $project = $data['project'];
        $clients = $data['clients'];
        
        foreach ($clients as $client) {
            $projectClient = new ProjectClient();
            $projectClient->setProject($project);
            $projectClient->setClient($client);

            $this->em->persist($projectClient);
            $this->isValid($projectClient);
        }
    }

    public function _remove(array $data)
    {
        $project = $data['project'];
        // $projectClients = $this->findBy([
        //     'project' => $project->getId(),
        //     'client' => $data['ids'],
        // ]);
        $projectClients = $this->findBy([
            'id' => $$data['ids'],
        ]);
        foreach ($data['projectClients'] as $projectClients) {
            $this->em->remove($projectClients);
        }
    }
}