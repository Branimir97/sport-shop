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
}
