<?php

namespace App\Repository;

use App\Entity\PromoCodeUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PromoCodeUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromoCodeUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromoCodeUser[]    findAll()
 * @method PromoCodeUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromoCodeUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromoCodeUser::class);
    }
}
