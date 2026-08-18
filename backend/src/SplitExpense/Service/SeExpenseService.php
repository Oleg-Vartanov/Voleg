<?php

namespace App\SplitExpense\Service;

use App\Core\Exception\NotFoundException;
use App\Core\Repository\CurrencyRepository;
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

    public function canUpdate(User $user, SeExpense $expense): bool
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
     * @throws DateMalformedStringException
     * @throws NotFoundException
     * @throws SeExpenseSplitException
     */
    public function update(User $updatedBy, SeExpense $expense, SeExpenseDto $dto): SeExpense
    {
        if (!$this->canUpdate($updatedBy, $expense)) {
            throw throw new SeExpenseSplitException('Can\'t edit expense with unconnected user.');
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
                    ?? throw new SeExpenseSplitException('User not found.');

                if (
                    $user->getId() !== $appliedBy->getId()
                    && !$this->connectionService->isConnected($appliedBy, $user)
                ) {
                    throw new SeExpenseSplitException('Split user require connection.');
                }
            }

            if (isset($currentSplits[$user->getId()])) {
                $currentSplits[$user->getId()]->setAmount($dto->amount);
            } else {
                $expense->addSplit(new SeExpenseSplit($expense, $user, $dto->amount));
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
