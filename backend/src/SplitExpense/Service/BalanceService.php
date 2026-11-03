<?php

namespace App\SplitExpense\Service;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Http\V1\Balance\BalanceAmount;
use App\SplitExpense\Http\V1\Balance\BalanceResponse;
use App\SplitExpense\Http\V1\Balance\UserBalance;
use App\SplitExpense\Repository\AdjustmentRepository;
use App\SplitExpense\Repository\ExpenseRepository;
use App\SplitExpense\ValueObject\BalanceEntry;
use App\User\Entity\User;
use App\User\Repository\UserRepository;

readonly class BalanceService
{
    public function __construct(
        private ExpenseRepository $expenseRepository,
        private AdjustmentRepository $adjustmentRepository,
        private UserRepository $userRepository,
        private CurrencyRepository $currencyRepository,
    ) {
    }

    public function forUser(User $user): BalanceResponse
    {
        $entries = $this->netEntries($user);

        if ($entries === []) {
            return new BalanceResponse();
        }

        $users = $this->usersById($entries);
        $currencies = $this->currenciesById($entries);

        $entriesByUserId = [];
        foreach ($entries as $entry) {
            $entriesByUserId[$entry->userId][] = $entry;
        }

        $userBalances = [];
        $totalByCurrencyId = [];

        foreach ($entriesByUserId as $userId => $userEntries) {
            $otherUser = $users[$userId] ?? null;
            if ($otherUser === null) {
                continue;
            }

            $balances = [];
            foreach ($userEntries as $entry) {
                $currency = $currencies[$entry->currencyId] ?? null;
                if ($currency === null) {
                    continue;
                }

                $balances[] = new BalanceAmount($currency, $entry->amount);
                $totalByCurrencyId[$entry->currencyId] =
                    ($totalByCurrencyId[$entry->currencyId] ?? 0) + $entry->amount;
            }

            if ($balances === []) {
                continue;
            }

            $userBalances[] = new UserBalance($otherUser, $this->sortByCurrency($balances));
        }

        usort($userBalances, function (UserBalance $a, UserBalance $b): int {
            return $a->user->getUsername() <=> $b->user->getUsername();
        });

        $totals = [];
        foreach ($totalByCurrencyId as $currencyId => $amount) {
            $currency = $currencies[$currencyId] ?? null;
            if ($currency === null) {
                continue;
            }

            $totals[] = new BalanceAmount($currency, $amount);
        }

        return new BalanceResponse($this->sortByCurrency($totals), $userBalances);
    }

    /**
     * Nets both directions into one signed entry per counterparty and currency. Positive
     * means the counterparty owes the user. Settled pairs are dropped.
     *
     * @return BalanceEntry[]
     */
    private function netEntries(User $user): array
    {
        $amounts = [];

        foreach ($this->expenseRepository->sumSplitsOwedToUser($user) as $entry) {
            $amounts[$entry->userId][$entry->currencyId] =
                ($amounts[$entry->userId][$entry->currencyId] ?? 0) + $entry->amount;
        }

        foreach ($this->expenseRepository->sumSplitsOwedByUser($user) as $entry) {
            $amounts[$entry->userId][$entry->currencyId] =
                ($amounts[$entry->userId][$entry->currencyId] ?? 0) - $entry->amount;
        }

        foreach ($this->adjustmentRepository->sumCreatedByUser($user) as $entry) {
            $amounts[$entry->userId][$entry->currencyId] =
                ($amounts[$entry->userId][$entry->currencyId] ?? 0) + $entry->amount;
        }

        foreach ($this->adjustmentRepository->sumOtherUser($user) as $entry) {
            $amounts[$entry->userId][$entry->currencyId] =
                ($amounts[$entry->userId][$entry->currencyId] ?? 0) - $entry->amount;
        }

        $entries = [];
        foreach ($amounts as $userId => $amountByCurrencyId) {
            foreach ($amountByCurrencyId as $currencyId => $amount) {
                if ($amount === 0) {
                    continue;
                }

                $entries[] = new BalanceEntry($userId, $currencyId, $amount);
            }
        }

        return $entries;
    }

    /**
     * @param BalanceEntry[] $entries
     *
     * @return array<int, User>
     */
    private function usersById(array $entries): array
    {
        $userIds = [];
        foreach ($entries as $entry) {
            $userIds[$entry->userId] = $entry->userId;
        }

        $users = [];
        foreach ($this->userRepository->findBy(['id' => array_values($userIds)]) as $user) {
            $users[(int) $user->getId()] = $user;
        }

        return $users;
    }

    /**
     * @param BalanceEntry[] $entries
     *
     * @return array<int, Currency>
     */
    private function currenciesById(array $entries): array
    {
        $currencyIds = [];
        foreach ($entries as $entry) {
            $currencyIds[$entry->currencyId] = $entry->currencyId;
        }

        $currencies = [];
        foreach ($this->currencyRepository->findBy(['id' => array_values($currencyIds)]) as $currency) {
            $currencies[(int) $currency->getId()] = $currency;
        }

        return $currencies;
    }

    /**
     * @param BalanceAmount[] $amounts
     *
     * @return BalanceAmount[]
     */
    private function sortByCurrency(array $amounts): array
    {
        usort($amounts, function (BalanceAmount $a, BalanceAmount $b): int {
            return $a->currency->getCode() <=> $b->currency->getCode();
        });

        return $amounts;
    }
}
