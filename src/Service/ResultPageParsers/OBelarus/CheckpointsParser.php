<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Service\ResultPageParsers\Exception\ParseResultsException;
use App\Service\Utils\TextUtils;
use Symfony\Component\DomCrawler\Crawler;

abstract class CheckpointsParser
{
    const PROFILE = 'profile';
    const NAME = 'name';
    const NUMBER_PLATE = 'number-plate';
    const RACE_CODE = 'race-code';

    const CHECKPOINTS = 'checkpoints';
    const TOTAL_TIME = 'total-time';
    const PACE = 'pace';
    const DISTANCE = 'distance';
    const TIME = 'time';

    public function parseCheckpointsPage(string $html)
    {
        $checkpoints = [];
        $tables = (new Crawler($html))
            ->filterXPath('//div[@id=\'results-body\']/table/tr')
            ->each(fn(Crawler $node, $i) => $node->children('td pre')->html());

        foreach ($tables as $table) {
            $checkpoints[] = [
                self::PROFILE => $this->parseProfile($table),
                self::CHECKPOINTS => $this->parseCheckpoints($table),
            ];
        }

        return $checkpoints;
    }

    protected function parseProfile(string $table): array
    {
        $lines = TextUtils::splitLines($table);
        $profileData = [];

        foreach ($lines as $line) {
            if (empty($profileData[self::NAME]) && $name = $this->parseName($line)) {
                $profileData[self::NAME] = $name;
            }
            if (empty($profileData[self::NUMBER_PLATE]) && $numberPlate = $this->parseNumberPlate($line)) {
                $profileData[self::NUMBER_PLATE] = $numberPlate;
            }
            if (empty($profileData[self::RACE_CODE]) && $raceCode = $this->parseRaceCode($line)) {
                $profileData[self::RACE_CODE] = $raceCode;
            }

            if ($profileData[self::NAME] && $profileData[self::NUMBER_PLATE] && $profileData[self::RACE_CODE]) {
                break;
            }
        }

        return $profileData;
    }

    protected function parseRaceCode(string $line): string
    {
        return $this->parseValue('/^Группа:\s+([\w\d:]+)/i', $line);
    }

    protected function parseNumberPlate(string $line): string
    {
        return $this->parseValue('/^№\s+(\d+),/i', $line);
    }

    protected function parseName(string $line): string
    {
        return $this->parseValue('/^<strong>(.*)<\/strong>/i', $line);
    }

    protected function parseValue(string $pattern, string $string)
    {
        $value = null;
        if (preg_match($pattern, $string, $matches)) {
            $value = $matches[1];
        }

        return $value;
    }

    protected function getFirstAndLastLines(string $table): array
    {
        $lines = TextUtils::splitLines($table);
        $first = $last = null;

        foreach ($lines as $n => $line) {
            if ($lines[$n] !== '' && str_replace('-', '', $lines[$n]) === '') {
                if (is_null($first)) {
                    $first = $n + 1;
                } elseif (!is_null($first) && is_null($last)) {
                    $last = $n - 1;

                    return [$first, $last];
                }
            }
        }

        throw new ParseResultsException('Can\'t find the first and the last lines to parse checkpoints table!');
    }

    protected function parseCheckpoints(string $table): array
    {
        $lines = TextUtils::splitLines($table);

        list($first, $last) = $this->getFirstAndLastLines($table);
        $checkpoints = [];

        try {
            for ($n = $first; $n <= $last; $n++) {
                $checkpoints[] = $this->parseCheckpoint($lines[$n]);
            }
        } catch (ParseResultsException $e) {
            // TODO: error logging
            return [];
        }

        return $checkpoints;
    }
}
