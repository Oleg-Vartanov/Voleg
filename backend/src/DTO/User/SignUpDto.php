<?php

namespace App\DTO\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'User Sign Up')]
class SignUpDto extends UserDto
{
    #[Groups([self::SIGN_UP])]
    #[Assert\Type('string', groups: [self::SIGN_UP]), Assert\Url(groups: [self::SIGN_UP])]
    #[OA\Property(type: 'string', example: 'https://www.google.com')]
    public mixed $verificationEmailRedirectUrl;
}