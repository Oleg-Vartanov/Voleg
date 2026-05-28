<?php

namespace App\SplitExpense\Service;

use App\Core\Repository\CurrencyRepository;
use App\Core\Util\PropertyAccessor;
use App\SplitExpense\Entity\SeCategory;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Entity\SeExpenseSplit;
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
        private SeExpenseRepository $expenseRepository,
        private SeCategoryRepository $categoryRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function hasAccess(User $user, SeExpense $expense): bool
    {
        if ($expense->getPaidByUser()->getId() !== $user->getId()) {
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
     * @throws DateMalformedStringException
     */
    public function create(User $owner, SeExpenseDto $dto): SeExpense
    {
        $paidBy = $this->userRepository->find($dto->paidByUserId ?? $owner->getId())
            ?? throw new LogicException('Payer not found.');
        $category = $this->categoryRepository->find($dto->categoryId ?? SeCategory::DEFAULT_ID)
            ?? throw new LogicException('Category not found.');
        $currency = $this->currencyRepository->find($dto->currencyId)
            ?? throw new LogicException('Currency not found.');

        $expense = new SeExpense(
            paidByUser: $paidBy,
            category: $category,
            amount: $dto->amount,
            title: $dto->title,
            currency: $currency,
            expenseDate: new DateTimeImmutable($dto->expenseDate),
            description: $dto->description,
        );

        $this->applySplits($expense, $dto->splits);

        return $expense;
    }

    /**
     * @throws LogicException|DateMalformedStringException
     */
    public function patch(SeExpense $expense, SeExpenseDto $dto): SeExpense
    {
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
            $this->applySplits($expense, $dto->splits);
        }

        return $expense;
    }

    public function delete(SeExpense $expense): void
    {
        $this->expenseRepository->remove($expense, true);
    }

    /**
     * @param SeExpenseSplitDto[] $dtos
     */
    private function applySplits(SeExpense $expense, array $dtos): void
    {
        foreach ($dtos as $dto) {
            $user = $this->userRepository->findById($dto->userId)
                ?? throw new LogicException('Split user not found.');

            $expense->addSplit(new SeExpenseSplit($expense, $user, $dto->amount));
        }

        // TODO: assert users: unique, not self,
        //  a proper split between paidBy and splits

        $this->assertSplitTotalMatches($expense);
    }

    private function assertSplitTotalMatches(SeExpense $expense): void
    {
        $total = '0';
        foreach ($expense->getSplits() as $split) {
            $total = bcadd($total, $split->getAmount(), 4);
        }

        if (bccomp($total, $expense->getAmount(), 4) !== 0) {
            throw new LogicException('Split amounts must sum to the expense amount.');
        }
    }
}
