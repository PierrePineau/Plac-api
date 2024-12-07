<?php

namespace App\Service\User;

use App\Core\Service\AbstractCoreService;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager extends AbstractCoreService
{
    private $passwordHash;
    public function __construct($container, $entityManager, UserPasswordHasherInterface $passwordHash)
    {
        $this->passwordHash = $passwordHash;
        parent::__construct($container, $entityManager, [
            'code' => 'user',
            'entity' => User::class,
        ]);
    }

    public function findOneByIdentifier(string $identifier): ?User
    {
        return $this->findOneBy(['email' => $identifier]);
    }
    // public function _create(array $data): Admin
    // {
    //     if (!isset($data['email'])) {
    //         throw new \Exception('Email is required');
    //     }

    //     if (!isset($data['password'])) {
    //         throw new \Exception('Password is required');
    //     }

    //     $email = $data['email'];
    //     $password = $data['password'];

    //     if ($this->findOneBy(['email' => $email])) {
    //         throw new \Exception($this->ELEMENT_ALREADY_EXISTS);
    //     }
        
    //     $admin = new Admin();
    //     $admin->setEmail($email);
    //     $hashedPassword = $this->passwordHash->hashPassword(
    //         $admin,
    //         $password
    //     );
    //     $admin->setPassword($hashedPassword);
    //     $admin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN']);

    //     $this->em->persist($admin);
    //     // $this->em->flush(); // Le flush est fait dans le AbstractCoreService

    //     return $admin;
    // }
}