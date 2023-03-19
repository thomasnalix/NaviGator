<?php

namespace App\PlusCourtChemin\Lib;

use SplPriorityQueue;

class PriorityQueue extends SplPriorityQueue
{

    public function compare(mixed $priority1, mixed $priority2) : int
    {
        if ($priority1 === $priority2) {
            return 0;
        }
        return $priority1 < $priority2 ? 1 : -1;
    }

    public function contains($data) : bool
    {
        foreach ($this as $item) {
            if ($item === $data) {
                return true;
            }
        }
        return false;
    }

}