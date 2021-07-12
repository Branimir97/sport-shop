<?php

namespace App\Repository;

use App\Entity\UserSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserSearch|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSearch|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSearch[]    findAll()
 * @method UserSearch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSearch::class);
    }
}
