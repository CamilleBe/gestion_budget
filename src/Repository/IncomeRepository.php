<?php

namespace App\Repository;

use App\Entity\Income;
use App\Entity\Period;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Income>
 */
class IncomeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Income::class);
    }

    public function save(Income $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Income $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupère les revenus d'une période, triés par date décroissante
     */
    public function findByPeriodOrderedByDate(Period $period): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.period = :period')
            ->setParameter('period', $period)
            ->orderBy('i.date', 'DESC')
            ->addOrderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le total des revenus pour une période
     */
    public function getTotalForPeriod(Period $period): float
    {
        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.amount)')
            ->andWhere('i.period = :period')
            ->setParameter('period', $period)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * Récupère les revenus d'un utilisateur pour toutes ses périodes
     */
    public function findByUserOrderedByDate(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.period', 'p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('i.date', 'DESC')
            ->addOrderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les revenus récents d'un utilisateur (limite configurable)
     */
    public function findRecentByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.period', 'p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('i.date', 'DESC')
            ->addOrderBy('i.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
} 