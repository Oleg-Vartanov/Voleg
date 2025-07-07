<?php

namespace App\User\Entity;

use App\FixturePredictions\Entity\FixturePrediction;
use App\User\DTO\Request\UserDto;
use App\User\Enum\RoleEnum;
use App\User\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\PersistentCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

/* OpenAi Documentation */
#[OA\Schema(title: 'User')]

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(
    name: '`user`',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'UNIQ_USER_EMAIL', fields: ['email']),
        new ORM\UniqueConstraint(name: 'UNIQ_USER_TAG', fields: ['tag']),
    ]
)]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_TAG', fields: ['tag'])]
#[HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const int VERIFICATION_EXPIRATION_TIME = 24 * 60 * 60;

    const string SHOW = 'show';
    const string SHOW_ADMIN = 'show:admin';
    const string SHOW_OWNER = 'show:owner';

    /** @var string[] */
    const array SHOW_ALL = [self::SHOW, self::SHOW_ADMIN, self::SHOW_OWNER];

    #[Groups([self::SHOW])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verificationCode = null;

    #[ORM\Column(options: ['default' => '1970-01-01 00:00:00'])]
    private DateTimeImmutable $verificationCodeExpireAt;

    #[Groups([self::SHOW])]
    #[ORM\Column(options: ['default' => '1970-01-01 00:00:00'])]
    private DateTimeImmutable $createdAt;

    #[ORM\OneToMany(targetEntity: FixturePrediction::class, mappedBy: 'user')]
    /**
     * @var $fixturePredictions FixturePrediction[]|array|PersistentCollection
     */
    private PersistentCollection|array $fixturePredictions;

    #[Groups([self::SHOW])]
    #[ORM\Column(length: 255)]
    private string $tag;

    public function __construct()
    {
        $this->updateVerificationCode();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = RoleEnum::ROLE_USER->value; // guarantee every user at least has ROLE_USER

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRoles(array $roles): bool
    {
        return in_array($roles, $this->getRoles(), true);
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

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(string $verificationCode): static
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    public function updateVerificationCode(): User
    {
        $this->setVerificationCode(bin2hex(random_bytes(16)));
        $this->setVerificationCodeExpireAt(
            (new DateTimeImmutable())->modify(
                '+'.self::VERIFICATION_EXPIRATION_TIME.' seconds'
            )
        );

        return $this;
    }

    public function getVerificationCodeExpireAt(): DateTimeImmutable
    {
        return $this->verificationCodeExpireAt;
    }

    public function setVerificationCodeExpireAt(DateTimeImmutable $value): static {
        $this->verificationCodeExpireAt = $value;

        return $this;
    }

    public function verificationCodeExpired(): bool
    {
        return $this->getVerificationCodeExpireAt() < new DateTimeImmutable();
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

    public function patch(UserDto $dto): self
    {
        if ($dto->isPropertyInitialized('email')) {
            $this->setEmail($dto->email);
        }
        if ($dto->isPropertyInitialized('displayName')) {
            $this->setDisplayName($dto->displayName);
        }
        if ($dto->isPropertyInitialized('tag')) {
            $this->setTag($dto->tag);
        }

        return $this;
    }

    /**
     * @return FixturePrediction[]|array|PersistentCollection
     */
    public function getFixturePredictions(): array|PersistentCollection
    {
        return $this->fixturePredictions;
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
