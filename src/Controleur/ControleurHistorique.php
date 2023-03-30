<?php

namespace Navigator\Controleur;

use Navigator\Service\HistoriqueServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class ControleurHistorique extends ControleurGenerique {

    private HistoriqueServiceInterface $historiqueService;

    public function __construct(HistoriqueServiceInterface $historiqueService) {
        $this->historiqueService = $historiqueService;
    }

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
        return parent::afficherErreur($errorMessage, "historique");
    }

    public function afficherListe(): Response {
        $historique = $this->historiqueService->recupererHistorique();
        return parent::afficherVue('vueGenerale.php', [
            "historique" => $historique,
            "pagetitle" => "Liste des trajets",
            "cheminVueBody" => "historique/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire() : void {}
}