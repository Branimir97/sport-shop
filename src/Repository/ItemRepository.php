<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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

    public function findByCategories($categories, $locale)
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
            ->addSelect('color');

        return $query->getQuery()
            ->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
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
