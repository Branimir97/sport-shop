<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
            ->addSelect('ai')
            ->orderBy('i.id', 'DESC');

        return $query;
    }

    public function getActionItemsQuery(): QueryBuilder
    {
        $query = $this->createQueryBuilder('i');
        $query
            ->leftJoin('i.itemCategories', 'ic')
            ->addSelect('ic')
            ->join('ic.category', 'c')
            ->addSelect('c')
            ->leftJoin('c.actionCategory', 'ac')
            ->where('ac IS NOT NULL')
            ->leftJoin('i.actionItem', 'ai')
            ->orWhere('ai IS NOT NULL');
        return $query;
    }

    public function searchByCipherAndName($keyword, $locale): QueryBuilder
    {
        $query = $this->createQueryBuilder('i');
        if($locale == 'en') {
            $query
                ->join('i.itemTranslations', 'it')
                ->where('it.field = :field')
                ->andWhere('it.content LIKE :title')
                ->orWhere('i.cipher = :cipher')
                ->setParameter('field', 'title')
                ->setParameter('title', '%'.$keyword.'%')
                ->setParameter('cipher', $keyword);
        }
        if($locale == 'hr') {
            $query
                ->where('i.title LIKE :title')
                ->orWhere('i.cipher = :cipher')
                ->setParameter('title', '%'.$keyword.'%')
                ->setParameter('cipher', $keyword);
        }

        $query
            ->leftJoin('i.images', 'images')
            ->leftJoin('i.itemColors', 'itemColors')
            ->leftJoin('itemColors.color', 'color')
            ->leftJoin('i.actionItem', 'ai')
            ->addSelect('images')
            ->addSelect('itemColors')
            ->addSelect('color')
            ->addSelect('ai')
            ->orderBy('i.id', 'DESC');
        return $query;
    }

    public function findSuggestedItems($gender, $userSearch)
    {
        $query = $this->createQueryBuilder('i')
            ->select('i.id')
            ->join('i.itemCategories', 'ic')
            ->join('ic.category', 'c');
        if($gender !== null) {
            if($gender == "Muški") {
                $query
                    ->where('c.name = :categoryMen')
                    ->setParameter('categoryMen', "Muškarci");
            } else if($gender == "Ženski") {
                $query
                    ->where('c.name = :categoryWomen')
                    ->setParameter('categoryWomen', "Žene");
            }
        }

        if($userSearch !== null) {
            $query
                ->orWhere('i.title LIKE :title')
                ->setParameter('title', '%'.$userSearch->getKeyword().'%');
        }

        else {
            return null;
        }

        return $query->getQuery()->getArrayResult();
    }
}
