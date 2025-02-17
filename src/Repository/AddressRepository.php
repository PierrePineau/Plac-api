<?php

namespace App\Repository;

use App\Core\Repository\AbstractCoreRepository;
use App\Entity\Address;
use Doctrine\Persistence\ManagerRegistry;

class AddressRepository extends AbstractCoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }
}
