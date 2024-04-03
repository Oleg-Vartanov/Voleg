<?php

namespace App\DTO\Auth;

use App\Entity\User;
use App\Trait\Arrayable;
use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    use Arrayable;

    #[Assert\NotBlank, Assert\Type('string'), Assert\Email, Assert\Length(max: 180),
        Assert\UniqueEntity(entityClass: User::class, fields: ['email'])]
    public mixed $email = '';

    #[Assert\NotBlank, Assert\Type('string'), Assert\Length(min: 6),
        Assert\Regex(pattern: '/\d+/i', message: 'Should have at least one digit.'),
        Assert\Regex(pattern: '/[#?!@$%^&*-]+/i', message: 'Should have at least one character from [#?!@$%^&*-].'),
        Assert\Regex(pattern: '/[A-Z]+/', message: 'Should have at least one upper case character.')]
    public mixed $password = '';

    #[Assert\NotBlank, Assert\Type('string'), Assert\Length(max: 255)]
    public mixed $displayName = '';
}