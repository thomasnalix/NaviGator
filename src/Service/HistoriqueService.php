<?php

namespace Navigator\Service;

use Navigator\Modele\Repository\HistoriqueRepository;

class HistoriqueService {

    public function recupererHistorique(): array {
        return (new HistoriqueRepository())->recuperer();
    }

}