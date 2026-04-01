<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'user_type', type: 'string')]
#[ORM\DiscriminatorMap([
    'user' => User::class,
    'mom' => Mom::class,
    'employer' => Employer::class,
    'childcare' => ChildCare::class,
    'admin' => Admin::class,
])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email = '';

    #[ORM\Column(length: 180)]
    private string $name = '';

    #[ORM\Column]
    private string $password = '';

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    public function __construct(string $email = '', string $password = '', array $roles = ['ROLE_USER'], ?string $name = '')
    {
        $this->email = $email;
        $this->password = $password;
        $this->roles = $this->normalizeRoles($roles);
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->normalizeRoles($this->roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $this->normalizeRoles($roles);

        return $this;
    }

    private function normalizeRoles(array $roles): array
    {
        $normalized = array_values(array_unique(array_map(
            static fn(mixed $role): string => (string) $role,
            $roles
        )));

        if (!in_array('ROLE_USER', $normalized, true)) {
            $normalized[] = 'ROLE_USER';
        }

        return $normalized;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }
}
