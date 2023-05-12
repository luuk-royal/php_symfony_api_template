<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;

class UserService
{
    public function __construct(
        private UserRepository $userRepository
    )
    {

    }

    public function store(User $user): User
    {
        return $this->userRepository->save($user);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findUser(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }
}