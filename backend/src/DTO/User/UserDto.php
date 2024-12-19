<?php

namespace App\DTO\User;

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

    #[Groups([self::SIGN_UP, self::UPDATE])]
    #[OA\Property(example: 'name@mail.com')]
    #[
        Assert\NotBlank(groups: [self::SIGN_UP]),
        Assert\Type('string', groups: [self::SIGN_UP, self::UPDATE]),
        Assert\Email(groups: [self::SIGN_UP, self::UPDATE]),
        Assert\Length(max: 180, groups: [self::SIGN_UP, self::UPDATE]),
        CustomAssert\InitializedAndNotNull(groups: [self::UPDATE]),
        CustomAssert\UniqueEntityField(entityClass: User::class, field: 'email', groups: [self::SIGN_UP, self::UPDATE]),
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

    #[Groups([self::SIGN_UP, self::UPDATE])]
    #[OA\Property(example: 'Cool Name')]
    #[
        Assert\NotBlank(groups: [self::SIGN_UP]),
        CustomAssert\InitializedAndNotNull(groups: [self::UPDATE]),
        Assert\Length(min: 1, groups: [self::SIGN_UP, self::UPDATE]),
        Assert\Type('string', groups: [self::SIGN_UP, self::UPDATE]),
        Assert\Length(max: 255, groups: [self::SIGN_UP, self::UPDATE]),
    ]
    public string $displayName;
}