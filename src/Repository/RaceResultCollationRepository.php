<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\Race;
use App\Entity\RaceResultCollation;
use App\Service\Rating\RatingCalculationException;
use App\Service\Utils\CollectionUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RaceResultCollation|null find($id, $lockMode = null, $lockVersion = null)
 * @method RaceResultCollation|null findOneBy(array $criteria, array $orderBy = null)
 * @method RaceResultCollation[]    findAll()
 * @method RaceResultCollation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RaceResultCollationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RaceResultCollation::class);
    }

    public function findByRaces(array $races)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.race IN (:races)')
            ->setParameter('races', $races)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function findByRace(Race $race)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.race = :race')
            ->setParameter('race', $race)
            ->getQuery()
            ->getResult();
    }

    public function findByProfiles(Profile $profile)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.profile = :profile')
            ->setParameter('profile', $profile)
            ->getQuery()
            ->getResult();
    }

    public function getProfileFrequencies(array $races)
    {
        if (count($races) === 1) {
            throw new RatingCalculationException("Can't normalize profiles for a single race");
        }

        $sql = "SELECT rrc.profile_id, COUNT(DISTINCT rrc.race_id) AS total
		FROM `race_result_collation` rrc
		WHERE rrc.race_id IN (?)
		GROUP BY rrc.profile_id
		ORDER BY total DESC, rrc.profile_id ASC";

        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            ['1' => CollectionUtils::entityColumn($races, 'id')],
            [Connection::PARAM_INT_ARRAY]
        );
    }

    public function getMissingRaceIdsForNormalizeProfiles(array $races, array $profileIds)
    {
        $sql = 'SELECT rrc.race_id
		FROM `race_result_collation` rrc
		LEFT JOIN `race_result_collation` rrc2 ON rrc.race_id = rrc2.race_id AND rrc2.profile_id IN (?)
		WHERE rrc.race_id IN (?) AND rrc2.profile_id IS NULL
		GROUP BY rrc.race_id';

        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            $sql,
            [
                '1' => $profileIds,
                '2' => CollectionUtils::entityColumn($races, 'id'),
            ],
            [
                Connection::PARAM_INT_ARRAY,
                Connection::PARAM_INT_ARRAY,
            ]
        );
    }

    public function findArrayForRace(Race $race)
    {
        $em = $this->getEntityManager();
        $tableName = $em->getClassMetadata(RaceResultCollation::class)->getTableName();
        $raceField = $em->getClassMetadata(RaceResultCollation::class)->getSingleAssociationJoinColumnName('race');

        $sql = "SELECT * from `{$tableName}` WHERE {$raceField} = ?";

        return $em->getConnection()->fetchAllAssociative($sql, ['1' => $race->getId()]);
    }

    public function findArrayForRacesAndProfiles(array $races, array $profileIds = [])
    {
        $em = $this->getEntityManager();
        $tableName = $em->getClassMetadata(RaceResultCollation::class)->getTableName();
        $raceField = $em->getClassMetadata(RaceResultCollation::class)->getSingleAssociationJoinColumnName('race');
        $profileField = $em->getClassMetadata(RaceResultCollation::class)->getSingleAssociationJoinColumnName('profile');

        $sql = "SELECT * from `{$tableName}` WHERE {$raceField} IN (?)";
        $params = ['1' => CollectionUtils::entityColumn($races, 'id')];

        if ($profileIds) {
            $sql .= " AND {$profileField} IN (?)";
            $params['2'] = $profileIds;
        }

        return $em->getConnection()->fetchAllAssociative($sql, $params, [Connection::PARAM_INT_ARRAY, Connection::PARAM_INT_ARRAY]);
    }

    public function findArrayForRacesGenerator(array $races)
    {
        foreach ($races as $race) {
            yield $this->findArrayForRace($race);
        }
    }
}
