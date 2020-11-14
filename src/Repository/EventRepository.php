<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param string $slug
     * @return Event|null
     * @throws NonUniqueResultException
     */
    public function findWithRaces(string $slug): ?Event
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.races', 'r')
            ->leftJoin('r.checkpoints', 'c')
            ->where('e.slug = :eventSlug')
            ->setParameter('eventSlug', $slug);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getAllQueryQB()
    {
        $qb = $this->createQueryBuilder('event')
            ->leftJoin('event.races', 'races')
            ->orderBy('event.date', 'DESC');

        return $qb;
    }

    // /**
    //  * @return Event[] Returns an array of Event objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
