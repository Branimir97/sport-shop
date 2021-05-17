<?php

namespace App\Repository;

use App\Entity\LoyaltyCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LoyaltyCard|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoyaltyCard|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoyaltyCard[]    findAll()
 * @method LoyaltyCard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoyaltyCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoyaltyCard::class);
    }
}
