<?php

namespace App\SplitExpense\Service;

use App\Core\Entity\Currency;
use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Http\V1\Balance\SeBalanceAmount;
use App\SplitExpense\Http\V1\Balance\SeBalanceResponse;
use App\SplitExpense\Http\V1\Balance\SeUserBalance;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\SplitExpense\ValueObject\SeBalanceEntry;
use App\User\Entity\User;
use App\User\Repository\UserRepository;

/**
 * Balances are derived from the splits on every read, never stored. The splits are the
 * ledger, so an edited or deleted expense can never leave a stale balance behind.
 */
readonly class SeBalanceService
{
    public function __construct(
        private SeExpenseRepository $expenseRepository,
        private UserRepository $userRepository,
        private CurrencyRepository $currencyRepository,
    ) {
    }

    public function forUser(User $user): SeBalanceResponse
    {
        $entries = $this->netEntries($user);

        if ($entries === []) {
            return new SeBalanceResponse();
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

                $balances[] = new SeBalanceAmount($currency, $entry->amount);
                $totalByCurrencyId[$entry->currencyId] =
                    ($totalByCurrencyId[$entry->currencyId] ?? 0) + $entry->amount;
            }

            if ($balances === []) {
                continue;
            }

            $userBalances[] = new SeUserBalance($otherUser, $this->sortByCurrency($balances));
        }

        usort($userBalances, function (SeUserBalance $a, SeUserBalance $b): int {
            return $a->user->getUsername() <=> $b->user->getUsername();
        });

        $totals = [];
        foreach ($totalByCurrencyId as $currencyId => $amount) {
            $currency = $currencies[$currencyId] ?? null;
            if ($currency === null) {
                continue;
            }

            $totals[] = new SeBalanceAmount($currency, $amount);
        }

        return new SeBalanceResponse($this->sortByCurrency($totals), $userBalances);
    }

    /**
     * Nets both directions into one signed entry per counterparty and currency. Positive
     * means the counterparty owes the user. Settled pairs are dropped.
     *
     * @return SeBalanceEntry[]
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

        $entries = [];
        foreach ($amounts as $userId => $amountByCurrencyId) {
            foreach ($amountByCurrencyId as $currencyId => $amount) {
                if ($amount === 0) {
                    continue;
                }

                $entries[] = new SeBalanceEntry($userId, $currencyId, $amount);
            }
        }

        return $entries;
    }

    /**
     * @param SeBalanceEntry[] $entries
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
     * @param SeBalanceEntry[] $entries
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
     * @param SeBalanceAmount[] $amounts
     *
     * @return SeBalanceAmount[]
     */
    private function sortByCurrency(array $amounts): array
    {
        usort($amounts, function (SeBalanceAmount $a, SeBalanceAmount $b): int {
            return $a->currency->getCode() <=> $b->currency->getCode();
        });

        return $amounts;
    }
}
