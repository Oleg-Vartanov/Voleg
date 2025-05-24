<?php

namespace App\User\DTO\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Verification Link')]
class VerificationLinkDto
{
    #[Assert\NotBlank, Assert\Positive]
    public int $userId;

    #[Assert\NotBlank]
    public string $code;

    #[OA\Property(example: 'https://www.google.com')]
    #[Assert\Type('string'), Assert\Url]
    public ?string $redirectUrl = null;
}