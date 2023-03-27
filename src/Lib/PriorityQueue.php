<?php

namespace Navigator\Lib;

use SplPriorityQueue;

class PriorityQueue extends SplPriorityQueue
{

    private array $visited = [];

    public function compare(mixed $priority1, mixed $priority2) : int
    {
        if ($priority1 === $priority2) {
            return 0;
        }
        return $priority1 < $priority2 ? 1 : -1;
    }

    public function contains($value) : bool
    {
        return isset($this->visited[$value]);
    }

    public function insert(mixed $value, mixed $priority)
    {
        parent::insert($value, $priority);
        $this->visited[$value] = true;
    }

    public function extract() : mixed
    {
        $value = parent::extract();
        unset($this->visited[$value]);
        return $value;
    }

}