<?php

declare(strict_types=1);

namespace App\Exchanging\DataFixtures;

use App\Exchanging\Entity\ExchangeEntity;
use App\Exchanging\Entity\ExchangeRateEntity;
use Doctrine\Persistence\ObjectManager;

final class ExchangeDemoFixtures
{
    /**
     * Deterministic demo data keeps this component independent from Host fixture bases.
     */
    public function load(ObjectManager $manager): void
    {
        $pairs = [
            ['USD', 'EUR', '0.9200000000', 'ecb'],
            ['USD', 'GBP', '0.7800000000', 'manual'],
            ['EUR', 'PLN', '4.2500000000', 'ecb'],
            ['USD', 'UAH', '41.2500000000', 'nbu'],
        ];

        foreach ($pairs as $index => [$base, $quote, $rateValue, $providerCode]) {
            $exchange = new ExchangeEntity($base, $quote);
            if (0 === $index % 3) {
                $exchange->deactivate();
            }
            $manager->persist($exchange);

            foreach (range(0, 2) as $dayOffset) {
                $manager->persist(new ExchangeRateEntity(
                    $base,
                    $quote,
                    $rateValue,
                    $providerCode,
                    (new \DateTimeImmutable(sprintf('-%d day', $dayOffset)))->setTime(0, 0),
                ));
            }
        }

        $manager->flush();
    }
}
