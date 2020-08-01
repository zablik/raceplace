<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Service\ResultPageParsers\Exception\ParseResultsException;

/**
 * Class Kupalle20200705CheckpointsParser
 * @package App\Service\ResultPageParsers\OBelarus
 */
class Kupalle20200705CheckpointsParser extends CheckpointsParser
{
    protected function parseCheckpoint(string $line): array
    {
        if (!preg_match('/^\s*([\d,]+)\sкм\s+([\d:]+)\s+([\d:]+)\s+([\d:]+)\/км/', $line, $matches)) {
            throw new ParseResultsException(sprintf('Can\'t parse checkpoint row "%s"', $line));
        }

        return [
            self::DISTANCE => $matches[1],
            self::TOTAL_TIME => $matches[2],
            self::TIME => $matches[3],
            self::PACE => $matches[4],
        ];
    }
}