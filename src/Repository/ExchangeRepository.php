<?php

declare(strict_types=1);

namespace App\Exchanging\Repository;

use App\Exchanging\Entity\ExchangeEntity;
use App\Exchanging\RepositoryInterface\ExchangeRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ExchangeEntity> */
final class ExchangeRepository extends ServiceEntityRepository implements ExchangeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeEntity::class);
    }

    public function save(ExchangeEntity $exchange, bool $flush = true): void
    {
        $this->getEntityManager()->persist($exchange);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActivePair(string $baseCurrencyCode, string $quoteCurrencyCode): ?ExchangeEntity
    {
        return $this->createQueryBuilder('exchange')
            ->andWhere('exchange.baseCurrencyCode = :baseCurrencyCode')
            ->andWhere('exchange.quoteCurrencyCode = :quoteCurrencyCode')
            ->andWhere('exchange.active = true')
            ->setParameter('baseCurrencyCode', strtoupper($baseCurrencyCode))
            ->setParameter('quoteCurrencyCode', strtoupper($quoteCurrencyCode))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
