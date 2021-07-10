<?php

namespace App\Repository;

use App\Entity\ItemTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemTranslation[]    findAll()
 * @method ItemTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemTranslation::class);
    }
}
