<?php

namespace Navigator\Modele\DataObject;

use Navigator\Modele\Repository\NoeudRoutierRepository;
use Exception;

class NoeudRoutier extends AbstractDataObject {

    public function __construct(
        private int $gid,
        private float $lat,
        private float $long,
    ) {
    }

    public function getGid(): int { return $this->gid; }

    public function getLat(): float { return $this->lat; }

    public function getLong(): float { return $this->long; }

    public function exporterEnFormatRequetePreparee(): array {
        // Inutile car pas d'ajout ni de màj
        throw new Exception("Vous ne devriez pas appeler cette fonction car il n'y a pas de modification des noeuds routiers");
        return [];
    }


}
