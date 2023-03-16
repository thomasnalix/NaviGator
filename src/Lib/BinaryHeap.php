<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\DataContainer;

class BinaryHeap implements DataStructure { // null
    private $heap = array();
    private $count = 0;

    public function insert(DataContainer $data) : void {
        $this->heap[++$this->count] = $data;
        $this->percolateUp($this->count);
    }

    public function search(DataContainer $data) : bool {
        for ($i = 1; $i <= $this->count; $i++) {
            if ($this->heap[$i] == $data) {
                return true;
            }
        }
        return false;
    }

    public function delete(DataContainer $data) : void {
        for ($i = 1; $i <= $this->count; $i++) {
            if ($this->heap[$i] == $data) {
                $this->heap[$i] = $this->heap[$this->count];
                unset($this->heap[$this->count]);
                $this->count--;
                $this->percolateDown($i);
                break;
            }
        }
    }

    public function getMinNode($current = null) : ?DataContainer {
        if ($this->isEmpty()) {
            return null;
        }

        if ($current === null) {
            $current = 1;
        }

        $left = $current * 2;
        $right = $current * 2 + 1;

        $minNode = $current;

        if (isset($this->heap[$left]) && $this->heap[$left]->getDistance() < $this->heap[$minNode]->getDistance()) {
            $minNode = $left;
        }

        if (isset($this->heap[$right]) && $this->heap[$right]->getDistance() < $this->heap[$minNode]->getDistance()) {
            $minNode = $right;
        }

        if ($minNode !== $current) {
            $this->swap($current, $minNode);
            $this->getMinNode($minNode);
        }

        return $this->heap[1];
    }

    public function isEmpty() : bool{
        return ($this->count === 0);
    }

    private function swap($i, $j){
        $tmp = $this->heap[$i];
        $this->heap[$i] = $this->heap[$j];
        $this->heap[$j] = $tmp;
    }

    private function percolateUp($i) {
        $parent = floor($i / 2);
        if ($i <= 1 || $this->heap[$i]->getDistance() >= $this->heap[$parent]->getDistance()) {
            return;
        }
        $this->swap($i, $parent);
        $this->percolateUp($parent);
    }

    private function percolateDown($i) {
        $left = $i * 2;
        $right = $i * 2 + 1;
        $min = $i;

        if ($left <= $this->count && $this->heap[$left]->getDistance() < $this->heap[$min]->getDistance()) {
            $min = $left;
        }

        if ($right <= $this->count && $this->heap[$right]->getDistance() < $this->heap[$min]->getDistance()) {
            $min = $right;
        }

        if ($min !== $i) {
            $this->swap($i, $min);
            $this->percolateDown($min);
        }
    }
}