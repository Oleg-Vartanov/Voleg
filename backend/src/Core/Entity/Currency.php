<?php

namespace App\Core\Entity;

use App\Core\Repository\CurrencyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_CURRENCY_CODE', fields: ['code'])]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @param string $code ISO 4217 code
     */
    public function __construct(
        #[ORM\Column(length: 3)]
        private readonly string $code,
        #[ORM\Column]
        private readonly int $decimalPlaces,
        #[ORM\Column(length: 8)]
        private readonly string $symbol,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDecimalPlaces(): int
    {
        return $this->decimalPlaces;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }
}
