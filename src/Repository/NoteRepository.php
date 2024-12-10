<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Entity\Note;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends AbstractCoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }
}
