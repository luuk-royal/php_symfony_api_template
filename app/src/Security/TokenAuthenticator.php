<?php

namespace App\Security;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    private const  AUTHENTICATION_HEADER = 'authentication';

    public function __construct(
        private AccessTokenRepository $accessTokenRepository
    ) {}

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::AUTHENTICATION_HEADER);
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get(self::AUTHENTICATION_HEADER);
        if (null === $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $accessToken = $this->accessTokenRepository->findOneByToken($apiToken);

        if ($accessToken->isValid()) {
            $user = $this->getUser($accessToken);

            return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
        } else {
            throw new AuthenticationException("Token has expired, Please log in again!");
        }
    }

    public function getUser(AccessToken $accessToken): User
    {

        if (null == $accessToken) {
            throw new AuthenticationException('Acces token has not been found!');
        }

        return $accessToken->getUser();
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
