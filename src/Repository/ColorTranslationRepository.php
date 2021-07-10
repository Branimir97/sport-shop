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
}
