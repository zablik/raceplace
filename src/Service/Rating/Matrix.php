<?php

namespace App\Service\Rating;

use App\Service\Utils\ArrayUtils;

class Matrix
{
    private array $matrix = [];
    private array $normalizeIds = [];
    private array $ids = [];

    const MAX_LEVEL = 3;

    public function setNormalizeIds(array $normalizeIds)
    {
        $this->normalizeIds = array_combine(
            $normalizeIds,
            array_fill(1, count($normalizeIds), true)
        );
    }

    private function getNormalizeIds(): array
    {
        return array_keys($this->normalizeIds);
    }

    private function initCell($id, $nid): Cell
    {
        return $this->matrix[$id][$nid] = new Cell();
    }

    private function generateCollation(int $id1, int $id2, float $ratio): Collation
    {
        return new Collation($id1, $id2, $ratio);
    }

    public function addCollation(int $id1, int $id2, int $raceId, float $ratio): void
    {
        if ($id1 === $id2) {
            return;
        }

        $collation = $this->generateCollation($id1, $id2, $ratio);

        if (!$this->canNormalizeOnId($collation->nid) && !$this->canNormalizeOnId($collation->id)) {
            throw new RatingCalculationException('None of IDs are normalising');
        }

        if (!$this->canNormalizeOnId($collation->nid) && $this->canNormalizeOnId($collation->id)) {
            $t = microtime(1);
            $collation->invert();
        }

        $cell = $this->getCell($collation->id, $collation->nid);

        if (!$cell->hasRatio($raceId)) {
            $cell->addCollation($collation->ratio, $raceId);
            $this->addIds($collation->id, $collation->nid);
        }
    }

    private function canNormalizeOnId(int $id): bool
    {
        return isset($this->normalizeIds[$id]) && $this->normalizeIds[$id] === true;
    }

    private function getCell(int $id1, int $id2): Cell
    {
        return $this->matrix[$id1][$id2] ?? $this->initCell($id1, $id2);
    }

    private function addIds(...$ids): void
    {
        foreach ($ids as $id) {
            $this->ids[$id] = true;
        }
    }

    public function getIds(): array
    {
        ksort($this->ids);

        return array_keys($this->ids);
    }

    public function completeMissing(int $level = 0): void
    {
        $runNextLevel = false;
        $normalizeIds = $this->getNormalizeIds();
        foreach ($this->matrix as $id => $row) {
            foreach ($normalizeIds as $normId) {
                if (!isset($row[$normId])) {
                    $collationIdsToCompare = $this->getCollationIdsToCompare($id, $normId, $level);
                    if ($collationIdsToCompare) {
                        $ratio = $this->estimateRatioByNeighbors($id, $normId, $collationIdsToCompare);
                        $this->fillMissingCollation($id, $normId, $ratio, $level + 1);
                    } else {
                        $runNextLevel = true;
                    }
                }
            }
        }

        if ($runNextLevel) {
            $this->completeMissing($level + 1);
        }
    }

    public function getRating(): array
    {
        $rating = [];
        foreach ($this->matrix as $id => $cells) {
            $ratios = array_map(fn(Cell $cell) => $cell->getRatio(), $cells);
            $rating[$id] = ArrayUtils::avg($ratios);
        }

        arsort($rating);

        return $rating;
    }

    private function fillMissingCollation(int $id, int $normId, float $ratio, int $level): void
    {
        $cell = $this->getCell($id, $normId);
        $cell->addCollation($ratio, null, $level);
    }

    private function getCollationIdsToCompare(int $id1, int $id2, int $level): array
    {
        $collationIdsForId = $this->getCollationIdsForId($id1, $level);
        $collationIdsForNormId = $this->getCollationIdsForId($id2, $level);

        return array_intersect($collationIdsForId, $collationIdsForNormId);
    }

    private function getCollationIdsForId(int $id, int $maxLevel = 0): array
    {
        $ids = [];
        /** @var Cell $cell */
        foreach ($this->matrix[$id] as $nid => $cell) {
            if ($cell->getLevel() <= $maxLevel) {
                $ids[] = $nid;
            }
        }

        return $ids;
    }

    private function estimateRatioByNeighbors(int $id, int $normId, array $collationIdsToCompare): float
    {
        $ratios = [];

        foreach ($collationIdsToCompare as $collId) {
            $idColl = $this->matrix[$id][$collId]->getRatio();
            $normIdColl = $this->matrix[$normId][$collId]->getRatio();

            $ratios[] = $idColl / $normIdColl;
        }

        return array_sum($ratios) / count($ratios);
    }
}
