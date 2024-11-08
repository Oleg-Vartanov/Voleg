<?php

namespace App\DTO\User;

use App\Entity\User;
use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    const SIGN_UP = 'sign-up';
    const UPDATE_ADMIN = 'update:admin';
    const UPDATE_OWNER = 'update:owner';
    const UPDATE_ALL = [self::UPDATE_ADMIN, self::UPDATE_OWNER];
    
    #[Groups([self::SIGN_UP, self::UPDATE_ADMIN])]
    #[
        Assert\NotBlank(groups: [self::SIGN_UP]),
        Assert\Type('string'),
        Assert\Email,
        Assert\Length(max: 180),
        CustomAssert\UniqueEntityField(entityClass: User::class, field: 'email')
    ]
    #[OA\Property(description: 'User\'s unique identifier.', type: 'string', example: 'name@mail.com')]
    public mixed $email;

    #[Groups([self::SIGN_UP])]
    #[
        Assert\NotBlank,
        Assert\Type('string'),
        Assert\Length(min: 6),
        Assert\Regex(pattern: '/^\S+$/', message: 'The value can\'t contain spaces.'),
        Assert\Regex(pattern: '/\d+/i', message: 'Should have at least one digit.'),
        Assert\Regex(pattern: '/[#?!@$%^&*-]+/i', message: 'Should have at least one character from [#?!@$%^&*-].'),
        Assert\Regex(pattern: '/[A-Z]+/', message: 'Should have at least one upper case character.')
    ]
    #[OA\Property(type: 'string', example: 'StrongPassword!1')]
    public mixed $password;

    #[Groups([self::SIGN_UP, self::UPDATE_ADMIN, self::UPDATE_OWNER])]
    #[
        Assert\NotBlank(groups: [self::SIGN_UP]),
        Assert\Length(min: 1),
        Assert\Type('string'),
        Assert\Length(max: 255)
    ]
    #[OA\Property(type: 'string', example: 'Cool Name')]
    public mixed $displayName;
}