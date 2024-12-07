<?php

namespace App\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

abstract class AbstractOAuthAuthenticator extends JWTAuthenticator implements AuthenticationEntryPointInterface
{
    protected string $serviceName = '';
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $eventDispatcher,
        TokenExtractorInterface $tokenExtractor,
        UserProviderInterface $userProvider,
        TranslatorInterface $translator = null,
    ) {
        parent::__construct(
            $jwtManager,
            $eventDispatcher,
            $tokenExtractor,
            $userProvider,
            $translator
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
}
