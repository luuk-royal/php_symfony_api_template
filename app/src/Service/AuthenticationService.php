<?php

namespace App\Service;

use App\Entity\AccessToken;
use App\Repository\AccessTokenRepository;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationService
{
    private AccessTokenRepository $accessTokenRepository;

    public function __construct(
        AccessTokenRepository $accessTokenRepository
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function generateToken(): string
    {
        return sha1(random_bytes(32));
    }


    /**
     * @throws InvalidArgumentException
     */
    public function createToken(UserInterface $user): AccessToken
    {
        // Can still happen if the user/token isn't properly set in the guards.
        if ($user === null) {
            throw new InvalidArgumentException('User cannot be null!');
        }

        $token = new AccessToken();
        $token->setToken($this->generateToken());
        $token->setUser($user);

        return $this->accessTokenRepository->save($token);
    }
}