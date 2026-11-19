<?php

namespace App\User\Http\V1\Auth;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Password Forgot Request Payload')]
class PasswordForgotRequestPayload
{
    #[Assert\NotBlank, Assert\Email]
    public string $email;
}
