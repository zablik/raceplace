<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\Race;
use App\Service\Rating\RatingCalculationException;
use App\Service\Utils\ArrayUtils;
use App\Service\Utils\CollectionUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Profile|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profile|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profile[]    findAll()
 * @method Profile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    public function getAllQueryQB()
    {
        return $this->createQueryBuilder('profile')
            ->leftJoin('profile.user', 'user')
            ->orderBy('profile.name', 'ASC');
    }

    public function getForRating(array $ids, array $races)
    {
        return $this->createQueryBuilder('profile', 'profile.id')
            ->select('profile, results, race')
            ->leftJoin('profile.results', 'results')
            ->leftJoin('results.race', 'race')
            ->andWhere('profile.id IN (:ids)')
            ->andWhere('results.race IN (:raceIds)')
            ->setParameters([
                'ids' => $ids,
                'raceIds' => CollectionUtils::entityColumn($races, 'id')
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
