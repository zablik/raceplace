<?php

namespace App\Entity\Extension;

use Doctrine\Common\Collections\ArrayCollection;

class AppArrayCollection extends ArrayCollection
{
    /**
     * @param array $collection
     * @param callable $compare
     */
    public function import($collection, callable $compare)
    {
        $toDelete = array_udiff($this->getValues(), $collection, $compare);
        $toAdd = array_udiff($collection, $this->getValues(), $compare);

        foreach ($toDelete as $toDeleteItem) {
            $this->removeElement($toDeleteItem);
        }

        foreach ($toAdd as $toAddItem) {
            $this->add($toAddItem);
        }
    }

    public function exiting($collection, callable $compare)
    {
        return array_uintersect($this->getValues(), $collection, $compare);
    }
}
