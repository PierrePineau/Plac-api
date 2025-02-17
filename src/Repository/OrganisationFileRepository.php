<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Core\Traits\OrganisationRepositoryTrait;
use App\Entity\OrganisationFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganisationFile>
 */
class OrganisationFileRepository extends AbstractCoreRepository
{
    private $accessRelation;
    use OrganisationRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganisationFile::class);
        $this->accessRelation = 'organisationFiles';
    }
}