<?php

namespace App\Repository;

use App\Entity\ItemSize;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemSize|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemSize|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemSize[]    findAll()
 * @method ItemSize[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemSizeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemSize::class);
    }

    // /**
    //  * @return ItemSize[] Returns an array of ItemSize objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ItemSize
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
