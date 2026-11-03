<?php

namespace App\SplitExpense\Service;

use App\Core\Exception\NotFoundException;
use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Entity\Category;
use App\SplitExpense\Entity\Expense;
use App\SplitExpense\Entity\ExpenseSplit;
use App\SplitExpense\Exception\ExpenseSplitException;
use App\SplitExpense\Http\V1\Request\ExpenseDto;
use App\SplitExpense\Http\V1\Request\ExpenseSplitDto;
use App\SplitExpense\Repository\CategoryRepository;
use App\SplitExpense\Repository\ExpenseRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use LogicException;

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
     * @throws DateMalformedStringException|ExpenseSplitException|LogicException
     */
    public function create(User $createdBy, ExpenseDto $dto): Expense
    {
        $paidBy = $this->userRepository->find($dto->paidByUserId)
            ?? throw new LogicException('Payer not found.');
        $category = $this->categoryRepository->find($dto->categoryId ?? Category::DEFAULT_ID)
            ?? throw new LogicException('Category not found.');
        $currency = $this->currencyRepository->find($dto->currencyId)
            ?? throw new LogicException('Currency not found.');

        $expense = new Expense(
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
     * @throws DateMalformedStringException
     * @throws NotFoundException
     * @throws ExpenseSplitException
     */
    public function update(User $updatedBy, Expense $expense, ExpenseDto $dto): Expense
    {
        if (!$this->canUpdate($updatedBy, $expense)) {
            throw throw new ExpenseSplitException('Can\'t edit expense with unconnected user.');
        }

        $paidBy = $this->userRepository->find($dto->paidByUserId)
            ?? throw new NotFoundException('Payer not found.', tag: 'paidByUserId');
        $category = $this->categoryRepository->find($dto->categoryId)
            ?? throw new NotFoundException('Category not found.', tag: 'categoryId');
        $currency = $this->currencyRepository->find($dto->currencyId)
            ?? throw new NotFoundException('Currency not found.', tag: 'currencyId');

        $expense->setPaidByUser($paidBy);
        $expense->setCategory($category);
        $expense->setCurrency($currency);
        $expense->setAmount($dto->amount);
        $expense->setTitle($dto->title);
        $expense->setExpenseDate(new DateTimeImmutable($dto->expenseDate));
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
     * @throws ExpenseSplitException
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
                    ?? throw new ExpenseSplitException('User not found.');

                if (
                    $user->getId() !== $appliedBy->getId()
                    && !$this->connectionService->isConnected($appliedBy, $user)
                ) {
                    throw new ExpenseSplitException('Split user require connection.');
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
     * @throws ExpenseSplitException
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
            throw new ExpenseSplitException('Split must include at least 2 users.');
        }

        if ($total !== $expense->getAmount()) {
            throw new ExpenseSplitException('Split amounts must sum to the expense amount.');
        }

        if (!$splitHasPaidBy) {
            throw new ExpenseSplitException('Split must include the paidBy user.');
        }

        if (count($splitsUserIds) !== count(array_unique($splitsUserIds))) {
            throw new ExpenseSplitException('Split must include unique users.');
        }

        if (!in_array($expense->getCreatedByUser()->getId(), $splitsUserIds)) {
            throw new ExpenseSplitException('Split must include who created the expense.');
        }
    }
}
