<?php

namespace App\Repository;

use App\Entity\Period;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Period>
 */
class PeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Period::class);
    }

    public function save(Period $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Period $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupère toutes les périodes d'un utilisateur, triées par date de début décroissante
     */
    public function findByUserOrderedByDate(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère la période actuelle d'un utilisateur (celle qui contient la date d'aujourd'hui)
     */
    public function findCurrentPeriodForUser(User $user): ?Period
    {
        $today = new \DateTime();
        
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andWhere('p.startDate <= :today')
            ->andWhere('p.endDate >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->orderBy('p.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Vérifie si une période chevauche avec d'autres périodes du même utilisateur
     */
    public function findOverlappingPeriods(User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate, ?Period $excludePeriod = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andWhere('(p.startDate <= :endDate AND p.endDate >= :startDate)')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($excludePeriod) {
            $qb->andWhere('p.id != :excludeId')
               ->setParameter('excludeId', $excludePeriod->getId());
        }

        return $qb->getQuery()->getResult();
    }
} 