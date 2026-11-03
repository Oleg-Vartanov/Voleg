<?php

namespace App\SplitExpense\Service;

use App\SplitExpense\Entity\Adjustment;
use App\SplitExpense\Entity\Expense;
use App\SplitExpense\Enum\FeedTypeEnum;
use App\SplitExpense\Http\V1\Feed\FeedItem;
use App\SplitExpense\Repository\AdjustmentRepository;
use App\SplitExpense\Repository\ExpenseRepository;
use App\SplitExpense\Repository\FeedRepository;
use App\User\Entity\User;
use Doctrine\DBAL\Exception;

readonly class FeedService
{
    public function __construct(
        private FeedRepository $feedRepository,
        private ExpenseRepository $expenseRepository,
        private AdjustmentRepository $adjustmentRepository,
    ) {
    }

    /**
     * @param FeedTypeEnum[] $types
     * @return FeedItem[]
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
            $idsByType[FeedTypeEnum::EXPENSE->value] ?? [],
        ));
        $adjustments = $this->indexById($this->adjustmentRepository->findByIdsWithRelations(
            $idsByType[FeedTypeEnum::ADJUSTMENT->value] ?? [],
        ));

        $items = [];
        foreach ($rows as $row) {
            // Skips a record deleted between the page query and hydration.
            $item = match ($row->type) {
                FeedTypeEnum::EXPENSE => isset($expenses[$row->id])
                    ? new FeedItem($row->type, expense: $expenses[$row->id])
                    : null,
                FeedTypeEnum::ADJUSTMENT => isset($adjustments[$row->id])
                    ? new FeedItem($row->type, adjustment: $adjustments[$row->id])
                    : null,
            };

            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param FeedTypeEnum[] $types
     */
    public function countForUser(User $user, array $types): int
    {
        $count = 0;
        foreach ($types as $type) {
            $count += match ($type) {
                FeedTypeEnum::EXPENSE => $this->expenseRepository->countForUser($user),
                FeedTypeEnum::ADJUSTMENT => $this->adjustmentRepository->countForUser($user),
            };
        }

        return $count;
    }

    /**
     * @template T of Expense|Adjustment
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
