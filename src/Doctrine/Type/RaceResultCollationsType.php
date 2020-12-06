<?php

namespace App\Doctrine\Type;

use App\Entity\ResultCollation;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

class RaceResultCollationsType extends JsonType
{
    const NAME = 'race_result_collations_type';

    const FIELD_PROFILE = 'p';
    const FIELD_RATIO = 'r';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $value = parent::convertToPHPValue($value, $platform);
        if (!is_array($value)) {
            $value = [];
        }

        return array_map(function (array $item) {
            if (empty($item[self::FIELD_RATIO]) || empty($item[self::FIELD_PROFILE])) {
                throw new InvalidArgumentException('Empty race collation values');
            }
            return (new ResultCollation())
                ->setProfileId($item[self::FIELD_PROFILE])
                ->setRatio($item[self::FIELD_RATIO]);

        }, $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $value = array_map(function (ResultCollation $collation) {
            if (empty($collation->getProfileId()) || empty($collation->getRatio())
            ) {
                throw new InvalidArgumentException('Empty race collation values');
            }

            return [
                self::FIELD_PROFILE => $collation->getProfileId(),
                self::FIELD_RATIO => $collation->getRatio(),
            ];
        }, $value);

        return parent::convertToDatabaseValue($value, $platform);
    }

    public function getName()
    {
        return self::NAME;
    }
}
