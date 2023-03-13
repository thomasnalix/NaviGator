<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\DataContainer;

class Node
{
    public DataContainer $data;
    public ?Node $leftChild;
    public ?Node $rightChild;

    function __construct($data)
    {
        $this->data = $data;
        $this->leftChild = null;
        $this->rightChild = null;
    }
}