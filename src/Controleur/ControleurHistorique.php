<?php

namespace App\PlusCourtChemin\Controleur;

class ControleurHistorique extends ControleurGenerique
{

    public static function afficherErreur($errorMessage = "", $controleur = ""): void {
        parent::afficherErreur($errorMessage, "historique");
    }

    public static function afficherListe(): void
    {
        $historique = (new HistoriqueService())->recupererHistorique();
        ControleurHistorique::afficherVue('vueGenerale.php', [
            "historique" => $historique,
            "pagetitle" => "Liste des trajets",
            "cheminVueBody" => "historique/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire() : void {}
}