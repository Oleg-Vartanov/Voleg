<?php

namespace App\User\Entity;

use App\FixturePredictions\Entity\FixturePrediction;
use App\User\Enum\RoleEnum;
use App\User\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use InvalidArgumentException;
use LogicException;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

/* OpenAi Documentation */
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
    private const int VERIFICATION_EXPIRATION_TIME = 24 * 60 * 60;

    #[Groups([self::SHOW])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var non-empty-string */
    #[Groups([self::SHOW_ADMIN, self::SHOW_OWNER])]
    #[ORM\Column(length: 180)]
    private string $email;

    #[Groups([self::SHOW_ADMIN, self::SHOW_OWNER])]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $emailChange = null;

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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailChangeCode = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $emailChangeCodeExpireAt = null;

    #[Groups([self::SHOW])]
    #[ORM\Column(options: ['default' => '1970-01-01 00:00:00'])]
    private DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, FixturePrediction>
     */
    #[ORM\OneToMany(targetEntity: FixturePrediction::class, mappedBy: 'user')]
    private Collection $fixturePredictions;

    #[Groups([self::SHOW])]
    #[ORM\Column(length: 255)]
    private string $tag;

    public function __construct()
    {
        $this->updateVerificationCode();
        $this->createdAt = new DateTimeImmutable();
        $this->fixturePredictions = new ArrayCollection();
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

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function updateVerificationCode(): User
    {
        $this->verificationCode = $this->generateVerificationCode();
        $this->verificationCodeExpireAt = $this->createVerificationCodeExpireDate();

        return $this;
    }

    public function getVerificationCodeExpireAt(): DateTimeImmutable
    {
        return $this->verificationCodeExpireAt;
    }

    public function verificationCodeExpired(): bool
    {
        return $this->getVerificationCodeExpireAt() < new DateTimeImmutable();
    }

    public function getEmailChange(): ?string
    {
        return !$this->emailChangeCodeExpired() ? $this->emailChange : null;
    }

    public function setEmailChange(?string $emailChange): static
    {
        $this->emailChange = empty($emailChange) ? null : $emailChange;

        return $this;
    }

    public function updateEmailChangeCode(): static
    {
        $this->emailChangeCode = $this->generateVerificationCode();
        $this->emailChangeCodeExpireAt = $this->createVerificationCodeExpireDate();

        return $this;
    }

    public function getEmailChangeCode(): ?string
    {
        return $this->emailChangeCode;
    }

    public function emailChangeCodeExpired(): bool
    {
        return $this->emailChangeCodeExpireAt === null || $this->emailChangeCodeExpireAt < new DateTimeImmutable();
    }

    public function clearEmailChangeChange(): static
    {
        $this->emailChange = null;
        $this->emailChangeCode = null;
        $this->emailChangeCodeExpireAt = null;

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

    /**
     * @return Collection<int, FixturePrediction>
     */
    public function getFixturePredictions(): Collection
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

    private function generateVerificationCode(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function createVerificationCodeExpireDate(): DateTimeImmutable
    {
        return (new DateTimeImmutable())->modify(
            '+' . self::VERIFICATION_EXPIRATION_TIME . ' seconds'
        );
    }
}
