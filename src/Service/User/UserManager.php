<?php

namespace App\Service\User;

use App\Core\Exception\DeniedException;
use App\Core\Service\AbstractCoreService;
use App\Entity\User;
use App\Event\Client\UserCreateEvent;
use App\Security\Middleware\UserMiddleware;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use League\OAuth2\Client\Provider\GoogleUser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class UserManager extends AbstractCoreService
{
    public const ROLE_USER = 'ROLE_USER'; // Les utilisateurs avec ce role peuvent se connecter
    public const ROLE_ADMIN = 'ROLE_ADMIN'; // Les utilisateurs avec ce role peuvent modifier les autres utilisateurs
    public const ROLE_EMPLOYE = 'ROLE_EMPLOYE'; // Les utilisateurs au sein d'une organisation
    private $passwordHash;
    private $jwtManager;

    public function __construct($container, $entityManager, Security $security, UserPasswordHasherInterface $passwordHash, JWTTokenManagerInterface $jwtManager)
    {
        $this->passwordHash = $passwordHash;
        $this->jwtManager = $jwtManager;
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

    public function create(array $data): ?array
    {
        try {
            $data = $this->guardMiddleware($data);
            $user = $this->_create($data);

            $this->em->flush();

            $returnData['user'] = $user->toArray('create');
            $authenticateUser = $this->getUser();

            // Si l'utilisateur connecté est un admin, on lui retourne le token
            if (!$authenticateUser->isAuthenticate()) {
                $token = $this->jwtManager->create($user);
                $returnData['token'] = $token;
            }

            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT_CREATED,
                'code' => 201,
                'data' => $returnData,
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
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
        
        // if ($authenticateUser->isAuthenticate() && $authenticateUser->isSuperAdmin()) {
        //     // On ne fait rien si c'est le superAdmin qui créer un compte
        // }else{
        //     // On send un event pour la création d'un compte
        //     $newEvent = new UserCreateEvent([
        //         'user' => $user,
        //     ]);
        //     // Send Event
        //     $this->dispatchEvent($newEvent);
        // }

        // Envoie email activation du compte par exemple
        $newEvent = new UserCreateEvent([
            'user' => $user,
            'authenticateUser' => $this->getUser(),
        ]);
        // Send Event
        $this->dispatchEvent($newEvent);

        return $user;
    }

    // Fonction utilisé pour créer un utilisateur, aussi utilisé dans le cadre de la création d'un compte employé
    public function _createUser(array $data): User
    {
        $user = new User();

        $this->setData(
            $user,
            [
                'email' => [
                    'required' => true,
                    'nullable' => false,
                ],
                'firstname' => [
                ],
                'lastname' => [
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

    // Fonction appelée lors de la création / connection via oauth
    public function oauth(array $data): ?array
    {
        try {
            $provider = $data['provider'];
            $oauthUser = $data['oauthUser'];

            $key = $provider.'Id';
            $setter = 'set'.ucfirst($provider).'Id';

            $user = $this->em->getRepository(User::class)->findOneBy([
                'email' => $oauthUser->getEmail(),
            ]);
            
            if (!$user) {
                $user = $this->_createUser([
                    'email' => $oauthUser->getEmail(),
                    'firstname' => $oauthUser->getFirstName(),
                    'lastname' => $oauthUser->getLastName(),
                    'password' => uniqid() . Uuid::v7()->toRfc4122(), // On lui génère un mot de passe aléatoire
                ]);
                $user->setRoles([self::ROLE_USER, self::ROLE_ADMIN]);

                // On associe le provider à l'utilisateur
                // On check si la méthode setProviderId existe sur l'objet User
                if (method_exists($user, $setter)) {
                    // setGoogleId par exemple
                    $user->$setter($oauthUser->getId());
                }
                $this->em->persist($user);

                $newEvent = new UserCreateEvent([
                    'user' => $user,
                    'authenticateUser' => $this->getUser(),
                ]);
                // Send Event
                $this->dispatchEvent($newEvent);
            }

            $returnData['user'] = $user->toArray('create');
            $authenticateUser = $this->getUser();

            // Si l'utilisateur connecté est un admin, on lui retourne le token
            if (!$authenticateUser->isAuthenticate()) {
                $token = $this->jwtManager->create($user);
                $returnData['token'] = $token;
            }

            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT.'.oauth',
                'code' => 200,
                'data' => $returnData,
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }

    public function me(array $data): ?array
    {
        try {
            // On récupère l'utilisateur connecté
            $authenticateUser = $this->getUser();

            if (!$authenticateUser->isAuthenticate()) {
                throw new DeniedException();
            }

            $user = $this->findOneBy([
                'id' => $authenticateUser->getId()
            ]);

            $returnData = [];
            $returnData['user'] = $user->toArray('auth');

			$organisation = $this->container->get(UserOrganisationManager::class)->getOneOrganisationsByUser([
				'idUser' => $user->getId(),
			]);

			$returnData['organisation'] = $organisation ? $organisation->getInfos() : null;
            
            return $this->messenger->newResponse([
                'success' => true,
                'message' => $this->ELEMENT_FOUND,
                'code' => 200,
                'data' => $returnData,
            ]);
        } catch (\Throwable $th) {
            return $this->messenger->errorResponse($th);
        }
    }
}