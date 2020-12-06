<?php

namespace App\Service\Rating;

class Collation
{
    public int $id;
    public int $nid;
    public float $ratio;

    public function __construct(int $id, int $nid, float $ratio)
    {
        $this->id = $id;
        $this->nid = $nid;
        $this->ratio = $ratio;
    }

    public function invert()
    {
        $a = $this->id;
        $this->id = $this->nid;
        $this->nid = $a;

        $this->ratio = 1 / $this->ratio;
    }
}
