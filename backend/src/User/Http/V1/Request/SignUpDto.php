<?php

namespace App\User\Http\V1\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'User Sign Up')]
class SignUpDto extends UserDto
{
}
