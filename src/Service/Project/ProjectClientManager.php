<?php

namespace App\Service\Project;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\OrganisationTrait;
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
        $by = $data['by'] ?? 'project';

        if ($by === 'project') {
            $project = $data['project'];
            $projectClients = $this->findBy([
                'project' => $project->getId(),
            ]);
            $existingClients = [];
            foreach ($projectClients as $projectClient) {
                $existingClients[] = $projectClient->getClient()->getId();
            }

            $clients = $data['clients'];
            
            foreach ($clients as $client) {
                if (!in_array($client->getId(), $existingClients)) {
                    $projectClient = new ProjectClient();
                    $projectClient->setProject($project);
                    $projectClient->setClient($client);
                    $this->em->persist($projectClient);
                }
            }
        }elseif ($by === 'client') {
            $client = $data['client'];
            $projectClients = $this->findBy([
                'client' => $client->getId(),
            ]);
            $existingProjects = [];
            foreach ($projectClients as $projectClient) {
                $existingProjects[] = $projectClient->getProject()->getId();
            }

            $projects = $data['projects'];
            
            foreach ($projects as $project) {
                if (!in_array($project->getId(), $existingProjects)) {
                    $projectClient = new ProjectClient();
                    $projectClient->setProject($project);
                    $projectClient->setClient($client);
                    $this->em->persist($projectClient);
                }
            }
        }
        
    }

    public function _remove(array $data)
    {
        $projectClients = $this->findBy([
            'id' => $$data['ids'],
        ]);
        foreach ($data['projectClients'] as $projectClients) {
            $this->em->remove($projectClients);
        }
    }
}