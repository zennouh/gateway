<?php

namespace App\Controller\Api;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth', name: 'api_auth_')]
class LoginController extends AbstractController
{
    public function __construct(
        private AuthService $authService,
    ) {}

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json(
                ['error' => 'Missing email or password'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $token = $this->authService->authenticate($data['email'], $data['password']);

            return $this->json([
                'token' => $token,
                'message' => 'Login successful',
            ]);
        } catch (\Exception) {
            return $this->json(
                ['error' => 'Invalid credentials!!'],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}
