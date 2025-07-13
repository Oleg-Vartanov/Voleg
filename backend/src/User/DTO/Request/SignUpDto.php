<?php

namespace App\User\DTO\Request;

use App\Core\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'User Sign Up')]
class SignUpDto extends UserDto
{
    #[Groups([self::SIGN_UP])]
    #[OA\Property(example: 'https://example.com')]
    #[Assert\Type('string', groups: [self::SIGN_UP]), Assert\Url(groups: [self::SIGN_UP])]
    public ?string $verificationEmailRedirectUrl = null;

    #[Groups([self::SIGN_UP])]
    #[OA\Property(example: 'super-duper-code')]
    #[Assert\NotBlank(groups: [self::SIGN_UP]),
        Assert\Type('string', groups: [self::SIGN_UP]),
        CustomAssert\EqualParamConfig('auth.sign.up.code', groups: [self::SIGN_UP])]
    public string $code;
}