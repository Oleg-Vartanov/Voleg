<?php

namespace App\User\Controller\Trait;

use App\User\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait UserControllerTrait
{
    protected function checkModifyAccess(int $id): void
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isOwner($id)) {
            throw new AccessDeniedHttpException();
        }
    }

    protected function isOwner(int $userId): bool
    {
        return $this->getUser()?->getId() === $userId;
    }

    protected function showGroups(?int $userIdToShow = null): array
    {
        $groups = [User::SHOW];
        if ($this->isGranted('ROLE_ADMIN')) {
            $groups[] = User::SHOW_ADMIN;
        }
        if (!is_null($userIdToShow) && $this->isOwner($userIdToShow)) {
            $groups[] = User::SHOW_OWNER;
        }

        return $groups;
    }
}