<?php

namespace App\Repository;

use App\Entity\ProfileResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfileResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfileResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfileResult[]    findAll()
 * @method ProfileResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfileResult::class);
    }

    // /**
    //  * @return ProfileResult[] Returns an array of ProfileResult objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProfileResult
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
