<?php

namespace App\Security;

use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class EmailPasswordAuthenticator extends AbstractAuthenticator
{
    private const FIELD_USERNAME = 'username';
    private const FIELD_PASSWORD = 'password';

    public function __construct(
        private UserProviderInterface $provider,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod(Request::METHOD_POST) && \in_array($request->getContentType(), ['json', 'application/json']);
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $credentials = $this->getCredentials($request);

        if (null === $credentials) {
            throw new CustomUserMessageAuthenticationException('Missing credentials for user login');
        }

        $user = $this->getUser($credentials);

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
    }

    public function getCredentials(Request $request): ?array
    {
        $content = json_decode($request->getContent(), true);

        if (!isset($content[self::FIELD_USERNAME])) {
            return null;
        }

        if (!isset($content[self::FIELD_PASSWORD])) {
            return null;
        }

        return [
            'username' => $content[self::FIELD_USERNAME],
            'password' => $content[self::FIELD_PASSWORD]
        ];
    }

    public function getUser($credentials): UserInterface
    {
        $user = $this->provider->loadUserByIdentifier($credentials['username']);

        if (!$this->userPasswordHasher->isPasswordValid($user, $credentials['password'])) {
            throw new AccessDeniedException('Invalid password provided');
        }

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}