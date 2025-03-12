<?php

namespace App\Repository;

use App\Entity\Task;
use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends AbstractCoreRepository
{
    private $accessRelation;
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
        $this->accessRelation = 'organisationTasks';
    }
}
