<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    /**
     * Get current user info - requires authentication
     */
    #[Route('/me', name: 'me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(
                ['error' => 'Not authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->json([
            // 'id' => $user->getId(),
            // 'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    // #[Route('/api/auth/login', name: 'api_login', methods: ['POST'])]
    // public function login(): void
    // {
    //     // This controller will never be executed,
    //     // json_login handles the request automatically
    //     throw new \Exception('This should never be reached.');
    // }
}
