<?php

namespace App\Security;

use App\Entity\User;
use App\Services\JwtService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly JwtService $jwtService,
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $user = $token->getUser();

        $response = new JsonResponse([
            "token" => $this->jwtService->generateToken($user),
            'message' => 'Login successful',

        ]);

        return $response;
    }
}
