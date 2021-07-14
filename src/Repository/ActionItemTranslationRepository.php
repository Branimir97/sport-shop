<?php

namespace App\Repository;

use App\Entity\ActionItemTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActionItemTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionItemTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionItemTranslation[]    findAll()
 * @method ActionItemTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActionItemTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionItemTranslation::class);
    }
}
