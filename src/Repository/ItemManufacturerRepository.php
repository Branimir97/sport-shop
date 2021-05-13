<?php

namespace App\Repository;

use App\Entity\ItemManufacturer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemManufacturer|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemManufacturer|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemManufacturer[]    findAll()
 * @method ItemManufacturer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemManufacturerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemManufacturer::class);
    }

    // /**
    //  * @return ItemManufacturer[] Returns an array of ItemManufacturer objects
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
    public function findOneBySomeField($value): ?ItemManufacturer
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
