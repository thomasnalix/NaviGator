<?php

namespace App\PlusCourtChemin\Lib;

class Node
{
    public ?Node $parent;
    public ?Node $left = null;
    public ?Node $right = null;
    public int $key;
    public float $value;

    public function __construct($key, $value, $parent = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->parent = $parent;
    }

    public function min()
    {
        $node = $this;
        while ($node->left) {
            if (!$node->left) break;
            $node = $node->left;
        }
        return $node;
    }

    public function delete()
    {
        if ($this->left && $this->right) {
            $min = $this->right->min();
            $this->value = $min->value;
            $min->delete();
        } elseif ($this->right) {
            if ($this->parent->left === $this) {
                $this->parent->left = $this->right;
                $this->right->parent = $this->parent->left;
            } elseif ($this->parent->right === $this) {
                $this->parent->right = $this->right;
                $this->right->parent = $this->parent->right;
            }
            $this->parent = null;
            $this->right   = null;
        } elseif ($this->left) {
            if ($this->parent->left === $this) {
                $this->parent->left = $this->left;
                $this->left->parent = $this->parent->left;
            } elseif ($this->parent->right === $this) {
                $this->parent->right = $this->left;
                $this->left->parent = $this->parent->right;
            }
            $this->parent = null;
            $this->left   = null;
        } else {
            if ($this->parent->right === $this) {
                $this->parent->right = null;
            } elseif ($this->parent->left === $this) {
                $this->parent->left = null;
            }
            $this->parent = null;
        }
    }

}