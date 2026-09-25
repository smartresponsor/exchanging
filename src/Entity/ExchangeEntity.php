<?php

declare(strict_types=1);

namespace App\Exchanging\Entity;

use App\Exchanging\Contract\ExchangeEntityInterface;
use App\Exchanging\Repository\ExchangeRepository;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRepository::class)]
#[ORM\Table(name: 'exchange_exchange')]
#[ORM\Index(columns: ['base_currency_code', 'quote_currency_code'], name: 'idx_exchange_pair')]
final class ExchangeEntity implements ExchangeEntityInterface
{
    use ObjectAuditEmbeddableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'base_currency_code', length: 3)]
    private string $baseCurrencyCode;

    #[ORM\Column(name: 'quote_currency_code', length: 3)]
    private string $quoteCurrencyCode;

    #[ORM\Column(name: 'is_active', type: 'boolean')]
    private bool $active = true;

    public function __construct(string $baseCurrencyCode, string $quoteCurrencyCode)
    {
        $this->baseCurrencyCode = strtoupper($baseCurrencyCode);
        $this->quoteCurrencyCode = strtoupper($quoteCurrencyCode);
        $this->initializeObjectAudit();
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
    public function active(): bool
    {
        return $this->active;
    }

    public function deactivate(): void
    {
        if (!$this->active) {
            return;
        }

        $this->active = false;
        $this->touchModified();
    }

    public function activate(): void
    {
        if ($this->active) {
            return;
        }

        $this->active = true;
        $this->touchModified();
    }

    /**
     * Legacy-monolith boundary name: the old model linked to Currency directly.
     * Exchanging now keeps the boundary as ISO currency code, not cross-component ORM relation.
     */
    public function sourceCurrencyReference(): string
    {
        return $this->baseCurrencyCode;
    }

    /**
     * Legacy-monolith boundary name: the old model linked to Currency directly.
     * Exchanging now keeps the boundary as ISO currency code, not cross-component ORM relation.
     */
    public function targetCurrencyReference(): string
    {
        return $this->quoteCurrencyCode;
    }
}
