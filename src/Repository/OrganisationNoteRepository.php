<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use App\Entity\OrganisationNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganisationNote>
 */
class OrganisationNoteRepository extends AbstractCoreRepository
{
    private $accessRelation;
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationNote::class);
        $this->accessRelation = 'organisationNotes';
    }
}
