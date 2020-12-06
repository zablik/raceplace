<?php

namespace App\Service\Rating;

class Cell
{
    private float $ratio = 1;
    private int $count = 0;
    private array $races = [];
    private int $level = 0;

    public function getRatio(): float
    {
        return $this->ratio;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function addCollation(float $ratio, int $raceId = null, int $level = 0): void
    {
        $this->races[$raceId] = true;
        $this->ratio = ($this->ratio * $this->count + $ratio) / ++$this->count;
        $this->level = $level;
    }

    public function hasRatio(int $raceId): bool
    {
        return isset($this->races[$raceId]) && $this->races[$raceId] === true;
    }
}
