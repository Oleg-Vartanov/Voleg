<?php

namespace App\SplitExpense\Repository;

use App\SplitExpense\Enum\SeFeedTypeEnum;
use App\SplitExpense\ValueObject\SeFeedRow;
use App\User\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;

/**
 * Expenses and adjustments live in separate tables, so the combined, date-ordered page
 * is selected with a native UNION (not expressible in DQL) and hydrated by the caller.
 */
readonly class SeFeedRepository
{
    private const array SELECTS = [
        SeFeedTypeEnum::EXPENSE->value => <<<'SQL'
            SELECT 'expense' AS type, e.id, e.expense_date AS record_date
            FROM se_expense e
            WHERE e.paid_by_user_id = :user
               OR EXISTS (SELECT 1 FROM se_expense_split s WHERE s.expense_id = e.id AND s.user_id = :user)
            SQL,
        SeFeedTypeEnum::ADJUSTMENT->value => <<<'SQL'
            SELECT 'adjustment' AS type, a.id, a.adjustment_date AS record_date
            FROM se_adjustment a
            WHERE a.created_by_user_id = :user OR a.other_user_id = :user
            SQL,
    ];

    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * Newest first; on equal dates adjustments go before expenses.
     *
     * @param SeFeedTypeEnum[] $types
     *
     * @return SeFeedRow[]
     * @throws Exception
     */
    public function find(User $user, array $types, int $offset = 0, int $limit = 100): array
    {
        $selects = [];
        foreach ($types as $type) {
            $selects[$type->value] = '(' . self::SELECTS[$type->value] . ')';
        }

        if ($selects === []) {
            return [];
        }

        $sql = implode(' UNION ALL ', $selects)
            . ' ORDER BY record_date DESC, type ASC, id DESC LIMIT :limit OFFSET :offset';

        /** @var list<array{type: string, id: int|string}> $rows */
        $rows = $this->connection->fetchAllAssociative(
            $sql,
            ['user' => $user->getId(), 'limit' => $limit, 'offset' => $offset],
            ['user' => ParameterType::INTEGER, 'limit' => ParameterType::INTEGER, 'offset' => ParameterType::INTEGER],
        );

        $result = [];
        foreach ($rows as $row) {
            $result[] = new SeFeedRow(
                type: SeFeedTypeEnum::from($row['type']),
                id: (int) $row['id'],
            );
        }

        return $result;
    }
}
