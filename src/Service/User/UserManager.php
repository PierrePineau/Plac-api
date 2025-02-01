<?php

namespace App\Service\User;

use App\Core\Service\AbstractCoreService;
use App\Entity\User;
use App\Security\Middleware\UserMiddleware;
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

    public function loadUserByIdentifierAndPayload(string $identifier): ?User
    {
        return $this->em->getRepository(User::class)->loadUserByIdentifierAndPayload($identifier);
    }

    public function middleware(array $data): mixed
    {
        $user = $data['user'];
        $userConnected = $this->getUser();
        // On vérifie si l'utilisateur connecté est le même que celui que l'on veut modifier
        // Ou si l'utilisateur connecté est un admin
        if (!$this->security->isGranted(UserMiddleware::ACCESS, [
            'user' => $user,
            'userConnected' => $userConnected,
        ])) {
            $this->deniedException();
        }
        return $data;
    }

    public function find($id, bool $throwException = true)
    {
        $element = parent::find($id, $throwException);
        if ($element->isDeleted() && !$this->security->isGranted('ROLE_ADMIN')) {
            $this->notFoundException();
        }

        return $element;
    }

    public function _get($id, array $filters = []): mixed
    {
        return $this->find($id);
    }
    
    public function _create(array $data)
    {
        if (!isset($data['email'])) {
            $this->errorException($this->ELEMENT.'.email.required');
        }

        if (!isset($data['password'])) {
            $this->errorException($this->ELEMENT.'.password.required');
        }

        $email = $data['email'];
        $password = $data['password'];

        if ($this->findOneBy(['email' => $email])) {
            $this->errorException($this->ELEMENT_ALREADY_EXISTS);
        }
        
        $user = new User();
        $user->setEmail($email);
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

    public function _update($id, array $data)
    {
        $user = $this->find($id);
        
        $this->setData(
            $user,
            [
                'firstname' => [
                    'required' => false,
                    'nullable' => false,
                ],
                'lastname' => [
                    'required' => false,
                    'nullable' => false,
                ],
            ],
            $data,
        );

        $this->em->persist($user);
        $this->isValid($user);
        
        return $user;
    }

    public function _delete($id, array $data = [])
    {
        $user = $this->find($id);
        $user->setDeleted(true);
        $user->setDeletedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
        return [];
    }
}