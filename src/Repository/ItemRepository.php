<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Translatable\TranslatableListener;

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

    public function findByCategoriesQuery($categories): QueryBuilder
    {
        $query = $this->createQueryBuilder('i');
        for($i=0; $i<count($categories); $i++) {
            $query
                ->join('i.itemCategories', 'itemCategories'.$i)
                ->join('itemCategories'.$i.'.category', 'category'.$i)
                ->andWhere('category'.$i.'.name = :category_'.$i)
                ->setParameter('category_'.$i, $categories[$i]);
        }
        $query
            ->leftJoin('i.images', 'img')
            ->addSelect('img')
            ->leftJoin('i.itemColors', 'itemColors')
            ->addSelect('itemColors')
            ->leftJoin('itemColors.color', 'color')
            ->addSelect('color')
            ->leftJoin('i.actionItem', 'ai')
            ->addSelect('ai');

        return $query;
    }

    public function getActionsOnItemsQuery(): QueryBuilder
    {
        $query = $this->createQueryBuilder('i');
        $query->join('i.actionItem', 'ai')
            ->where('ai IS NOT NULL')
            ->addSelect('ai');

        return $query;
    }

    public function getCategoryActionsQuery(): QueryBuilder
    {
        $query = $this->createQueryBuilder('i');
        $query->join('i.itemCategories', 'ic')
            ->addSelect('ic')
            ->join('ic.category', 'c')
            ->addSelect('c')
            ->join('c.actionCategory', 'ac')
            ->addSelect('ac')
            ->where('ac IS NOT NULL');
        return $query;
    }

    public function searchByCipherAndName($keyword)
    {
        return $this->createQueryBuilder('i')
            ->where('i.title LIKE :title')
            ->orWhere('i.cipher = :cipher')
            ->setParameter('title', '%'.$keyword.'%')
            ->setParameter('cipher', $keyword)
            ->leftJoin('i.images', 'images')
            ->leftJoin('i.itemColors', 'itemColors')
            ->leftJoin('itemColors.color', 'color')
            ->addSelect('images')
            ->addSelect('itemColors')
            ->addSelect('color')
            ->getQuery()
            ->getArrayResult();
    }
}
