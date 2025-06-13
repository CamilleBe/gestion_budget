<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\User;
use App\Enum\CategoryType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupère toutes les catégories d'un utilisateur, triées par nom
     */
    public function findByUserOrderedByName(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les catégories d'un utilisateur par type
     */
    public function findByUserAndType(User $user, CategoryType $type): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les catégories de revenus d'un utilisateur
     */
    public function findIncomeCategories(User $user): array
    {
        return $this->findByUserAndType($user, CategoryType::INCOME);
    }

    /**
     * Récupère les catégories de dépenses d'un utilisateur
     */
    public function findExpenseCategories(User $user): array
    {
        return $this->findByUserAndType($user, CategoryType::EXPENSE);
    }
} 