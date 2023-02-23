<?php

namespace App\PlusCourtChemin\Modele\DataObject;

use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;
use Exception;

class NoeudRoutier extends AbstractDataObject
{
    //private array $voisins;

    public function __construct(
        private int $gid,
        private string $coords,
        private array $voisins = []
    ) {

        //$this->voisins = (new NoeudRoutierRepository())->getVoisins($this->getGid());
    }

    public function getGid(): int { return $this->gid; }

    public function getCoords(): string { return $this->coords; }


    public function getVoisins(): array {
        return $this->voisins;
    }

    public function exporterEnFormatRequetePreparee(): array {
        // Inutile car pas d'ajout ni de màj
        throw new Exception("Vous ne devriez pas appeler cette fonction car il n'y a pas de modification des noeuds routiers");
        return [];
    }


}
