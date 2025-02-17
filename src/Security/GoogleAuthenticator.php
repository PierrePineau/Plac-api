<?php

namespace App\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GoogleAuthenticator extends AbstractOAuthAuthenticator
{
    protected string $serviceName = 'google';
    protected const FIREWALL_NAME = 'app';
    private ?TranslatorInterface $translator;
    private ?EventDispatcherInterface $eventDispatcher;
    private ?HttpClientInterface $httpClient;

    public function __construct(
        ClientRegistry $clientRegistry,
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $eventDispatcher,
        TokenExtractorInterface $tokenExtractor,
        UserProviderInterface $userProvider,
        TranslatorInterface $translator = null,
        HttpClientInterface $httpClient
    ) {
        parent::__construct(
            $jwtManager,
            $eventDispatcher,
            $tokenExtractor,
            $userProvider,
            $translator
        );
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->httpClient = $httpClient;
    }

    public function authenticate(Request $request): Passport
    {
        $googleToken = $request->get('token');
        if (!$googleToken) {
            throw new AuthenticationException('No Google token provided');
        }

        // Vérifiez le token auprès des serveurs de Google
        $response = $this->httpClient->request(
            'GET',
            'https://www.googleapis.com/oauth2/v3/tokeninfo',
            [
                'query' => ['id_token' => $googleToken],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new AuthenticationException('Invalid Google token');
        }

        $googleUser = $response->toArray();
        $email = $googleUser['email'] ?? null;

        if (!$email) {
            throw new AuthenticationException('Google token does not contain an email');
        }

        $payload = [
            'email' => $email,
            'oauth' => 'google',
            'google_id' => $googleUser['sub'],
        ];

        $idClaim = 'email';

        // Charge ou créer l'utilisateur 
        $passport = new SelfValidatingPassport(
            new UserBadge(
                (string) $payload[$idClaim],
                fn ($userIdentifier) => $this->loadUser($payload, $userIdentifier)
            )
        );

        $token = $this->createToken($passport, self::FIREWALL_NAME);

        $passport->setAttribute('payload', $payload);
        $passport->setAttribute('token', $token);

        return $passport;
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
