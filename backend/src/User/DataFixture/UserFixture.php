<?php

namespace App\User\DataFixture;

use App\User\Entity\User;
use App\User\Enum\RoleEnum;
use App\User\Service\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public const string DEFAULT_PASSWORD = 'Qwerty1!';

    public const string REF_ADMIN = 'user.admin';
    public const string REF_USER = 'user.default';

    public static function refUser(int $n): string
    {
        return "user.{$n}";
    }

    public static function username(int $n): string
    {
        return "user{$n}";
    }

    public function __construct(
        private readonly UserService $userService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $password = $this->userService->hashPassword(
            new User(),
            self::DEFAULT_PASSWORD,
        );

        $namedUsers = [
            [self::REF_ADMIN, 'admin@test.com', 'admin', [RoleEnum::ROLE_ADMIN->value]],
            [self::REF_USER, 'user@test.com', 'user', [RoleEnum::ROLE_USER->value]],
        ];
        foreach (range(1, 10) as $i) {
            $namedUsers[] = [
                self::refUser($i),
                self::username($i) . '@test.com',
                self::username($i),
                [RoleEnum::ROLE_USER->value],
            ];
        }

        foreach ($namedUsers as [$reference, $email, $username, $roles]) {
            $manager->persist(
                $this->createUser($password, $email, $username, $roles, $reference),
            );
        }

        foreach (range(11, 100) as $i) {
            $manager->persist(
                $this->createUser(
                    $password,
                    self::username($i) . '@test.com',
                    self::username($i),
                    [RoleEnum::ROLE_USER->value],
                ),
            );
        }

        $manager->flush();
    }

    /**
     * @param string[] $roles
     */
    private function createUser(
        string $password,
        string $email,
        string $username,
        array $roles,
        ?string $reference = null,
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setUsername($username);
        $user->setRoles($roles);
        $user->setVerified(true);

        if ($reference !== null) {
            $this->addReference($reference, $user);
        }

        return $user;
    }
}
