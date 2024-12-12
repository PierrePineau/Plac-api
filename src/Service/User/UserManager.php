<?php

namespace App\Service\User;

use App\Core\Service\AbstractCoreService;
use App\Core\Traits\UserTrait;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager extends AbstractCoreService
{
    use UserTrait;
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

    public function middleware(array $data): mixed
    {
        $user = $data['user'];
        $userConnected = $this->getUser();

        // On vérifie si l'utilisateur connecté est le même que celui que l'on veut modifier
        // Ou si l'utilisateur connecté est un admin
        if ($user->getId() !== $userConnected->getId() && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new \Exception($this->ELEMENT.'.not_allowed', 423);
        }
        return $data;
    }

    public function _get($id, array $filters = []): mixed
    {
        $element = $this->getCustomer([
            'idUser' => $id,
        ]);
        $element = $element->toArray();

        return $element;
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
        $customer = $this->getCustomer([
            'idUser' => $id,
        ]);
        // CANNOT UPDATE EMAIL
        // if (isset($data['email'])) {
        //     $user->setEmail($data['email']);
        // }

        $this->setData(
            $customer,
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

        $this->em->persist($customer);
        $this->isValid($customer);
        
        return $customer;
    }

    public function _delete($id, array $data = [])
    {
        $customer = $this->getCustomer([
            'idUser' => $id,
        ]);

        $this->em->remove($customer);
    }
}