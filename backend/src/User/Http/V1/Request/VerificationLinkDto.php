<?php

namespace App\User\Http\V1\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'Verification Link')]
class VerificationLinkDto
{
    #[Assert\NotBlank, Assert\Positive]
    public int $userId;

    #[Assert\NotBlank]
    public string $code;
}
