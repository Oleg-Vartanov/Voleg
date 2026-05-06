<?php

namespace App\User\Http\V1\Request;

use App\Core\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Password Reset Dto')]
class PasswordResetDto
{
    #[Assert\NotBlank]
    public string $selector;

    #[Assert\NotBlank]
    public string $token;

    #[Assert\NotBlank, CustomAssert\StrongPassword]
    public string $password;
}
