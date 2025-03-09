<?php

namespace App\DTO\User\Request;

use App\Entity\User;
use App\Interface\PropertyAccessorInterface;
use App\Trait\PropertyAccessor;
use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class UserDto implements PropertyAccessorInterface
{
    use PropertyAccessor;

    const SIGN_UP = 'SignUp';
    const UPDATE = 'Update';
    const ALL = [self::SIGN_UP, self::UPDATE];

    #[Groups(self::ALL)]
    #[OA\Property(example: 'name@mail.com')]
    #[
        Assert\NotBlank(groups: [self::SIGN_UP]),
        Assert\Type('string', groups: self::ALL),
        Assert\Email(groups: self::ALL),
        Assert\Length(max: 180, groups: self::ALL),
        CustomAssert\InitializedAndNotNull(groups: [self::UPDATE]),
        CustomAssert\UniqueEntityField(entityClass: User::class, field: 'email', groups: self::ALL),
    ]
    public string $email;

    #[Groups([self::SIGN_UP])]
    #[OA\Property(example: 'StrongPassword!1')]
    #[
        Assert\NotBlank(groups: [self::SIGN_UP]),
        Assert\Type('string', groups: [self::SIGN_UP]),
        Assert\Length(min: 6, groups: [self::SIGN_UP]),
        Assert\Regex(pattern: '/^\S+$/', message: 'The value can\'t contain spaces.', groups: [self::SIGN_UP]),
        Assert\Regex(pattern: '/\d+/i', message: 'Should have at least one digit.', groups: [self::SIGN_UP]),
        Assert\Regex(pattern: '/[#?!@$%^&*-]+/i', message: 'Should have at least one character from [#?!@$%^&*-].', groups: [self::SIGN_UP]),
        Assert\Regex(pattern: '/[A-Z]+/', message: 'Should have at least one upper case character.', groups: [self::SIGN_UP])
    ]
    public string $password;

    #[Groups(self::ALL)]
    #[OA\Property(example: 'Cool Name')]
    #[
        Assert\NotBlank(groups: [self::SIGN_UP]),
        CustomAssert\InitializedAndNotNull(groups: [self::UPDATE]),
        Assert\Type('string', groups: self::ALL),
        Assert\Length(min: 1, groups: self::ALL),
        Assert\Length(max: 255, groups: self::ALL),
    ]
    public string $displayName;

    #[Groups(self::ALL)]
    #[OA\Property(example: 'cool-name')]
    #[
        Assert\NotBlank(groups: [self::SIGN_UP]),
        CustomAssert\InitializedAndNotNull(groups: [self::UPDATE]),
        Assert\Type('string', groups: self::ALL),
        Assert\Length(min: 1, groups: self::ALL),
        Assert\Length(max: 255, groups: self::ALL),
        Assert\Regex(pattern: '/^\S+$/', message: 'The value can\'t contain spaces.', groups: self::ALL),
        Assert\Regex(pattern: '/^[^A-Z]*$/', message: 'All the letters must be lowercase.', groups: self::ALL),
        CustomAssert\UniqueEntityField(entityClass: User::class, field: 'tag', groups: self::ALL),
    ]
    public string $tag;
}