<?php

namespace App\Repository;

use App\Entity\ItemTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemTag[]    findAll()
 * @method ItemTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemTag::class);
    }
}
