<?php

namespace App\PlusCourtChemin\Lib;

class BinarySearchTree
{

    private ?Node $root;
    private int $size = 1;

    public function __construct(Node $root) {
        $this->root = $root;
    }

    public function insert(Node $node): void {
        if ($this->root === null) {
            $this->root = $node;
            $this->size++;
        } else {
            $this->insertNode($this->root, $node);
        }
    }

    private function insertNode(Node $root, Node $node): void {
        if ($node->value < $root->value) {
            if ($root->leftNode === null) {
                $root->leftNode = $node;
                $node->parent = $root;
                $this->size++;
            } else {
                $this->insertNode($root->leftNode, $node);
            }
        } else {
            if ($root->rightNode === null) {
                $root->rightNode = $node;
                $node->parent = $root;
                $this->size++;
            } else {
                $this->insertNode($root->rightNode, $node);
            }
        }
    }

    public function searchMin(): ?Node {
        return $this->searchMinNode($this->root);
    }

    private function searchMinNode(Node $root): ?Node {

        if ($root->leftNode === null) {
            return $root;
        }

        return $this->searchMinNode($root->leftNode);
    }

    public function exists(int $value): bool {
        return $this->getNodeByKey($this->root, $value) !== null;
    }

    public function getByKey(int $key): ?Node {
        return $this->getNodeByKey($this->root, $key);
    }

    private function getNodeByKey(Node $root, int $key): ?Node {
        if ($root->key === $key) {
            return $root;
        }

        if ($key < $root->value) {
            if ($root->leftNode === null) {
                return null;
            }
            return $this->getNodeByKey($root->leftNode, $key);
        } else {
            if ($root->rightNode === null) {
                return null;
            }
            return $this->getNodeByKey($root->rightNode, $key);
        }
    }

    public function delete(int $key): void {
        $this->deleteNode($this->root, $this->getByKey($key));
    }

    private function deleteNode(Node $root, Node $targetNode): void {
        if ($targetNode->leftNode === null && $targetNode->rightNode === null) {
            if ($targetNode->parent === null) {
                $this->root = null;
            } else if ($targetNode->parent->leftNode === $targetNode) {
                $targetNode->parent->leftNode = null;
            } else {
                $targetNode->parent->rightNode = null;
            }
        } else if ($targetNode->leftNode === null) {
            if ($targetNode->parent === null) {
                $this->root = $targetNode->rightNode;
            } else if ($targetNode->parent->leftNode === $targetNode) {
                $targetNode->parent->leftNode = $targetNode->rightNode;
            } else {
                $targetNode->parent->rightNode = $targetNode->rightNode;
            }
        } else if ($targetNode->rightNode === null) {
            if ($targetNode->parent === null) {
                $this->root = $targetNode->leftNode;
            } else if ($targetNode->parent->leftNode === $targetNode) {
                $targetNode->parent->leftNode = $targetNode->leftNode;
            } else {
                $targetNode->parent->rightNode = $targetNode->leftNode;
            }
        } else {
            $minNode = $this->searchMinNode($targetNode->rightNode);
            $targetNode->value = $minNode->value;
            $this->deleteNode($root, $minNode);
        }
    }


    public function size(): int {
        return $this->size;
    }

}