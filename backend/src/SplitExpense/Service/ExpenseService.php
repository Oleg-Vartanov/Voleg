<?php

namespace App\SplitExpense\Service;

use App\Core\Exception\UnprocessableException;
use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Entity\Category;
use App\SplitExpense\Entity\Expense;
use App\SplitExpense\Entity\ExpenseSplit;
use App\SplitExpense\Http\V1\Request\ExpenseDto;
use App\SplitExpense\Http\V1\Request\ExpenseSplitDto;
use App\SplitExpense\Repository\CategoryRepository;
use App\SplitExpense\Repository\ExpenseRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;

readonly class ExpenseService
{
    public function __construct(
        private CurrencyRepository $currencyRepository,
        private ConnectionService $connectionService,
        private ExpenseRepository $expenseRepository,
        private CategoryRepository $categoryRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function hasAccess(User $user, Expense $expense): bool
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

    public function canUpdate(User $user, Expense $expense): bool
    {
        if (!$this->hasAccess($user, $expense)) {
            return false;
        }

        foreach ($expense->getSplits() as $split) {
            if (
                $split->getUser()->getId() !== $user->getId()
                && !$this->connectionService->isConnected($user, $split->getUser())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws UnprocessableException
     */
    public function create(User $createdBy, ExpenseDto $dto): Expense
    {
        $paidBy = $this->userRepository->find($dto->paidByUserId)
            ?? throw new UnprocessableException('Payer not found.', 'paidByUserId');
        $category = $this->categoryRepository->find($dto->categoryId ?? Category::DEFAULT_ID)
            ?? throw new UnprocessableException('Category not found.', 'categoryId');
        $currency = $this->currencyRepository->find($dto->currencyId)
            ?? throw new UnprocessableException('Currency not found.', 'currencyId');

        try {
            $expenseDate = new DateTimeImmutable($dto->expenseDate);
        } catch (DateMalformedStringException $e) {
            throw new UnprocessableException('Invalid date. ' . $e->getMessage(), 'expenseDate');
        }

        $expense = new Expense(
            paidByUser: $paidBy,
            createdByUser: $createdBy,
            category: $category,
            amount: $dto->amount,
            title: $dto->title,
            currency: $currency,
            expenseDate: $expenseDate,
            description: $dto->description,
        );

        $this->applySplits($createdBy, $expense, $dto->splits);

        return $expense;
    }

    /**
     * @throws UnprocessableException
     */
    public function update(User $updatedBy, Expense $expense, ExpenseDto $dto): Expense
    {
        if (!$this->canUpdate($updatedBy, $expense)) {
            $this->expenseSplitException("Can't edit expense with unconnected user.");
        }

        $paidBy = $this->userRepository->find($dto->paidByUserId)
            ?? throw new UnprocessableException('Payer not found.', 'paidByUserId');
        $category = $this->categoryRepository->find($dto->categoryId)
            ?? throw new UnprocessableException('Category not found.', 'categoryId');
        $currency = $this->currencyRepository->find($dto->currencyId)
            ?? throw new UnprocessableException('Currency not found.', 'currencyId');

        $expense->setPaidByUser($paidBy);
        $expense->setCategory($category);
        $expense->setCurrency($currency);
        $expense->setAmount($dto->amount);
        $expense->setTitle($dto->title);
        try {
            $expense->setExpenseDate(new DateTimeImmutable($dto->expenseDate));
        } catch (DateMalformedStringException $e) {
            throw new UnprocessableException('Invalid date: ' . $e->getMessage(), 'expenseDate');
        }
        $expense->setDescription($dto->description);

        $this->applySplits($updatedBy, $expense, $dto->splits);

        return $expense;
    }

    public function delete(Expense $expense): void
    {
        $this->expenseRepository->remove($expense, true);
    }

    /**
     * @param ExpenseSplitDto[] $dtos
     *
     * @throws UnprocessableException
     */
    private function applySplits(User $appliedBy, Expense $expense, array $dtos): void
    {
        $currentSplits = [];
        $appliedSplitsUsersIds = [];

        foreach ($expense->getSplits() as $split) {
            $currentSplits[$split->getUser()->getId()] = $split;
        }

        foreach ($dtos as $dto) {
            $appliedSplitsUsersIds[] = $dto->userId;

            if ($appliedBy->getId() === $dto->userId) {
                $user = $appliedBy;
            } else {
                $user = $this->userRepository->findById($dto->userId)
                    ?? $this->expenseSplitException('User not found.');

                if (
                    $user->getId() !== $appliedBy->getId()
                    && !$this->connectionService->isConnected($appliedBy, $user)
                ) {
                    $this->expenseSplitException('Split user require connection.');
                }
            }

            if (isset($currentSplits[$user->getId()])) {
                $currentSplits[$user->getId()]->setAmount($dto->amount);
            } else {
                $expense->addSplit(new ExpenseSplit($expense, $user, $dto->amount));
            }
        }

        foreach ($currentSplits as $split) {
            if (!in_array($split->getUser()->getId(), $appliedSplitsUsersIds)) {
                $expense->removeSplit($split);
            }
        }

        $this->assertSplit($expense);
    }

    /**
     * @throws UnprocessableException
     */
    private function assertSplit(Expense $expense): void
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
            $this->expenseSplitException('Split must include at least 2 users.');
        }

        if ($total !== $expense->getAmount()) {
            $this->expenseSplitException('Split amounts must sum to the expense amount.');
        }

        if (!$splitHasPaidBy) {
            $this->expenseSplitException('Split must include the paidBy user.');
        }

        if (count($splitsUserIds) !== count(array_unique($splitsUserIds))) {
            $this->expenseSplitException('Split must include unique users.');
        }

        if (!in_array($expense->getCreatedByUser()->getId(), $splitsUserIds)) {
            $this->expenseSplitException('Split must include who created the expense.');
        }
    }

    /**
     * @throws UnprocessableException
     */
    private function expenseSplitException(string $message): never
    {
        throw new UnprocessableException($message, 'splits');
    }
}
