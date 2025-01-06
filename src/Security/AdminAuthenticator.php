<?php

namespace App\Security;

use App\Core\Utils\Messenger;
use App\Service\Admin\AdminManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EventListener\UserProviderListener;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminAuthenticator extends JWTAuthenticator
{
    private $container;
    private $translator;
    private $logger;

    private $eventDispatcher;
    private $passwordHash;

    private $userProvider;
    private $jwtManager;

    public const API_KEY_INVALID = 'api_key_invalid';
    public const API_KEY_MISSING = 'api_key_missing';
    public const API_KEY_CODE_MISSING = 'api_key_code_missing';

    public const API_KEY_NOT_FOUND = 'api_key_not_found';
    public const USER_NOT_FOUND = 'admin.not_found';
    public const INVALID_CREDENTIALS = 'invalid_credentials';
    public const CREDENTIALS_EXPIRED = 'credentials_expired';

    // Your own logic
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $eventDispatcher,
        TokenExtractorInterface $tokenExtractor,
        UserProviderInterface $adminProvider,
        TranslatorInterface $translator = null,
        $container,
        UserPasswordHasherInterface $passwordHash,
        LoggerInterface $logger,
    )
    {
        parent::__construct(
            $jwtManager,
            $eventDispatcher,
            $tokenExtractor,
            $adminProvider,
            $translator
        );
        $this->container = $container;
        $this->jwtManager = $jwtManager;
        $this->passwordHash = $passwordHash;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->userProvider = $adminProvider;
    }

    public function supports(Request $request): ?bool
    {
        // $this->logger->debug($this->getTokenExtractor()->extract($request));
        // return false !== $this->getTokenExtractor()->extract($request);
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $this->logger->debug('orem');

        $token = $this->getTokenExtractor()->extract($request);
        if ($token) {
            try {
                if ($token === false) {
                    throw new \LogicException('Unable to extract a JWT token from the request. Also, make sure to call `supports()` before `authenticate()` to get a proper client error.');
                }
                if (!$payload = $this->jwtManager->parse($token)) {
                    // throw new InvalidTokenException('Invalid JWT Token');
                    throw new CustomUserMessageAuthenticationException($this::CREDENTIALS_EXPIRED, [], Response::HTTP_UNAUTHORIZED);
                }
            } catch (JWTDecodeFailureException $e) {
                if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                    // throw new ExpiredTokenException();
                    throw new CustomUserMessageAuthenticationException($this::CREDENTIALS_EXPIRED, [], Response::HTTP_UNAUTHORIZED);
                }

                // throw new InvalidTokenException('Invalid JWT Token', 0, $e);
                throw new CustomUserMessageAuthenticationException($this::CREDENTIALS_EXPIRED, [], Response::HTTP_UNAUTHORIZED);
            }

            $idClaim = $this->jwtManager->getUserIdClaim();
            if (!isset($payload[$idClaim])) {
                throw new InvalidPayloadException($idClaim);
            }

            $passport = new SelfValidatingPassport(
                new UserBadge(
                    (string) $payload[$idClaim],
                    fn ($userIdentifier) => $this->loadUser($payload, $userIdentifier)
                )
            );

            $passport->setAttribute('payload', $payload);
            $passport->setAttribute('token', $token);

            return $passport;

        }else {
            // On authentifie l'utilisateur
            try {
                $data = $request->getContent();
                $data = json_decode($data, true);
                $identifier = $data['username'];
                $password = $data['password'];

                $this->logger->debug(json_encode($data));

                $AdminManager = $this->container->get(AdminManager::class);
                $user = $AdminManager->findOneByIdentifier($identifier);
                $this->logger->debug($user ? 'User found' : 'User not found');
            
                if (!$user) {
                    throw new CustomUserMessageAuthenticationException($this::USER_NOT_FOUND, [], Response::HTTP_UNAUTHORIZED);
                }

                if ($this->passwordHash->isPasswordValid($user, $password)) {
                    $token = $this->jwtManager->create($user);
                    $payload = $this->jwtManager->parse($token);
                    $idClaim = $this->jwtManager->getUserIdClaim();
                    if (!isset($payload[$idClaim])) {
                        throw new InvalidPayloadException($idClaim);
                    }

                    $passport = new SelfValidatingPassport(
                        new UserBadge(
                            (string)$payload[$idClaim],
                            function ($userIdentifier) use ($user) {
                                return $this->userProvider->loadUserByIdentifier($userIdentifier);
                            }
                        )
                    );
                    $passport->setAttribute('payload', $payload);
                    $passport->setAttribute('token', $token);
                    return $passport;
                }else{
                    throw new CustomUserMessageAuthenticationException($this::INVALID_CREDENTIALS, [], Response::HTTP_UNAUTHORIZED);
                }
            } catch (\Throwable $th) {
                throw new CustomUserMessageAuthenticationException($th->getMessage(), [], Response::HTTP_UNAUTHORIZED);
                return null;
            }
        }
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        if ($authException instanceof CustomUserMessageAuthenticationException) {
            $data = [
                // you may want to customize or obfuscate the message first
                'code' => $authException->getCode(),
                'error' => $authException->getMessage(),
                'message' => $this->translator->trans($authException->getMessage())
                // or to translate this message
                // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
            ];
            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }else{
            return parent::start($request, $authException);
        }
    }
}
