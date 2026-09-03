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

    public function __construct(
        private readonly UserService $userService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $password = $this->userService->hashPassword(new User(), self::DEFAULT_PASSWORD);

        $users = [
            ['admin@test.com', 'admin', [RoleEnum::ROLE_ADMIN->value]],
        ];

        foreach (range(1, 1000) as $id) {
            $users[] = ['user'.$id.'@test.com', 'user'.$id, []];
        }

        foreach ($users as [$email, $username, $roles]) {
            $user = new User();
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setUsername($username);
            $user->setRoles($roles);
            $user->setVerified(true);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
