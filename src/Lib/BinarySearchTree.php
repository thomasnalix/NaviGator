<?php

namespace App\PlusCourtChemin\Lib;

use mysql_xdevapi\BaseResult;

class BinarySearchTree
{

    private ?Node $root;
    private int $size = 0;

    public function __construct(Node $root = null)
    {
        $this->root = $root;
        if ($root !== null) $this->size++;
    }

    public function insert(int $key, float $value): void {
        $this->insertNode($this->root, new Node($key, $value));
    }

    private function insertNode(?Node $root, Node $node): void {
        if ($root === null) {
            $this->root = $node;
            $this->size++;
            return;
        }
        if ($node->value < $root->value) {
            if ($root->leftNode === null) {
                $root->leftNode = $node;
                $this->size++;
            } else {
                $this->insertNode($root->leftNode, $node);
            }
        } else {
            if ($root->rightNode === null) {
                $root->rightNode = $node;
                $this->size++;
            } else {
                $this->insertNode($root->rightNode, $node);
            }
        }
    }

    public function searchMin(): ?Node {
        if ($this->root === null) return null;
        return $this->searchMinNode($this->root);
    }

    private function searchMinNode(Node $root): ?Node {
        if ($root->leftNode === null) return $root;
        return $this->searchMinNode($root->leftNode);
    }

    public function exists(int $key, float $value): bool {
        if ($this->root === null) return false;
        return $this->getNodeByKeyValue($this->root, $key, $value) !== null;
    }

    private function getNodeByKeyValue(?Node $root, int $key, float $value): ?Node {
        if ($root === null) return null;
        if ($root->key === $key) return $root;
        if ($value < $root->value) {
            return $this->getNodeByKeyValue($root->leftNode, $key, $value);
        } else {
            return $this->getNodeByKeyValue($root->rightNode, $key, $value);
        }
    }

    public function delete(int $key, float $value): void {
        if ($this->root === null) return;
        $this->deleteNode($this->root, $key, $value);
    }

    private function deleteNode(Node $root, int $key, float $value): void {
        if ($value < $root->value) {
            if ($root->leftNode === null) return;
            if ($root->leftNode->key === $key) {
                $root->leftNode = null;
                $this->size--;
            } else {
                $this->deleteNode($root->leftNode, $key, $value);
            }
        } else if ($value > $root->value) {
            if ($root->rightNode === null) return;
            if ($root->rightNode->key === $key) {
                $root->rightNode = null;
                $this->size--;
            } else {
                $this->deleteNode($root->rightNode, $key, $value);
            }
        } else {
            if ($root->leftNode === null && $root->rightNode === null) {
                $this->root = null;
                $this->size--;
            } else if ($root->leftNode === null) {
                $this->root = $root->rightNode;
                $this->size--;
            } else if ($root->rightNode === null) {
                $this->root = $root->leftNode;
                $this->size--;
            } else {
                $minNode = $this->searchMinNode($root->rightNode);
                $this->deleteNode($root, $minNode->key, $minNode->value);
                $root->key = $minNode->key;
                $root->value = $minNode->value;
            }
        }
    }


    public function size(): int {
        return $this->size;
    }

}