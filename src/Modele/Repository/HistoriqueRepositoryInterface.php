<?php

namespace Navigator\Modele\Repository;

interface HistoriqueRepositoryInterface {

    public function ajouterHistorique(string $login, string $trajet, string $json): bool;
}