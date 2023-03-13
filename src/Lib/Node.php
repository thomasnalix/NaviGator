<?php

namespace App\PlusCourtChemin\Lib;

class Node {

    public ?Node $parent = null;
    public ?Node $leftNode = null;
    public ?Node $rightNode = null;
    public int $key;
    public float $value;

    public function __construct(int $key, float $value) {
        $this->key = $key;
        $this->value = $value;
    }

}