<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\DataContainer;

class FibonacciNode {

    public $left;
    public $right;
    public $parent;
    public $child;
    public $degree;
    public $mark;
    public float $key;
    public ?DataContainer $data;

    public function __construct() {
        $this->left = null;
        $this->right = null;
        $this->parent = null;
        $this->child = null;
        $this->degree = null;
        $this->mark = null;
        $this->data = null;
        $this->key = 0;
    }

}