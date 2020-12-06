<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Race;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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

    #region Command processing

    private function getIterationQueryBuilder(array $raceIds = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r');

        if ($raceIds) {
            $qb->where('r.id in (:ids)')
                ->setParameters([':ids' => $raceIds]);
        }

        return $qb;
    }

    public function getIterationFindQuery(array $raceIds = null): Query
    {
        return $this->getIterationQueryBuilder($raceIds)->getQuery();
    }

    /**
     * @param int[]|null $raceIds
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getIterationCount(array $raceIds = null): int
    {
        return $this->getIterationQueryBuilder($raceIds)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    #endregion Command processing

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

    public function findWithCheckpoints(Event $event, string $type)
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.checkpoints', 'c')
            ->andWhere('r.event = :event')
            ->andWhere('r.type = :type')
            ->setParameters([
                'event' => $event,
                'type' => $type,
            ]);

        return $qb->getQuery()->getResult();
    }

    public function findByFilter(string $type, float $minDistance, float $maxDistance, \DateTime $from, \DateTime $till)
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.event', 'e')
            ->andWhere('e.date > :from')
            ->andWhere('e.date < :till')
            ->andWhere('r.type = :type')
            ->andWhere('r.distance > :minDistance')
            ->andWhere('r.distance < :maxDistance')
            ->setParameters([
                'from' => $from,
                'till' => $till,
                'type' => $type,
                'minDistance' => $minDistance,
                'maxDistance' => $maxDistance,
            ]);

        return $qb->getQuery()->getResult();
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
