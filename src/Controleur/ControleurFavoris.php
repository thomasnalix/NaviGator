<?php

namespace App\PlusCourtChemin\Controleur;

use App\PlusCourtChemin\Service\FavorisService;

class ControleurFavoris extends ControleurGenerique
{

    public static function afficherErreur($errorMessage = "", $controleur = ""): void {
        parent::afficherErreur($errorMessage, "favoris");
    }

    public static function afficherListe(): void
    {
        $favoris = (new FavorisService())->recupererFavoris();
        ControleurFavoris::afficherVue('vueGenerale.php', [
            "favoris" => $favoris,
            "pagetitle" => "Liste des favoris",
            "cheminVueBody" => "favoris/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire() : void {}

}