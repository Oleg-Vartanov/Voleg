<?php

namespace App\User\Http\V1\Request;

use App\Core\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'Password Change Dto')]
class PasswordChangeDto
{
    #[Assert\NotBlank]
    public string $currentPassword;

    #[Assert\NotBlank, CustomAssert\StrongPassword]
    public string $newPassword;
}
