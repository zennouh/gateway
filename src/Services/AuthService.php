<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\JwtService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class AuthService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private JwtService $jwtService,
        private UserRepository $userRepository,
    ) {}

    /**
     * Authenticate a user with email and password
     * 
     * @param string $email
     * @param string $password
     * @return string JWT token
     * @throws BadCredentialsException
     */
    public function authenticate(string $email, string $password): string
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$this->verifyPassword($user, $password)) {
            throw new BadCredentialsException('Invalid credentials!!');
        }

        return $this->createToken($user);
    }

    /**
     * Verify a password against a user
     * 
     * @param User|null $user
     * @param string $password
     * @return bool
     */
    public function verifyPassword(?User $user, string $password): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    /**
     * Hash a password
     * 
     * @param User $user
     * @param string $password
     * @return string
     */
    public function hashPassword(User $user, string $password): string
    {
        return $this->passwordHasher->hashPassword($user, $password);
    }

    /**
     * Create a JWT token for a user
     * 
     * @param User $user
     * @return string
     */
    public function createToken(User $user): string
    {
        return $this->jwtService->generateToken($user);
    }
}
