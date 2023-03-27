<?php

namespace Navigator\Modele\DataObject;

use Navigator\Modele\Repository\NoeudRoutierRepository;
use Exception;

class NoeudRoutier extends AbstractDataObject {
    //private array $voisins;

    public function __construct(
        private int $gid,
        private float $lat,
        private float $long,
        private array $voisins = []
    ) {
        //$this->voisins = (new NoeudRoutierRepository())->getVoisins($this->getGid());
    }

    public function getGid(): int { return $this->gid; }

    public function getLat(): float { return $this->lat; }

    public function getLong(): float { return $this->long; }

    public function getVoisins(): array {
        return $this->voisins;
    }

    public function exporterEnFormatRequetePreparee(): array {
        // Inutile car pas d'ajout ni de màj
        throw new Exception("Vous ne devriez pas appeler cette fonction car il n'y a pas de modification des noeuds routiers");
        return [];
    }


}
