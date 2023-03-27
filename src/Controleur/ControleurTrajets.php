<?php

namespace Navigator\Controleur;

use Navigator\Service\TrajetsService;

class ControleurTrajets extends ControleurGenerique
{

    public static function afficherErreur($errorMessage = "", $controleur = ""): void {
        parent::afficherErreur($errorMessage, "trajets");
    }

    public static function afficherListe(): void
    {
        $trajets = (new TrajetsService())->recupererTrajets();
        ControleurTrajets::afficherVue('vueGenerale.php', [
            "trajets" => $trajets,
            "pagetitle" => "Liste des trajets",
            "cheminVueBody" => "trajets/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire() : void {}

}