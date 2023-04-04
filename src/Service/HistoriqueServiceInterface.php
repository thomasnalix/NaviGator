<?php

namespace Navigator\Service;

interface HistoriqueServiceInterface {
    public function recupererHistorique(): array;

    public function ajouterTrajet($login, $trajet, $json);
}