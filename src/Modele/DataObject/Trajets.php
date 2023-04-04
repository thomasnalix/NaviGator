<?php

namespace Navigator\Modele\DataObject;

class Trajets extends AbstractDataObject {

    /**
     * @param $idTrajet
     * @param array $trajets
     */
    public function __construct(private $idTrajet, private array $trajets) {
    }

    /**
     * @return mixed
     */
    public function getIdTrajet() {
        return $this->idTrajet;
    }

    /**
     * @param mixed $idTrajet
     */
    public function setIdTrajet($idTrajet): void {
        $this->idTrajet = $idTrajet;
    }

    /**
     * @return array
     */
    public function getTrajets(): array {
        return $this->trajets;
    }

    /**
     * @param array $trajets
     */
    public function setTrajets(array $trajets): void {
        $this->trajets = $trajets;
    }

    public function exporterEnFormatRequetePreparee(): array {
        return [
            "idTrajet" => $this->idTrajet,
            "trajets" => $this->trajets
        ];
    }
}