<?php

namespace App\Service\Admin;

use App\Core\Service\AbstractCoreService;
use App\Entity\Admin;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminManager extends AbstractCoreService
{
    private $passwordHash;
    public function __construct($container, $entityManager, Security $security, UserPasswordHasherInterface $passwordHash)
    {
        $this->passwordHash = $passwordHash;
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'code' => 'admin',
            'entity' => Admin::class,
        ]);
    }

    public function loadUserByIdentifier(string $identifier): ?Admin
    {
        return $this->em->getRepository(Admin::class)->loadUserByIdentifier($identifier);
    }

    public function _create(array $data): Admin
    {
        if (!isset($data['email'])) {
            throw new \Exception('Email is required');
        }

        if (!isset($data['password'])) {
            throw new \Exception('Password is required');
        }

        $email = $data['email'];
        $password = $data['password'];

        if ($this->findOneBy(['email' => $email])) {
            throw new \Exception($this->ELEMENT_ALREADY_EXISTS);
        }
        
        $admin = new Admin();
        $admin->setEmail($email);
        $hashedPassword = $this->passwordHash->hashPassword(
            $admin,
            $password
        );
        $admin->setPassword($hashedPassword);
        $admin->setRoles(['ROLE_ADMIN']);

        $this->em->persist($admin);
        // $this->em->flush(); // Le flush est fait dans le AbstractCoreService

        return $admin;
    }
}