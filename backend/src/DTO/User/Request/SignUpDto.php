<?php

namespace App\DTO\User\Request;

use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'User Sign Up')]
class SignUpDto extends UserDto
{
    #[Groups([self::SIGN_UP])]
    #[OA\Property(example: 'https://www.google.com')]
    #[Assert\Type('string', groups: [self::SIGN_UP]), Assert\Url(groups: [self::SIGN_UP])]
    public ?string $verificationEmailRedirectUrl = null;

    #[Groups([self::SIGN_UP])]
    #[OA\Property(example: 'super-duper-code')]
    #[Assert\NotBlank,
        Assert\Type('string', groups: [self::SIGN_UP]),
        CustomAssert\EqualParamConfig('auth.sign.up.code', groups: [self::SIGN_UP])]
    public string $code;
}