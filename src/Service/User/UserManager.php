<?php

namespace App\Service\User;

use App\Core\Service\AbstractCoreService;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager extends AbstractCoreService
{
    private $passwordHash;
    public function __construct($container, $entityManager, Security $security, UserPasswordHasherInterface $passwordHash)
    {
        $this->passwordHash = $passwordHash;
        parent::__construct($container, $entityManager, [
            'identifier' => 'uuid',
            'code' => 'user',
            'entity' => User::class,
            'security' => $security,
        ]);
    }

    public function findOneByIdentifier(string $identifier): ?User
    {
        return $this->em->getRepository(User::class)->loadUserByIdentifier($identifier);
    }
    
    public function _create(array $data)
    {
        if (!isset($data['email'])) {
            throw new \Exception($this->ELEMENT.'.email.required');
        }

        if (!isset($data['password'])) {
            throw new \Exception($this->ELEMENT.'.password.required');
        }

        $email = $data['email'];
        $password = $data['password'];

        if ($this->findOneBy(['email' => $email])) {
            throw new \Exception($this->ELEMENT_ALREADY_EXISTS);
        }
        
        $user = new User();
        $user->setEmail($email);
        $user->setUuid($this->generateUuid());
        $hashedPassword = $this->passwordHash->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        // $this->em->flush(); // Le flush est fait dans le AbstractCoreService
        // Vérifie si l'entité est valide
        $this->isValid($user);
        return $user;
    }
}