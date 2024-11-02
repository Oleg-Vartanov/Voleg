<?php

namespace App\DTO;

use App\Attribute\Roles;
use App\Entity\User;
use App\Trait\Arrayable;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: 'User')]
class UserDto
{
    use Arrayable;

    #[Groups(['GET'])]
    public mixed $id;

    #[Roles(['ROLE_ADMIN', 'OWNER'])]
    #[Groups(['GET'])]
    public mixed $email;

    #[Groups(['GET', 'PATCH'])]
    #[Assert\NotBlank, Assert\Type('string'), Assert\Length(max: 255)]
    public mixed $displayName;

    #[Groups(['GET'])]
    public mixed $createdAt;

    public static function createByUser(User $user): self
    {
        $dto = new self();
        $dto->id = $user->getId();
        $dto->email = $user->getEmail();
        $dto->displayName = $user->getDisplayName();
        $dto->createdAt = $user->getCreatedAt();

        return $dto;
    }
}