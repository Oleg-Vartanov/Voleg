<?php

namespace App\SplitExpense\Service;

use App\Core\Entity\Currency;
use App\Core\Exception\UnprocessableException;
use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Entity\SeCategory;
use App\SplitExpense\Http\V1\Insights\SeCategoryTotal;
use App\SplitExpense\Http\V1\Insights\SeInsightsResponse;
use App\SplitExpense\Repository\SeCategoryRepository;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\SplitExpense\ValueObject\SeCategoryTotalEntry;
use App\User\Entity\User;
use App\User\Repository\UserRepository;

readonly class SeInsightsService
{
    public function __construct(
        private SeExpenseRepository $expenseRepository,
        private SeCategoryRepository $categoryRepository,
        private CurrencyRepository $currencyRepository,
        private UserRepository $userRepository,
        private SeConnectionService $connectionService,
    ) {
    }

    /**
     * Total expense cost paid by `$user`, plus expenses paid by connected `$userIds` that include
     * `$user` as a split participant.
     *
     * @param int[] $userIds Extra connected payers to include.
     *
     * @throws UnprocessableException
     */
    public function forUser(
        User $user,
        int $offset = 0,
        int $limit = 6,
        ?int $categoryId = null,
        array $userIds = [],
    ): SeInsightsResponse {
        $otherPayers = $this->resolveOtherPayers($user, $userIds);
        $allMonths = $this->expenseRepository->listPaidExpenseMonths($user, $otherPayers, $categoryId);
        $totalMonthCount = count($allMonths);
        $months = array_slice($allMonths, $offset, $limit);
        $entries = $this->expenseRepository->sumPaidExpensesByCategory(
            $user,
            $otherPayers,
            $months,
            $categoryId,
        );
        if ($entries === []) {
            return new SeInsightsResponse(totalMonthCount: $totalMonthCount);
        }

        $categories = $this->categoriesById($entries);
        $currencies = $this->currenciesById($entries);
        $totals = [];

        foreach ($entries as $entry) {
            $category = $categories[$entry->categoryId] ?? null;
            $currency = $currencies[$entry->currencyId] ?? null;
            if ($category === null || $currency === null) {
                continue;
            }

            $totals[] = new SeCategoryTotal(
                $entry->month,
                $category,
                $currency,
                $entry->amount,
            );
        }

        usort($totals, static function (SeCategoryTotal $a, SeCategoryTotal $b): int {
            $byMonth = $b->month <=> $a->month;
            if ($byMonth !== 0) {
                return $byMonth;
            }

            return [$a->category->getTitle(), $a->currency->getCode()]
                <=> [$b->category->getTitle(), $b->currency->getCode()];
        });

        return new SeInsightsResponse($totals, $totalMonthCount);
    }

    /**
     * @param int[] $userIds
     *
     * @return User[]
     *
     * @throws UnprocessableException
     */
    private function resolveOtherPayers(User $user, array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(
            $userIds,
            static fn (int $id): bool => $id !== (int) $user->getId(),
        )));
        if ($userIds === []) {
            return [];
        }

        /** @var User[] $others */
        $others = $this->userRepository->findBy(['id' => $userIds]);
        if (count($others) !== count($userIds)) {
            throw new UnprocessableException('User not found.', 'userIds');
        }

        foreach ($others as $other) {
            if (!$this->connectionService->isConnected($user, $other)) {
                throw new UnprocessableException('Users require a connection.', 'userIds');
            }
        }

        return $others;
    }

    /**
     * @param SeCategoryTotalEntry[] $entries
     *
     * @return array<int, SeCategory>
     */
    private function categoriesById(array $entries): array
    {
        $ids = [];
        foreach ($entries as $entry) {
            $ids[$entry->categoryId] = $entry->categoryId;
        }

        $categories = [];
        foreach ($this->categoryRepository->findBy(['id' => array_values($ids)]) as $category) {
            $categories[(int) $category->getId()] = $category;
        }

        return $categories;
    }

    /**
     * @param SeCategoryTotalEntry[] $entries
     *
     * @return array<int, Currency>
     */
    private function currenciesById(array $entries): array
    {
        $ids = [];
        foreach ($entries as $entry) {
            $ids[$entry->currencyId] = $entry->currencyId;
        }

        $currencies = [];
        foreach ($this->currencyRepository->findBy(['id' => array_values($ids)]) as $currency) {
            $currencies[(int) $currency->getId()] = $currency;
        }

        return $currencies;
    }
}
