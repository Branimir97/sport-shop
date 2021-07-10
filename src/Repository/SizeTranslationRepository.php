<?php

namespace App\Repository;

use App\Entity\SizeTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SizeTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SizeTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method SizeTranslation[]    findAll()
 * @method SizeTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SizeTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SizeTranslation::class);
    }
}
