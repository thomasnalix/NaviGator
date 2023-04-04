<?php

namespace Navigator\Modele\Repository;

interface HistoriqueRepositoryInterface {

    public function ajouterHistorique(string $login, array $trajet, array $json): bool;
}