<?php

namespace App\PlusCourtChemin\Lib;

class Node {

    public $parent = null;
    public $leftNode = null;
    public $rightNode = null;
    public $key;
    public $value;

    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }

}