<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findByCategories($categories)
    {
        return $this->createQueryBuilder('i')
            ->join('i.itemCategories', 'itemCategories')
            ->join('itemCategories.category', 'category')
            ->where('category.name IN (:categories)')
            ->setParameter('categories', $categories)
            ->join('i.images', 'images')
            ->join('i.itemColors', 'itemColors')
            ->join('itemColors.color', 'color')
            ->addSelect('images')
            ->addSelect('itemColors')
            ->addSelect('color')
            ->getQuery()
            ->getArrayResult();
    }

    public function searchByCipherAndName($keyword)
    {
        return $this->createQueryBuilder('i')
            ->where('i.title LIKE :title')
            ->setParameter('title', '%'.$keyword.'%')
            ->join('i.images', 'images')
            ->join('i.itemColors', 'itemColors')
            ->join('itemColors.color', 'color')
            ->addSelect('images')
            ->addSelect('itemColors')
            ->addSelect('color')
            ->getQuery()
            ->getArrayResult();
    }
}
