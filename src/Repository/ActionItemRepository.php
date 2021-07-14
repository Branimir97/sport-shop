<?php

namespace App\Repository;

use App\Entity\ActionItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActionItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActionItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActionItem[]    findAll()
 * @method ActionItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActionItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionItem::class);
    }
}
