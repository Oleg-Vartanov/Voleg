<?php

namespace App\SplitExpense\Service;

use App\SplitExpense\Entity\SeAdjustment;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Enum\SeFeedTypeEnum;
use App\SplitExpense\Http\V1\Feed\SeFeedItem;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\SplitExpense\Repository\SeFeedRepository;
use App\User\Entity\User;
use Doctrine\DBAL\Exception;

readonly class SeFeedService
{
    public function __construct(
        private SeFeedRepository $feedRepository,
        private SeExpenseRepository $expenseRepository,
        private SeAdjustmentRepository $adjustmentRepository,
    ) {
    }

    /**
     * @param SeFeedTypeEnum[] $types
     * @return SeFeedItem[]
     * @throws Exception
     */
    public function listForUser(User $user, array $types, int $offset = 0, int $limit = 100): array
    {
        $rows = $this->feedRepository->find($user, $types, $offset, $limit);

        $idsByType = [];
        foreach ($rows as $row) {
            $idsByType[$row->type->value][] = $row->id;
        }

        $expenses = $this->indexById($this->expenseRepository->findByIdsWithRelations(
            $idsByType[SeFeedTypeEnum::EXPENSE->value] ?? [],
        ));
        $adjustments = $this->indexById($this->adjustmentRepository->findByIdsWithRelations(
            $idsByType[SeFeedTypeEnum::ADJUSTMENT->value] ?? [],
        ));

        $items = [];
        foreach ($rows as $row) {
            // Skips a record deleted between the page query and hydration.
            $item = match ($row->type) {
                SeFeedTypeEnum::EXPENSE => isset($expenses[$row->id])
                    ? new SeFeedItem($row->type, expense: $expenses[$row->id])
                    : null,
                SeFeedTypeEnum::ADJUSTMENT => isset($adjustments[$row->id])
                    ? new SeFeedItem($row->type, adjustment: $adjustments[$row->id])
                    : null,
            };

            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param SeFeedTypeEnum[] $types
     */
    public function countForUser(User $user, array $types): int
    {
        $count = 0;
        foreach ($types as $type) {
            $count += match ($type) {
                SeFeedTypeEnum::EXPENSE => $this->expenseRepository->countForUser($user),
                SeFeedTypeEnum::ADJUSTMENT => $this->adjustmentRepository->countForUser($user),
            };
        }

        return $count;
    }

    /**
     * @template T of SeExpense|SeAdjustment
     *
     * @param T[] $entities
     *
     * @return array<int, T>
     */
    private function indexById(array $entities): array
    {
        $byId = [];
        foreach ($entities as $entity) {
            $byId[(int) $entity->getId()] = $entity;
        }

        return $byId;
    }
}
