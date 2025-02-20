<?php

namespace App\Service\User;

use App\Core\Exception\DeniedException;
use App\Core\Service\AbstractCoreService;
use App\Entity\User;
use App\Event\Client\UserCreateEvent;
use App\Security\Middleware\UserMiddleware;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager extends AbstractCoreService
{
    public const ROLE_USER = 'ROLE_USER'; // Les utilisateurs avec ce role peuvent se connecter
    public const ROLE_ADMIN = 'ROLE_ADMIN'; // Les utilisateurs avec ce role peuvent modifier les autres utilisateurs
    public const ROLE_EMPLOYE = 'ROLE_EMPLOYE'; // Les utilisateurs au sein d'une organisation
    private $passwordHash;
    public function __construct($container, $entityManager, Security $security, UserPasswordHasherInterface $passwordHash)
    {
        $this->passwordHash = $passwordHash;
        parent::__construct($container, $entityManager, [
            'security' => $security,
            'identifier' => 'uuid',
            'code' => 'user',
            'entity' => User::class,
        ]);
    }

    public function loadUserByIdentifierAndPayload(string $identifier): ?User
    {
        return $this->em->getRepository(User::class)->loadUserByIdentifierAndPayload($identifier);
    }

    public function middleware(array $data): mixed
    {
        $user = $data['user'];
        $authenticateUser = $this->getUser();
        // On vérifie si l'utilisateur connecté est le même que celui que l'on veut modifier
        // Ou si l'utilisateur connecté est un admin
        if (!$this->security->isGranted(UserMiddleware::ACCESS, [
            'user' => $user,
            'authenticateUser' => $authenticateUser,
        ])) {
            throw new DeniedException();
        }
        return $data;
    }

    public function find($id, bool $throwException = true)
    {
        $element = parent::find($id, $throwException);
        if ($element->isDeleted() && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new NotFoundHttpException();
        }

        return $element;
    }
    
    // Fonction appelée lors de la création d'un utilisateur (création de compte et d'une organisation)
    public function _create(array $data)
    {
        if (!isset($data['email'])) {
            throw new NotFoundHttpException($this->ELEMENT.'.email.required');
        }
        if (FILTER_VAR($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new NotFoundHttpException($this->ELEMENT.'.email.invalid');
        }
        if ($this->findOneBy(['email' => $data['email']])) {
            throw new NotFoundHttpException($this->ELEMENT_ALREADY_EXISTS);
        }
        $user = $this->_createUser($data);
        $user->setRoles([self::ROLE_USER, self::ROLE_ADMIN]);
        
        $this->isValid($user);

        $this->em->flush();
        
        $authenticateUser = $this->getUser();
        if ($authenticateUser->isAuthenticate() && $authenticateUser->isSuperAdmin()) {
            // On ne fait rien si c'est le superAdmin qui créer un compte
        }else{
            // Envoie email activation du compte
            // On send un event pour la création d'un compte
            $newEvent = new UserCreateEvent([
                'user' => $user,
            ]);
            // Send Event
            $this->dispatchEvent($newEvent);
        }
        return $user;
    }

    // Fonction utilisé pour créer un utilisateur, aussi utilisé dans le cadre de la création d'un compte employé
    public function _createUser(array $data): User
    {
        $user = new User();

        $this->setData(
            $user,
            [
                'firstname' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'lastname' => [
                    'required' => true,
                    'nullable' => false,
                ],
            ],
            $data
        );

        $hashedPassword = $this->passwordHash->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashedPassword);

        $this->em->persist($user);

        return $user;
    }

    public function _update($id, array $data)
    {
        $user = $this->_get($id);
        
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
        $user = $this->_get($id);
        $user->setDeleted(true);
        $user->setDeletedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
        return [];
    }
}