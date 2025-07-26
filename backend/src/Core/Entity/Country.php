<?php

namespace App\Core\Entity;

use App\Core\Repository\CountryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(name: 'iso_3166_1_alpha_2', length: 2)]
    private string $Iso31661Alpha2;

    #[ORM\Column(name: 'iso_3166_1_alpha_3', length: 3)]
    private string $Iso31661Alpha3;

    #[ORM\Column(name: 'iso_3166_1_numeric', type: Types::SMALLINT)]
    private int $Iso31661Numeric;

    #[ORM\Column(name: 'iso_3166_2', length: 6, nullable: true)]
    private ?string $iso31662;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIso31661Alpha2(): string
    {
        return $this->Iso31661Alpha2;
    }

    public function setIso31661Alpha2(string $Iso31661Alpha2): static
    {
        $this->Iso31661Alpha2 = $Iso31661Alpha2;

        return $this;
    }

    public function getIso31661Alpha3(): string
    {
        return $this->Iso31661Alpha3;
    }

    public function setIso31661Alpha3(string $Iso31661Alpha3): static
    {
        $this->Iso31661Alpha3 = $Iso31661Alpha3;

        return $this;
    }

    public function getIso31661Numeric(): int
    {
        return $this->Iso31661Numeric;
    }

    public function setIso31661Numeric(int $Iso31661Numeric): static
    {
        $this->Iso31661Numeric = $Iso31661Numeric;

        return $this;
    }

    public function getIso31662(): ?string
    {
        return $this->iso31662;
    }

    public function setIso31662(?string $iso31662): static
    {
        $this->iso31662 = $iso31662;

        return $this;
    }
}
