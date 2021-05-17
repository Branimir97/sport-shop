<?php

namespace App\Repository;

use App\Entity\WishListItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WishListItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method WishListItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method WishListItem[]    findAll()
 * @method WishListItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WishListItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WishListItem::class);
    }

    // /**
    //  * @return WishListItem[] Returns an array of WishListItem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WishListItem
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
