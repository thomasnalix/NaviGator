<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Modele\Repository\HistoriqueRepository;

class HistoriqueService
{

    public function recupererHistorique(): array
    {
        return (new HistoriqueRepository())->recuperer();
    }

}