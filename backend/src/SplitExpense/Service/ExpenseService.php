<?php

namespace App\SplitExpense\Service;

use App\Core\Exception\UnprocessableException;
use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Model\Entity\Category;
use App\SplitExpense\Model\Entity\Expense;
use App\SplitExpense\Model\Entity\ExpenseSplit;
use App\SplitExpense\Http\V1\Expense\ExpenseRequestPayload;
use App\SplitExpense\Http\V1\Expense\ExpenseSplitRequestPayload;
use App\SplitExpense\Repository\CategoryRepository;
use App\SplitExpense\Repository\ExpenseRepository;
use App\User\Model\Entity\User;
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
     * @throws UnprocessableException
     */
    public function create(User $createdBy, ExpenseRequestPayload $payload): Expense
    {
        $paidBy = $this->userRepository->find($payload->paidByUserId)
            ?? throw new UnprocessableException('Payer not found.', 'paidByUserId');
        $category = $this->categoryRepository->find($payload->categoryId ?? Category::DEFAULT_ID)
            ?? throw new UnprocessableException('Category not found.', 'categoryId');
        $currency = $this->currencyRepository->find($payload->currencyId)
            ?? throw new UnprocessableException('Currency not found.', 'currencyId');

        try {
            $expenseDate = new DateTimeImmutable($payload->expenseDate);
        } catch (DateMalformedStringException $e) {
            throw new UnprocessableException('Invalid date. ' . $e->getMessage(), 'expenseDate');
        }

        $expense = new Expense(
            paidByUser: $paidBy,
            createdByUser: $createdBy,
            category: $category,
            amount: $payload->amount,
            title: $payload->title,
            currency: $currency,
            expenseDate: $expenseDate,
            description: $payload->description,
        );

        $this->applySplits($createdBy, $expense, $payload->splits);

        return $expense;
    }

    /**
     * @throws UnprocessableException
     */
    public function update(User $updatedBy, Expense $expense, ExpenseRequestPayload $payload): Expense
    {
        if (!$this->canUpdate($updatedBy, $expense)) {
            $this->expenseSplitException("Can't edit expense with unconnected user.");
        }

        $paidBy = $this->userRepository->find($payload->paidByUserId)
            ?? throw new UnprocessableException('Payer not found.', 'paidByUserId');
        $category = $this->categoryRepository->find($payload->categoryId)
            ?? throw new UnprocessableException('Category not found.', 'categoryId');
        $currency = $this->currencyRepository->find($payload->currencyId)
            ?? throw new UnprocessableException('Currency not found.', 'currencyId');

        $expense->setPaidByUser($paidBy);
        $expense->setCategory($category);
        $expense->setCurrency($currency);
        $expense->setAmount($payload->amount);
        $expense->setTitle($payload->title);
        try {
            $expense->setExpenseDate(new DateTimeImmutable($payload->expenseDate));
        } catch (DateMalformedStringException $e) {
            throw new UnprocessableException('Invalid date: ' . $e->getMessage(), 'expenseDate');
        }
        $expense->setDescription($payload->description);

        $this->applySplits($updatedBy, $expense, $payload->splits);

        return $expense;
    }

    public function delete(Expense $expense): void
    {
        $this->expenseRepository->remove($expense, true);
    }

    /**
     * @param ExpenseSplitRequestPayload[] $splitPayloads
     *
     * @throws UnprocessableException
     */
    private function applySplits(User $appliedBy, Expense $expense, array $splitPayloads): void
    {
        $currentSplits = [];
        $appliedSplitsUsersIds = [];

        foreach ($expense->getSplits() as $split) {
            $userId = $split->getUser()->getId() ?? throw new LogicException('Split user is not persisted.');
            $currentSplits[$userId] = $split;
        }

        foreach ($splitPayloads as $splitPayload) {
            $appliedSplitsUsersIds[] = $splitPayload->userId;

            if ($appliedBy->getId() === $splitPayload->userId) {
                $user = $appliedBy;
            } else {
                $user = $this->userRepository->findById($splitPayload->userId)
                    ?? $this->expenseSplitException('User not found.');

                if (
                    $user->getId() !== $appliedBy->getId()
                    && !$this->connectionService->isConnected($appliedBy, $user)
                ) {
                    $this->expenseSplitException('Split user require connection.');
                }
            }

            if (isset($currentSplits[$splitPayload->userId])) {
                $currentSplits[$splitPayload->userId]->setAmount($splitPayload->amount);
            } else {
                $expense->addSplit(new ExpenseSplit($expense, $user, $splitPayload->amount));
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
