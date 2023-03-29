<?php

namespace Navigator\Controleur;

use Navigator\Service\HistoriqueService;
use Symfony\Component\HttpFoundation\Response;

class ControleurHistorique extends ControleurGenerique {

    public static function afficherErreur($errorMessage = "", $controleur = ""): void {
        parent::afficherErreur($errorMessage, "historique");
    }

    public static function afficherListe(): Response {
        $historique = (new HistoriqueService())->recupererHistorique();
        return ControleurHistorique::afficherVue('vueGenerale.php', [
            "historique" => $historique,
            "pagetitle" => "Liste des trajets",
            "cheminVueBody" => "historique/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire() : void {}
}