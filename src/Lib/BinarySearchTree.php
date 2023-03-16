<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\DataContainer;

class BinarySearchTree implements DataStructure {


    private array $nodes = [];

    public ?Node $root;

    function __construct() {
        $this->root = null;
    }

    function insert(DataContainer $data) : void {
        $newNode = new Node($data);
        $this->nodes[$data->getGid()] = $newNode;
        if ($this->root == null) {
            $this->root = $newNode;
            return;
        }

        $current = $this->root;

        while (true) {
            if ($data->getDistance() < $current->data->getDistance()) {
                if ($current->leftChild == null) {
                    $current->leftChild = $newNode;
                    break;
                } else {
                    $current = $current->leftChild;
                }
            } else {
                if ($current->rightChild == null) {
                    $current->rightChild = $newNode;
                    break;
                } else {
                    $current = $current->rightChild;
                }
            }
        }
    }

    function search(DataContainer $data) : bool {
        return isset($this->nodes[$data->getGid()]);
    }

    function delete(DataContainer $data) : void {
        $this->root = $this->deleteNode($this->root, $data);
        $this->nodes[$data->getGid()] = null;
    }

    function deleteNode(?Node $node, DataContainer $data) {
        if ($node == null) {
            return null;
        }
        if ($data->getDistance() < $node->data->getDistance()) {
            $node->leftChild = $this->deleteNode($node->leftChild, $data);
        } else if ($data->getDistance() > $node->data->getDistance()) {
            $node->rightChild = $this->deleteNode($node->rightChild, $data);
        } else if ($data->getGid() == $node->data->getGid()) {
            if ($node->leftChild == null && $node->rightChild == null) {
                $node = null;
            } else if ($node->leftChild == null) {
                $node = $node->rightChild;
            } else if ($node->rightChild == null) {
                $node = $node->leftChild;
            } else {
                $minRight = $this->getMinNode($node->rightChild);
                $node->data = $minRight;
                $node->rightChild = $this->deleteNode($node->rightChild, $minRight);
            }
        } else {
            $node->rightChild = $this->deleteNode($node->rightChild, $data);
        }

        return $node;
    }

    function getMinNode($current = null) : DataContainer {
        if ($current == null) $current = $this->root;

        while ($current->leftChild != null) {
            $current = $current->leftChild;
        }

        return $current->data;
    }

    function isEmpty() : bool {
        return $this->root == null;
    }

}