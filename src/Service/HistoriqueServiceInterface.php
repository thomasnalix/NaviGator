<?php

namespace Navigator\Service;

interface HistoriqueServiceInterface {

    public function ajouterHistorique($login, $trajet, $json);
}