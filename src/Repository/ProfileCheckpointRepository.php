<?php

namespace App\Repository;

use App\Entity\ProfileCheckpoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfileCheckpoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfileCheckpoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfileCheckpoint[]    findAll()
 * @method ProfileCheckpoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileCheckpointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfileCheckpoint::class);
    }

    // /**
    //  * @return ProfileCheckpoint[] Returns an array of ProfileCheckpoint objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProfileCheckpoint
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
