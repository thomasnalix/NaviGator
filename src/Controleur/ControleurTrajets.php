<?php

namespace Navigator\Controleur;

use Navigator\Service\TrajetsService;
use Symfony\Component\HttpFoundation\Response;

class ControleurTrajets extends ControleurGenerique {

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
        return parent::afficherErreur($errorMessage, "trajets");
    }

    public static function afficherListe(): Response {
        $trajets = (new TrajetsService())->recupererTrajets();
        return ControleurTrajets::afficherVue('base.html.twig', [
            "trajets" => $trajets,
            "pagetitle" => "Liste des trajets",
            "cheminVueBody" => "trajets/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire() : void {}
}