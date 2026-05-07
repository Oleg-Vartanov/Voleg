<?php

namespace App\User\DataFixture;

use App\User\Entity\User;
use App\User\Enum\RoleEnum;
use App\User\Service\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public const string DEFAULT_PASSWORD = '!Qwerty1';

    public function __construct(
        private readonly UserService $userService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'email' => 'admin@admin.com',
                'password' => 'admin',
                'displayName' => 'Admin',
                'tag' => 'admin',
                'roles' => [RoleEnum::ROLE_ADMIN->value],
                'verified' => true,
                'reference' => 'admin',
            ],
            [
                'email' => 'user@user.com',
                'password' => 'user',
                'displayName' => 'User',
                'tag' => 'user',
                'roles' => [RoleEnum::ROLE_USER->value],
                'verified' => true,
                'reference' => 'user',
            ],
        ];

        foreach ($users as $user) {
            $u = new User();
            $u->setEmail($user['email']);
            $this->userService->setHashedPassword($u, $user['password']);
            $u->setDisplayName($user['displayName']);
            $u->setTag($user['tag']);
            $u->setRoles($user['roles']);
            $u->setVerified($user['verified']);

            $manager->persist($u);

            $this->addReference($user['reference'], $u);
        }

        $manager->flush();
    }
}
