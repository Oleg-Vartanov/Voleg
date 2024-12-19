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

    #[Assert\NotBlank, Assert\Type('digit'), Assert\Positive]
    public int $userId;

    #[Assert\NotBlank, Assert\Type('string')]
    public string $code;

    #[OA\Property(example: 'https://www.google.com')]
    #[Assert\Type('string'), Assert\Url]
    public ?string $redirectUrl = null;
}