<?php

namespace Navigator\Controleur;

use Navigator\Service\FavorisService;
use Symfony\Component\HttpFoundation\Response;

class ControleurFavoris extends ControleurGenerique {

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
        return parent::afficherErreur($errorMessage, "favoris");
    }

    public static function afficherListe(): Response {
        $favoris = (new FavorisService())->recupererFavoris();
        return ControleurFavoris::afficherVue('base.html.twig', [
            "favoris" => $favoris,
            "pagetitle" => "Liste des favoris",
            "cheminVueBody" => "favoris/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire() : void {}
}