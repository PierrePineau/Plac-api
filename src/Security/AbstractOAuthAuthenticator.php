<?php

namespace App\Security;

use App\Entity\User;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractOAuthAuthenticator extends JWTAuthenticator implements AuthenticationEntryPointInterface
{
    protected string $serviceName = '';
    protected const FIREWALL_NAME = 'app';
    private ?ClientRegistry $clientRegistry;
    private ?TranslatorInterface $translator;
    private ?EventDispatcherInterface $eventDispatcher;
    private $entityManager;
    public function __construct(
        $entityManager,
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $eventDispatcher,
        TokenExtractorInterface $tokenExtractor,
        UserProviderInterface $userProvider,
        TranslatorInterface $translator,
        ClientRegistry $clientRegistry,
        string $serviceName
    ) {
        parent::__construct(
            $jwtManager,
            $eventDispatcher,
            $tokenExtractor,
            $userProvider,
            $translator
        );
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->clientRegistry = $clientRegistry;
        $this->serviceName = $serviceName;
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient($this->serviceName);
        $oauthUser = $client->fetchUser();

        if (!$oauthUser) {
            throw new AuthenticationException('No user found');
        }
        // $accessToken = $client->getAccessToken();
        // $user = $client->fetchUserFromToken($accessToken);
        return new SelfValidatingPassport(
            new UserBadge($oauthUser->getId(), function() use ($oauthUser, $client) {
                // googleId
                $key = $this->serviceName . 'Id';
                // 1) have they logged in with Facebook before? Easy!
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(
                    [$key => $oauthUser->getId()]
                );
                return $existingUser;
            })
        );
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'api_oauth_login_check' && $request->get('service') === $this->serviceName;
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
            $exception = new MissingTokenException($authException->getMessage() ?? 'JWT Token not found', 0, $authException);
            $event = new JWTNotFoundEvent($exception, new JWTAuthenticationFailureResponse($exception->getMessageKey()), $request);
            $this->eventDispatcher->dispatch($event, $authException->getMessage());
            return $event->getResponse();
        }
    }
}
