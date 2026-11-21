<?php

namespace App\User\Http\V1\Auth;

use App\Core\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Password Reset Request Payload')]
class PasswordResetRequestPayload
{
    #[Assert\NotBlank]
    public string $token;

    #[Assert\NotBlank, CustomAssert\Password]
    public string $password;
}
