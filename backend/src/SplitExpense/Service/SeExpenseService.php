<?php

namespace App\SplitExpense\Service;

use App\Core\Repository\CurrencyRepository;
use App\Core\Util\PropertyAccessor;
use App\SplitExpense\Entity\SeCategory;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Entity\SeExpenseSplit;
use App\SplitExpense\Exception\SeExpenseSplitException;
use App\SplitExpense\Http\V1\Request\SeExpenseDto;
use App\SplitExpense\Http\V1\Request\SeExpenseSplitDto;
use App\SplitExpense\Repository\SeCategoryRepository;
use App\SplitExpense\Repository\SeExpenseRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use LogicException;

readonly class SeExpenseService
{
    public function __construct(
        private CurrencyRepository $currencyRepository,
        private SeConnectionService $connectionService,
        private SeExpenseRepository $expenseRepository,
        private SeCategoryRepository $categoryRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function hasAccess(User $user, SeExpense $expense): bool
    {
        if ($expense->getPaidByUser()->getId() === $user->getId()) {
            return true;
        }

        foreach ($expense->getSplits() as $split) {
            if ($split->getUser()->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws DateMalformedStringException|SeExpenseSplitException|LogicException
     */
    public function create(User $createdBy, SeExpenseDto $dto): SeExpense
    {
        $paidBy = $this->userRepository->find($dto->paidByUserId)
            ?? throw new LogicException('Payer not found.');
        $category = $this->categoryRepository->find($dto->categoryId ?? SeCategory::DEFAULT_ID)
            ?? throw new LogicException('Category not found.');
        $currency = $this->currencyRepository->find($dto->currencyId)
            ?? throw new LogicException('Currency not found.');

        $expense = new SeExpense(
            paidByUser: $paidBy,
            createdByUser: $createdBy,
            category: $category,
            amount: $dto->amount,
            title: $dto->title,
            currency: $currency,
            expenseDate: new DateTimeImmutable($dto->expenseDate),
            description: $dto->description,
        );

        $this->applySplits($createdBy, $expense, $dto->splits);

        return $expense;
    }

    /**
     * @throws LogicException|DateMalformedStringException|SeExpenseSplitException
     */
    public function patch(User $patchedBy, SeExpense $expense, SeExpenseDto $dto): SeExpense
    {
        foreach ($expense->getSplits() as $split) {
            if (!$this->connectionService->isConnected($patchedBy, $split->getUser())) {
                throw new SeExpenseSplitException('Can\'t edit expense with unconnected user.');
            }
        }

        $props = array_flip(PropertyAccessor::getInitializedProperties($dto));

        if (isset($props['title'])) {
            $expense->setTitle($dto->title);
        }

        if (isset($props['description'])) {
            $expense->setDescription($dto->description);
        }

        if (isset($props['amount'])) {
            $expense->setAmount($dto->amount);
        }

        if (isset($props['expenseDate'])) {
            $expense->setExpenseDate(new DateTimeImmutable($dto->expenseDate));
        }

        if (isset($props['currencyId'])) {
            $currency = $this->currencyRepository->find($dto->currencyId)
                ?? throw new LogicException('Currency not found.');
            $expense->setCurrency($currency);
        }

        if (isset($props['categoryId'])) {
            $category = $this->categoryRepository->find($dto->categoryId)
                ?? throw new LogicException('Category not found.');
            $expense->setCategory($category);
        }

        if (isset($props['paidByUserId'])) {
            $paidBy = $this->userRepository->find($dto->paidByUserId)
                ?? throw new LogicException('Payer user not found.');
            $expense->setPaidByUser($paidBy);
        }

        if (isset($props['splits'])) {
            $expense->clearSplits();
            $this->applySplits($patchedBy, $expense, $dto->splits);
        }

        return $expense;
    }

    public function delete(SeExpense $expense): void
    {
        $this->expenseRepository->remove($expense, true);
    }

    /**
     * @param SeExpenseSplitDto[] $dtos
     *
     * @throws SeExpenseSplitException
     */
    private function applySplits(User $appliedBy, SeExpense $expense, array $dtos): void
    {
        foreach ($dtos as $dto) {
            if ($appliedBy->getId() === $dto->userId) {
                $user = $appliedBy;
            } else {
                $user = $this->userRepository->findById($dto->userId)
                    ?? throw new SeExpenseSplitException('User not found.');

                if (!$this->connectionService->isConnected($appliedBy, $user)) {
                    throw new SeExpenseSplitException('Split user require connection.');
                }
            }

            $expense->addSplit(new SeExpenseSplit($expense, $user, $dto->amount));
        }

        $this->assertSplit($expense);
    }

    /**
     * @throws SeExpenseSplitException
     */
    private function assertSplit(SeExpense $expense): void
    {
        $total = 0;
        $splitHasPaidBy = false;
        $splitsUserIds = [];

        foreach ($expense->getSplits() as $split) {
            $total += $split->getAmount();

            if ($split->getUser()->getId() === $expense->getPaidByUser()->getId()) {
                $splitHasPaidBy = true;
            }

            $splitsUserIds[] = $split->getUser()->getId();
        }

        if (count($expense->getSplits()) < 2) {
            throw new SeExpenseSplitException('Split must include at least 2 users.');
        }

        if ($total !== $expense->getAmount()) {
            throw new SeExpenseSplitException('Split amounts must sum to the expense amount.');
        }

        if (!$splitHasPaidBy) {
            throw new SeExpenseSplitException('Split must include the paidBy user.');
        }

        if (count($splitsUserIds) !== count(array_unique($splitsUserIds))) {
            throw new SeExpenseSplitException('Split must include unique users.');
        }

        if (!in_array($expense->getCreatedByUser()->getId(), $splitsUserIds)) {
            throw new SeExpenseSplitException('Split must include who created the expense.');
        }
    }
}
