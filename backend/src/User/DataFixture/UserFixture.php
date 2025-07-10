<?php

namespace App\User\DataFixture;

use App\User\Entity\User;
use App\User\Enum\RoleEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
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
                'email' => 'user@admin.com',
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
            $u->setPassword(
                $this->passwordHasher->hashPassword($u, $user['password'])
            );
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
