<?php

namespace App\SplitExpense\Service;

use App\Core\Entity\Currency;
use App\Core\Exception\UnprocessableException;
use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Entity\SeAdjustment;
use App\SplitExpense\Http\V1\Request\SeAdjustmentDto;
use App\SplitExpense\Repository\SeAdjustmentRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;

readonly class SeAdjustmentService
{
    public function __construct(
        private CurrencyRepository $currencyRepository,
        private SeConnectionService $connectionService,
        private SeAdjustmentRepository $adjustmentRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function hasAccess(User $user, SeAdjustment $adjustment): bool
    {
        return $adjustment->getCreatedByUser()->getId() === $user->getId()
            || $adjustment->getOtherUser()->getId() === $user->getId();
    }

    public function canUpdate(User $user, SeAdjustment $adjustment): bool
    {
        if (!$this->hasAccess($user, $adjustment)) {
            return false;
        }

        $other = $adjustment->getCreatedByUser()->getId() === $user->getId()
            ? $adjustment->getOtherUser()
            : $adjustment->getCreatedByUser();

        return $this->connectionService->isConnected($user, $other);
    }

    /**
     * @throws UnprocessableException
     */
    public function create(User $createdBy, SeAdjustmentDto $dto): SeAdjustment
    {
        $otherUser = $this->resolveOtherUser($createdBy, $dto->otherUserId);

        return new SeAdjustment(
            createdByUser: $createdBy,
            otherUser: $otherUser,
            amount: $dto->amount,
            currency: $this->resolveCurrency($dto->currencyId),
            adjustmentDate: $this->resolveDate($dto->adjustmentDate),
            description: $dto->description,
        );
    }

    /**
     * @throws UnprocessableException
     */
    public function update(SeAdjustment $adjustment, SeAdjustmentDto $dto): SeAdjustment
    {
        $adjustment->setCurrency($this->resolveCurrency($dto->currencyId));
        $adjustment->setAmount($dto->amount);
        $adjustment->setAdjustmentDate($this->resolveDate($dto->adjustmentDate));
        $adjustment->setDescription($dto->description);

        return $adjustment;
    }

    public function delete(SeAdjustment $adjustment): void
    {
        $this->adjustmentRepository->remove($adjustment, true);
    }

    /**
     * @throws UnprocessableException
     */
    private function resolveCurrency(int $currencyId): Currency
    {
        $currency = $this->currencyRepository->find($currencyId);
        if (null === $currency) {
            throw new UnprocessableException('Currency not found.', 'currencyId');
        }

        return $currency;
    }

    /**
     * @throws UnprocessableException
     */
    private function resolveDate(string $date): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($date);
        } catch (DateMalformedStringException) {
            throw new UnprocessableException('Invalid date.', 'adjustmentDate');
        }
    }

    /**
     * @throws UnprocessableException
     */
    private function resolveOtherUser(User $createdBy, int $otherUserId): User
    {
        $otherUser = $this->userRepository->find($otherUserId);

        if (null === $otherUser) {
            throw new UnprocessableException('User not found.', 'otherUserId');
        }
        if ($createdBy->getId() === $otherUser->getId()) {
            throw new UnprocessableException("You can't adjust a balance with yourself.", 'otherUserId');
        }
        if (!$this->connectionService->isConnected($createdBy, $otherUser)) {
            throw new UnprocessableException('Users require a connection.', 'otherUserId');
        }

        return $otherUser;
    }
}
