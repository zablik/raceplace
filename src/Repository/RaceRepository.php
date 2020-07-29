<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Race;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Race|null find($id, $lockMode = null, $lockVersion = null)
 * @method Race|null findOneBy(array $criteria, array $orderBy = null)
 * @method Race[]    findAll()
 * @method Race[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Race::class);
    }

    public function findWithResults(Event $event, string $type, float $distance, string $group = null)
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.profileResults', 'pr')
            ->leftJoin('pr.profile', 'p', null)
            ->leftJoin('r.checkpoints', 'c')
            ->leftJoin('pr.checkpoints', 'pc')
            ->andWhere('r.event = :event')
            ->andWhere('r.type = :type')
            ->andWhere('r.distance = :distance')
            ->setParameters([
                'event' => $event,
                'type' => $type,
                'distance' => $distance,
            ]);

        if ($group) {
            $qb->andWhere('p.group = :group')->setParameter('group', $group);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    // /**
    //  * @return Race[] Returns an array of Race objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Race
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
