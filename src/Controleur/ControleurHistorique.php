<?php

namespace Navigator\Controleur;

use Navigator\Service\HistoriqueService;
use Symfony\Component\HttpFoundation\Response;

class ControleurHistorique extends ControleurGenerique {

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
        return parent::afficherErreur($errorMessage, "historique");
    }

    public static function afficherListe(): Response {
        $historique = (new HistoriqueService())->recupererHistorique();
        return ControleurHistorique::afficherVue('base.html.twig', [
            "historique" => $historique,
            "pagetitle" => "Liste des trajets",
            "cheminVueBody" => "historique/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire() : void {}
}