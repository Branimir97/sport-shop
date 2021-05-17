<?php

namespace App\Repository;

use App\Entity\ItemColor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemColor|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemColor|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemColor[]    findAll()
 * @method ItemColor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemColorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemColor::class);
    }
}
