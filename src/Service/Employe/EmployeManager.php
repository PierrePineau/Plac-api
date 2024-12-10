<?php

namespace App\Service\Employe;

use App\Core\Service\AbstractCoreService;
use App\Entity\Employe;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EmployeManager extends AbstractCoreService
{
    private $passwordHash;
    public function __construct($container, $entityManager, Security $security, UserPasswordHasherInterface $passwordHash)
    {
        $this->passwordHash = $passwordHash;
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'employe',
            'entity' => Employe::class,
            'security' => $security,
        ]);
    }
}