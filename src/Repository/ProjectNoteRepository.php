<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRelationnalRepository;
use App\Core\Repository\AbstractCoreRepository;
use App\Entity\ProjectNote;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectNote>
 */
class ProjectNoteRepository extends AbstractCoreRelationnalRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectNote::class);
    }
}
