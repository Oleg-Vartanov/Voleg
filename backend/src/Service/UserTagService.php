<?php

namespace App\Service;

use App\Repository\UserRepository;

// TODO: Remove if generate() won't be used after user tag development is done.
readonly class UserTagService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function format(string $tag): string
    {
        return strtolower(str_replace(' ', '-', $tag));
    }

    private function generate(string $displayName): string
    {
        $tag = $this->format($displayName);

        // Add a suffix number.
        if ($this->userRepository->tagExist($tag)) {
            $highestTag = $this->userRepository->findUserTagWithHighestNumber($tag);

            $tag .= $highestTag && preg_match('/(\d+)$/', $highestTag, $matches)
                ? ((int)$matches[1] + 1)
                : '1';
        }

        return $tag;
    }
}