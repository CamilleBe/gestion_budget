<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\Period;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expense>
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function save(Expense $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Expense $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupère les dépenses d'une période, triées par date décroissante
     */
    public function findByPeriodOrderedByDate(Period $period): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.period = :period')
            ->setParameter('period', $period)
            ->orderBy('e.date', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le total des dépenses pour une période
     */
    public function getTotalForPeriod(Period $period): float
    {
        $result = $this->createQueryBuilder('e')
            ->select('SUM(e.amount)')
            ->andWhere('e.period = :period')
            ->setParameter('period', $period)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * Récupère les dépenses d'un utilisateur pour toutes ses périodes
     */
    public function findByUserOrderedByDate(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.period', 'p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.date', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les dépenses récentes d'un utilisateur (limite configurable)
     */
    public function findRecentByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.period', 'p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.date', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
} 