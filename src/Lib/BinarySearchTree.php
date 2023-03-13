<?php

namespace App\PlusCourtChemin\Lib;

class BinarySearchTree
{

    public $root;

    public function __construct($key, $value)
    {
        $this->root = new Node($key, $value);
    }

    public function search($value)
    {
        $node = $this->root;

        while($node) {
            if ($value > $node->value) {
                $node = $node->right;
            } elseif ($value < $node->value) {
                $node = $node->left;
            } else {
                break;
            }
        }

        return $node;
    }

    public function insert($key, $value)
    {
        $node = $this->root;
        if (!$node) {
            return $this->root = new Node($key, $value);
        }

        while($node) {
            if ($value > $node->value) {
                if ($node->right) {
                    $node = $node->right;
                } else {
                    $node = $node->right = new Node($key, $value, $node);
                    break;
                }
            } elseif ($value < $node->value) {
                if ($node->left) {
                    $node = $node->left;
                } else {
                    $node = $node->left = new Node($value, $value, $node);
                    break;
                }
            } else {
                break;
            }
        }
        return $node;
    }

    public function delete($value)
    {
        $node = $this->search($value);
        if ($node) {
            $node->delete();
        }
    }

    public function isEmpty()
    {
        return $this->root === null;
    }

}