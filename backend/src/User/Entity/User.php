<?php

namespace App\User\Entity;

use App\User\Enum\RoleEnum;
use App\User\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use InvalidArgumentException;
use LogicException;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[OA\Schema(title: 'User')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_TAG', fields: ['tag'])]
#[HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const string SHOW = 'show';
    public const string SHOW_ADMIN = 'show:admin';
    public const string SHOW_OWNER = 'show:owner';
    /** @var string[] */
    public const array SHOW_ALL = [self::SHOW, self::SHOW_ADMIN, self::SHOW_OWNER];

    #[Groups([self::SHOW])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var non-empty-string */
    #[Groups([self::SHOW_ADMIN, self::SHOW_OWNER])]
    #[ORM\Column(length: 180)]
    private string $email;

    /** @var string[] The user roles */
    #[ORM\Column]
    private array $roles = [];

    /** @var string The hashed password */
    #[ORM\Column]
    private string $password;

    #[Groups([self::SHOW])]
    #[ORM\Column(length: 255)]
    private string $displayName;

    #[ORM\Column]
    private bool $verified = false;

    #[Groups([self::SHOW])]
    #[ORM\Column(options: ['default' => '1970-01-01 00:00:00'])]
    private DateTimeImmutable $createdAt;

    #[Groups([self::SHOW])]
    #[ORM\Column(length: 255)]
    private string $tag;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = !empty($email) ? $email : throw new InvalidArgumentException();

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @return non-empty-string
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) ($this->id ?? throw new LogicException('User ID is not initialized.'));
    }

    /**
     * @return non-empty-array<string>
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = RoleEnum::ROLE_USER->value; // guarantee every user at least has ROLE_USER

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = trim($displayName);

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /** Triggered only in initial persist. */
    public function prePersist(): void
    {
        $this->setCreatedAt(new DateTimeImmutable());
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = trim($tag);

        return $this;
    }
}
