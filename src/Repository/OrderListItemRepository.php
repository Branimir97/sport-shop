<?php

namespace App\Repository;

use App\Entity\OrderListItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderListItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderListItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderListItem[]    findAll()
 * @method OrderListItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderListItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderListItem::class);
    }
}
