<?php

declare(strict_types=1);

namespace App\Exchanging\Policy;

/**
 * Lifecycle guard for exchange rate.
 *
 * The policy is intentionally framework-free: entities/services can call it
 * without introducing cross-component Doctrine dependencies.
 */
final class ExchangeRateLifecyclePolicy
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'draft' => ['published', 'rejected'],
        'published' => ['superseded', 'revoked'],
        'superseded' => ['archived'],
        'revoked' => ['archived'],
        'rejected' => ['draft', 'archived'],
        'archived' => [],
    ];

    public function canTransition(string $from, string $to): bool
    {
        $from = self::normalize($from);
        $to = self::normalize($to);

        if ($from === $to) {
            return true;
        }

        return \in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public function assertCanTransition(string $from, string $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new \DomainException(sprintf(
                'Invalid exchange rate lifecycle transition from "%s" to "%s".',
                $from,
                $to,
            ));
        }
    }

    /** @return list<string> */
    public function allowedNextStatuses(string $from): array
    {
        return self::TRANSITIONS[self::normalize($from)] ?? [];
    }

    private static function normalize(string $status): string
    {
        return strtolower(trim($status));
    }
}
