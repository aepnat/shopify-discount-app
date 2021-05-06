<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class SessionAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $shop = $request->get('shop');

        if (!$shop) {
            return new Response('Your session has expired. Please access the app via Shopify Admin again.');
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('index', [
                'shop' => $shop,
            ])
        );
    }

    public function supports(Request $request): bool
    {
        if (!$session = $request->getSession()) {
            return false;
        }

        return true;
    }

    public function getCredentials(Request $request)
    {
        if (!$session = $request->getSession()) {
            return false;
        }

        if (!$session->has('shopUrl')) {
            return false;
        }

        return [
            'shopUrl' => $session->get('shopUrl'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        if (!$credentials) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            return null;
        }

        return $userProvider->loadUserByUsername($credentials['shopUrl']);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        return new RedirectResponse(
            $this->urlGenerator->generate('index')
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        return null;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}