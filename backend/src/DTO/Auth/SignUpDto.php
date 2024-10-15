<?php

namespace App\DTO\Auth;

use App\Entity\User;
use App\Validator\Constraints as CustomAssert;
use App\Trait\Arrayable;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Sign Up')]
class SignUpDto
{
    use Arrayable;

    #[Groups(['documentation'])]
    #[Assert\NotBlank, Assert\Type('string'), Assert\Email, Assert\Length(max: 180),
        CustomAssert\UniqueEntityField(entityClass: User::class, field: 'email')]
    #[OA\Property(description: 'User\'s unique identifier.', type: 'string')]
    public mixed $email;

    #[Groups(['documentation'])]
    #[Assert\NotBlank, Assert\Type('string'), Assert\Length(min: 6),
        Assert\Regex(pattern: '/^\S+$/', message: 'The value can\'t contain spaces.'),
        Assert\Regex(pattern: '/\d+/i', message: 'Should have at least one digit.'),
        Assert\Regex(pattern: '/[#?!@$%^&*-]+/i', message: 'Should have at least one character from [#?!@$%^&*-].'),
        Assert\Regex(pattern: '/[A-Z]+/', message: 'Should have at least one upper case character.')]
    #[OA\Property(type: 'string')]
    public mixed $password;

    #[Groups(['documentation'])]
    #[Assert\NotBlank, Assert\Type('string'), Assert\Length(max: 255)]
    #[OA\Property(type: 'string')]
    public mixed $displayName;

    #[Groups(['documentation'])]
    #[Assert\Type('string'), Assert\Url]
    #[OA\Property(type: 'string')]
    public mixed $verificationEmailRedirectUrl;
}