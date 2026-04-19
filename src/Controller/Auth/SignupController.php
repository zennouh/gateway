<?php

namespace App\Controller\Auth;

use App\Entity\Admin;
use App\Entity\ChildCare;
use App\Entity\Employer;
use App\Entity\Mom;
use App\Entity\User;
use App\Services\AuthService;
use App\Services\GatewayService;
use App\Test\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/auth', name: 'api_auth_')]
class SignupController extends AbstractController
{
    private const ROLE_BY_TYPE = [
        'mom' => 'ROLE_MOM',
        'employer' => 'ROLE_EMPLOYER',
        'childcare' => 'ROLE_CHILDCARE',
        'admin' => 'ROLE_ADMIN',
    ];

    public function __construct(
        private AuthService $authService,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private string $uploadsAvatarDir,
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, UploadService $uploadService): JsonResponse
    {
        try {


            $data = $request->request->all();

            if ($data === []) {
                $data = json_decode($request->getContent(), true);
            }

            if (!is_array($data)) {
                return $this->json(
                    ['error' => 'Invalid JSON payload'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if (empty($data['email']) || empty($data['password']) || empty($data['userType'])) {
                return $this->json(
                    ['error' => 'Missing email, password, or userType'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $userType = strtolower((string) $data['userType']);

            try {
                $user = $this->buildUserByType($userType, $data);
            } catch (\InvalidArgumentException $exception) {
                return $this->json(
                    ['error' => $exception->getMessage()],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $user->setEmail((string) $data['email']);
            $user->setRoles($this->resolveRolesForType($userType));

            $hashedPassword = $this->authService->hashPassword($user, (string) $data['password']);
            $user->setPassword($hashedPassword);
            $user->setName((string) ($data['name'] ?? ''));

            $file = $request->files->get('avatar');

            if (!$file) {
                throw new Exception("Avatar file is missing in the request");
            }

            // dd($file);

            if ($file && !$file->isValid()) {
                return $this->json(
                    ['error' => 'Invalid avatar file upload'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            try {
                $filename =  $uploadService->uploadImage($file);
            } catch (Exception $e) {
                $filename = null;
            }

            $user->setAvatar($filename ?? "/uploads/avatars/profile.png");

            $this->entityManager->persist($user);
            $this->entityManager->flush();



            return $this->json([
                'message' => 'User registered successfully',
                'userType' => $userType,
                'roles' => $user->getRoles(),
                'avatar' => $user->getAvatar(),
                // 'token' => $token,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            // dd($e->getMessage(), $e->getTraceAsString());
            return $this->json(
                ['error' => 'Failed: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }



    private function resolveRolesForType(string $userType): array
    {
        if (!isset(self::ROLE_BY_TYPE[$userType])) {
            throw new \InvalidArgumentException('Invalid userType. Allowed values: mom, employer, childcare, admin');
        }

        return ['ROLE_USER', self::ROLE_BY_TYPE[$userType]];
    }

    private function buildUserByType(string $userType, array $data): User
    {
        return match ($userType) {
            'mom' => $this->buildMom($data),
            'employer' => $this->buildEmployer($data),
            'childcare' => $this->buildChildcare($data),
            'admin' => $this->buildAdmin($data),
            default => throw new \InvalidArgumentException('Invalid userType. Allowed values: mom, employer, childcare, admin'),
        };
    }

    private function buildMom(array $data): Mom
    {
        if (!isset($data['numberOfChildren'], $data['address'])) {
            throw new \InvalidArgumentException('Mom requires: numberOfChildren, address');
        }

        $mom = new Mom();
        $mom->setNumberOfChildren((int) $data['numberOfChildren']);
        $mom->setChildrenCount((int) ($data['childrenCount'] ?? $data['numberOfChildren']));
        $mom->setAddress((string) $data['address']);

        return $mom;
    }

    private function buildEmployer(array $data): Employer
    {
        if (!isset($data['location'], $data['description'])) {
            throw new \InvalidArgumentException('Employer requires: location, description');
        }

        $employer = new Employer();
        $employer->setLocation((string) $data['location']);
        $employer->setDescription((string) $data['description']);

        return $employer;
    }

    private function buildChildcare(array $data): ChildCare
    {
        if (!isset($data['capacity'], $data['degree'], $data['pricePerHour'])) {
            throw new \InvalidArgumentException('Childcare requires: capacity, degree, pricePerHour');
        }

        $childcare = new ChildCare();
        $childcare->setCapacity((int) $data['capacity']);
        $childcare->setDegree((string) $data['degree']);
        $childcare->setPricePerHour((float) $data['pricePerHour']);

        return $childcare;
    }

    private function buildAdmin(array $data): Admin
    {
        // if (!isset($data['department'])) {
        //     throw new \InvalidArgumentException('Admin requires: department');
        // }

        $admin = new Admin();
        $admin->setIsActive(true);
        // $admin->setDepartment((string) $data['department']);

        return $admin;
    }
}
