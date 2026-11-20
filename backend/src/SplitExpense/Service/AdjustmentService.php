<?php

namespace App\SplitExpense\Service;

use App\Core\Exception\AccessDeniedException;
use App\Core\Model\Entity\Currency;
use App\Core\Exception\UnprocessableException;
use App\Core\Repository\CurrencyRepository;
use App\SplitExpense\Model\Entity\Adjustment;
use App\SplitExpense\Http\V1\Adjustment\AdjustmentRequestPayload;
use App\SplitExpense\Repository\AdjustmentRepository;
use App\User\Model\Entity\User;
use App\User\Repository\UserRepository;
use DateMalformedStringException;
use DateTimeImmutable;

readonly class AdjustmentService
{
    public function __construct(
        private CurrencyRepository $currencyRepository,
        private ConnectionService $connectionService,
        private AdjustmentRepository $adjustmentRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function hasAccess(User $user, Adjustment $adjustment): bool
    {
        return $adjustment->getCreatedByUser()->getId() === $user->getId()
            || $adjustment->getOtherUser()->getId() === $user->getId();
    }

    public function canUpdate(User $user, Adjustment $adjustment): bool
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
    public function create(
        User $createdBy,
        AdjustmentRequestPayload $payload,
    ): Adjustment {
        $otherUser = $this->resolveOtherUser($createdBy, $payload->otherUserId);

        return new Adjustment(
            createdByUser: $createdBy,
            otherUser: $otherUser,
            amount: $payload->amount,
            currency: $this->resolveCurrency($payload->currencyId),
            adjustmentDate: $this->resolveDate($payload->adjustmentDate),
            description: $payload->description,
        );
    }

    /**
     * @throws UnprocessableException
     * @throws AccessDeniedException
     */
    public function update(
        User $updatedBy,
        Adjustment $adjustment,
        AdjustmentRequestPayload $payload,
    ): Adjustment {
        if (!$this->canUpdate($updatedBy, $adjustment)) {
            throw new AccessDeniedException();
        }

        $adjustment->setCurrency($this->resolveCurrency($payload->currencyId));
        $adjustment->setAmount($payload->amount);
        $adjustment->setAdjustmentDate($this->resolveDate($payload->adjustmentDate));
        $adjustment->setDescription($payload->description);

        return $adjustment;
    }

    public function delete(Adjustment $adjustment): void
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
