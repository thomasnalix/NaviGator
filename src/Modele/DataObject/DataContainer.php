<?php

namespace App\PlusCourtChemin\Modele\DataObject;

class DataContainer {

    private int $gid;
    private float $distance;

    public function __construct(int $gid, float $distance) {
        $this->gid = $gid;
        $this->distance = $distance;
    }

    public function getGid(): int {
        return $this->gid;
    }

    public function getDistance(): float {
        return $this->distance;
    }

}