<?php

namespace Navigator\Controleur;

use Navigator\Service\TrajetsServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class ControleurTrajets extends ControleurGenerique {

    private TrajetsServiceInterface $trajetsService;

    public function __construct(TrajetsServiceInterface $trajetsService) {
        $this->trajetsService = $trajetsService;
    }

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
        return parent::afficherErreur($errorMessage, "trajets");
    }

    public function afficherListe(): Response {
        $trajets = $this->trajetsService->recupererTrajets();
        return parent::afficherVue('base.html.twig', [
            "trajets" => $trajets,
            "pagetitle" => "Liste des trajets",
            "cheminVueBody" => "trajets/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire(): void {
    }
}