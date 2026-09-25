<?php

declare(strict_types=1);

namespace App\Exchanging\Entity;

use App\Exchanging\Contract\ExchangeRateEntityInterface;
use App\Exchanging\Repository\ExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[ORM\Table(name: 'exchange_rate')]
#[ORM\Index(columns: ['base_currency_code', 'quote_currency_code', 'rate_date'], name: 'idx_exchange_rate_pair_date')]
#[ORM\Index(columns: ['provider_code', 'rate_date'], name: 'idx_exchange_rate_provider_date')]
final class ExchangeRateEntity implements ExchangeRateEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'base_currency_code', length: 3)]
    private string $baseCurrencyCode;

    #[ORM\Column(name: 'quote_currency_code', length: 3)]
    private string $quoteCurrencyCode;

    #[ORM\Column(name: 'rate_value', type: Types::DECIMAL, precision: 20, scale: 10)]
    private string $rateValue;

    #[ORM\Column(name: 'provider_code', length: 80)]
    private string $providerCode;

    #[ORM\Column(name: 'rate_date', type: 'date_immutable')]
    private \DateTimeImmutable $rateDate;

    #[ORM\Column(name: 'captured_at_immutable', type: 'datetime_immutable')]
    private \DateTimeImmutable $capturedAtImmutable;

    public function __construct(string $baseCurrencyCode, string $quoteCurrencyCode, string $rateValue, string $providerCode, \DateTimeImmutable $rateDate)
    {
        $this->baseCurrencyCode = strtoupper($baseCurrencyCode);
        $this->quoteCurrencyCode = strtoupper($quoteCurrencyCode);
        $this->rateValue = $rateValue;
        $this->providerCode = $providerCode;
        $this->rateDate = $rateDate;
        $this->capturedAtImmutable = new \DateTimeImmutable();
    }

    public function id(): ?int
    {
        return $this->id;
    }
    public function baseCurrencyCode(): string
    {
        return $this->baseCurrencyCode;
    }
    public function quoteCurrencyCode(): string
    {
        return $this->quoteCurrencyCode;
    }
    public function rateValue(): string
    {
        return $this->rateValue;
    }
    public function providerCode(): string
    {
        return $this->providerCode;
    }
    public function rateDate(): \DateTimeImmutable
    {
        return $this->rateDate;
    }
    public function capturedAtImmutable(): \DateTimeImmutable
    {
        return $this->capturedAtImmutable;
    }

    /** Legacy-monolith compatibility: source currency relation is represented as a boundary code. */
    public function getSourceCurrency(): string
    {
        return $this->baseCurrencyCode;
    }

    /** Legacy-monolith compatibility: target currency relation is represented as a boundary code. */
    public function getTargetCurrency(): string
    {
        return $this->quoteCurrencyCode;
    }

    /** Legacy-monolith compatibility: ratio maps to canonical rate value. */
    public function getRatio(): string
    {
        return $this->rateValue;
    }
}
