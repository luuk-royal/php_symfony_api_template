<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AuthenticationService;
use App\Service\UserService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends BaseController
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private UserService $userService
    ) {
    }

    #[Route(path: '/login', name: 'app_login', methods: 'POST')]
    public function login(): JsonResponse
    {
        try {
            return $this->json([

                "token" => $this->authenticationService->createToken($this->getUser())->getToken()
            ]);
        } catch (\Exception $exception) {
            return $this->json(json_encode($exception), 400);
        }
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/register', name: 'app_register', methods: 'POST')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $temp = json_decode($request->getContent(), true);

        if (!array_key_exists('username', $temp)) {
            return new JsonResponse('userName can not be null!', 400);
        }

        try {
            $user = $this->userService->findUser($temp['username']);

            if ($user != null){
                return new JsonResponse('Account with this name already exists!', 400);
            }

        } catch (NonUniqueResultException $e) {
            return new JsonResponse('Account with this name already exists!', 400);
        }


        if (!array_key_exists('password', $temp)) {
            return new JsonResponse('password can not be null!', 400);
        }

        $user = new User();
        $user->setUsername($temp['username']);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user, $temp['password']
            )
        );

        return JsonResponse::fromJsonString(
            json_encode($this->userService->store($user)->getUsername())
        );
    }
}
