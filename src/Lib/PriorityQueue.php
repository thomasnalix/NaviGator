<?php

namespace Navigator\Lib;

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

    public function insert(mixed $value, mixed $priority)
    {
        parent::insert($value, $priority);
    }

    public function extract() : mixed
    {
        return parent::extract();
    }

}