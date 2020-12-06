<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\Race;
use App\Service\Rating\RatingCalculationException;
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
}
