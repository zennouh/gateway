<?php

namespace App\Services;

use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class JwtService
{
    private const DEFAULT_TTL_SECONDS = 3600;

    public function __construct(
        #[Autowire('%env(APP_SECRET)%')]
        private  string $secret,
    ) {}

    public function generateToken(User $user, array $customClaims = [], ?int $ttlSeconds = null): string
    {
        $now = time();
        $ttl = $ttlSeconds ?? self::DEFAULT_TTL_SECONDS;

        $payload = array_merge([
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'iat' => $now,
            'exp' => $now + $ttl,
        ], $customClaims);

        // dd($this->secret);

        // $this->secret = "azertyuiopqsdfghjklmwxcvbn2001_SECRETKY";

        return JWT::encode($payload, (string) $this->secret, 'HS256');
    }

    public function decodeToken(string $token): object
    {
        $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));

        return  $decoded;
    }

    public function isTokenValid(string $token): object
    {

        return  $this->decodeToken($token);
    }

    public function extractBearerToken(?string $authorizationHeader): ?string
    {
        if ($authorizationHeader === null) {
            return null;
        }

        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($authorizationHeader, 7));

        return $token !== '' ? $token : null;
    }
}
