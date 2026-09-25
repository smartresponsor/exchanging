<?php

declare(strict_types=1);

namespace App\Exchanging\Repository;

use App\Exchanging\DTO\ExchangeRateReadRequestDTO;
use App\Exchanging\Entity\ExchangeRateEntity;
use App\Exchanging\RepositoryInterface\ExchangeRateRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ExchangeRateEntity> */
final class ExchangeRateRepository extends ServiceEntityRepository implements ExchangeRateRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRateEntity::class);
    }

    public function save(ExchangeRateEntity $exchangeRate, bool $flush = true): void
    {
        $this->getEntityManager()->persist($exchangeRate);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLatestForPair(string $baseCurrencyCode, string $quoteCurrencyCode): ?ExchangeRateEntity
    {
        return $this->createQueryBuilder('rate')
            ->andWhere('rate.baseCurrencyCode = :baseCurrencyCode')
            ->andWhere('rate.quoteCurrencyCode = :quoteCurrencyCode')
            ->setParameter('baseCurrencyCode', strtoupper($baseCurrencyCode))
            ->setParameter('quoteCurrencyCode', strtoupper($quoteCurrencyCode))
            ->orderBy('rate.rateDate', 'DESC')
            ->addOrderBy('rate.capturedAtImmutable', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestForPairOnOrBeforeDate(
        string $baseCurrencyCode,
        string $quoteCurrencyCode,
        \DateTimeImmutable $rateDate,
    ): ?ExchangeRateEntity {
        return $this->createQueryBuilder('rate')
            ->andWhere('rate.baseCurrencyCode = :baseCurrencyCode')
            ->andWhere('rate.quoteCurrencyCode = :quoteCurrencyCode')
            ->andWhere('rate.rateDate <= :rateDate')
            ->setParameter('baseCurrencyCode', strtoupper($baseCurrencyCode))
            ->setParameter('quoteCurrencyCode', strtoupper($quoteCurrencyCode))
            ->setParameter('rateDate', $rateDate)
            ->orderBy('rate.rateDate', 'DESC')
            ->addOrderBy('rate.capturedAtImmutable', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<ExchangeRateEntity>
     */
    public function findLatestRates(ExchangeRateReadRequestDTO $request): array
    {
        $builder = $this->createQueryBuilder('rate')
            ->orderBy('rate.rateDate', 'DESC')
            ->addOrderBy('rate.capturedAtImmutable', 'DESC')
            ->setMaxResults(max(1, min(100, $request->limit)));

        if ($request->baseCurrencyCode !== null && $request->baseCurrencyCode !== '') {
            $builder
                ->andWhere('rate.baseCurrencyCode = :baseCurrencyCode')
                ->setParameter('baseCurrencyCode', strtoupper($request->baseCurrencyCode));
        }

        if ($request->quoteCurrencyCode !== null && $request->quoteCurrencyCode !== '') {
            $builder
                ->andWhere('rate.quoteCurrencyCode = :quoteCurrencyCode')
                ->setParameter('quoteCurrencyCode', strtoupper($request->quoteCurrencyCode));
        }

        if ($request->providerCode !== null && $request->providerCode !== '') {
            $builder
                ->andWhere('rate.providerCode = :providerCode')
                ->setParameter('providerCode', $request->providerCode);
        }

        /** @var list<ExchangeRateEntity> $rates */
        $rates = $builder->getQuery()->getResult();

        return $rates;
    }
}
