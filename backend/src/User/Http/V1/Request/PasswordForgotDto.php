<?php

namespace App\User\Http\V1\Request;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Password Forgot Dto')]
class PasswordForgotDto
{
    #[Assert\NotBlank, Assert\Email]
    public string $email;
}
