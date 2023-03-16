<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\DataContainer;
use Exception;

class FibonacciHeap implements DataStructure {

    private array $nodes;

    private $min;
    private $size;

    public function __construct() {
        $this->min = null;
        $this->size = 0;
        $this->nodes = [];
    }

    public function insert(DataContainer $data) : void {
        $x = new FibonacciNode();
        $x->data = $data;
        $x->key = $data->getDistance();
        $x->left = $x;
        $x->right = $x;
        $this->min = $this->concatenate($x, $this->min);
        $this->size++;
        $this->nodes[$data->getGid()] = $data->getGid(); // on s'en fou de la valeur, pour avoir O(1) sur isset faut juste la clé
    }

    public function search($dataContainer) : bool {
        return isset($this->nodes[$dataContainer->getGid()]);
    }

    // Le nom serait plus "extractMin"
    public function getMinNode($ignored = null) : ?DataContainer {
        $minimum = $this->min;
        if ($minimum !== null) {
            if ($minimum->child !== null) {
                $current = $minimum->child;
                while (true) {
                    $current->parent = null;
                    $current = $current->right;
                    if ($current === $minimum->child)
                        break;
                }
                $this->min = $this->concatenate($this->min, $minimum->child);
            }

            if ($minimum->right === $minimum) {
                $this->min = null;
            } else {
                $this->min = $minimum->right;
                $this->remove($minimum);
                $this->consolidate();
            }
            $this->size--;
        }
        unset($this->nodes[$minimum->data->getGid()]);
        return $minimum->data;
    }

    public function isEmpty() : bool {
        return $this->size === 0;
    }

    private function concatenate($x, $y) {
        if ($x === null)
            return $y;
        else if ($y === null)
            return $x;

        $savingXRight = $x->right;
        $x->right = $y->right;
        $x->right->left = $x;
        $y->right = $savingXRight;
        $y->right->left = $y;

        if ($x->key < $y->key)
            return $x;
        else
            return $y;
    }

    private function remove(FibonacciNode $x) {
        $x->left->right = $x->right;
        $x->right->left = $x->left;
        $x->right = $x;
        $x->left = $x;
    }

    private function heapLink(FibonacciNode $x, FibonacciNode $y) {
        $this->remove($x);
        $this->concatenate($x, $y->child);
        $x->parent = $y;
        $y->child = $x;
        $y->degree++;
        $x->mark = false;
    }

    private function consolidate() {
        $A = [];
        $rootList = [];
        $start = $this->min;
        $current = $start;

        while (true) {
            $rootList[] = $current;
            if ($current->right === $start)
                break;
            $current = $current->right;
        }

        foreach ($rootList as $x) {
            $d = $x->degree;
            while (isset($A[$d])) {
                $y = $A[$d];
                if ($x->key > $y->key) {
                    $tmp = $x;
                    $x = $y;
                    $y = $tmp;
                }
                $this->heapLink($y, $x);
                $A[$d] = null;
                $d++;
            }
            $A[$d] = $x;
        }

        $this->min = null;
        foreach ($A as $a) {
            if ($a !== null) {
                $this->remove($a);
                $this->min = $this->concatenate($a, $this->min);
            }
        }
    }

    function delete(DataContainer $data): void
    {
        throw new Exception("Pas besoin de l'implémenter car inutile ici, on utilise uniquement extractMin()");
    }

}