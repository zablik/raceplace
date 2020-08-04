<?php

namespace App\Service\Utils;

use Doctrine\Common\Collections\Collection;

class CollectionUtils
{
    public static function importCollection(
        $owner,
        array $existing,
        array $input,
        string $name,
        callable $compare,
        array $fieldsToSet = []
    ) {
        $toKeep = self::arrayIntersect($existing, $input, $compare);
        $toDelete = self::arrayDiff($existing, $input, $compare);
        $toAdd = self::arrayDiff($input, $existing, $compare);

        foreach ($toDelete as $deleting) {
            $owner->{'remove' . $name}($deleting);
        }

        foreach ($toAdd as $adding) {
            $owner->{'add' . $name}($adding);
        }

        foreach ($toKeep as $kept) {
            foreach ($input as $item) {
                if ($compare($kept, $item)) {
                    foreach ($fieldsToSet as $field) {
                        $kept->{'set' . ucfirst($field)}($item->{'get' . ucfirst($field)}());
                    }

                    break;
                }
            }
        }

        return $toKeep;
    }

    public static function arrayIntersect(array $arr1, array $arr2, callable $compare)
    {
        return array_filter($arr1, function ($arr1Item) use ($arr2, $compare) {
            foreach ($arr2 as $arr2Item) {
                if ($compare($arr1Item, $arr2Item)) {
                    return true;
                }
            }

            return false;
        });
    }

    public static function arrayDiff(array $arr1, array $arr2, callable $compare)
    {
        return array_filter($arr1, function ($arr1Item) use ($arr2, $compare) {
            foreach ($arr2 as $arr2Item) {
                if ($compare($arr1Item, $arr2Item)) {
                    return false;
                }
            }

            return true;
        });
    }
}
