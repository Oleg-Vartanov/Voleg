<?php

namespace App\DTO\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Verification Link')]
#[Groups([self::DOCUMENTATION])]
class VerificationLinkDto
{
    const DOCUMENTATION = 'documentation';

    #[OA\Property(type: 'integer')]
    #[Assert\NotBlank, Assert\Type('digit'), Assert\Positive]
    public mixed $userId;

    #[OA\Property(type: 'string')]
    #[Assert\NotBlank, Assert\Type('string')]
    public mixed $code;

    #[OA\Property(type: 'string', example: 'https://www.google.com')]
    #[Assert\Type('string'), Assert\Url]
    public mixed $redirectUrl;
}