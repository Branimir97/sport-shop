<?php

namespace App\Repository;

use App\Entity\ColorTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ColorTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ColorTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ColorTranslation[]    findAll()
 * @method ColorTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ColorTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ColorTranslation::class);
    }

    // /**
    //  * @return ColorTranslation[] Returns an array of ColorTranslation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ColorTranslation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
