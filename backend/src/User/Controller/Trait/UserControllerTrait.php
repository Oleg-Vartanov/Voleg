<?php

namespace App\User\Controller\Trait;

use App\User\Entity\User;
use App\User\Enum\RoleEnum;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait UserControllerTrait
{
    protected function checkModifyAccess(int $id): void
    {
        if (!$this->isGranted(RoleEnum::ROLE_ADMIN->value) && !$this->isOwner($id)) {
            throw new AccessDeniedHttpException();
        }
    }

    protected function isOwner(int $userId): bool
    {
        /** @var User|null $user */
        $user = $this->getUser();

        return $user?->getId() === $userId;
    }

    /**
     * @return string[]
     */
    protected function showGroups(?int $userIdToShow = null): array
    {
        $groups = [User::SHOW];
        if ($this->isGranted(RoleEnum::ROLE_ADMIN->value)) {
            $groups[] = User::SHOW_ADMIN;
        }
        if (!is_null($userIdToShow) && $this->isOwner($userIdToShow)) {
            $groups[] = User::SHOW_OWNER;
        }

        return $groups;
    }
}
