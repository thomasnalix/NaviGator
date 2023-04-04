<?php

namespace Navigator\Controleur;

use Navigator\Service\FavorisServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class ControleurFavoris extends ControleurGenerique {

    private FavorisServiceInterface $favorisService;

    public function __construct(FavorisServiceInterface $favorisService) {
        $this->favorisService = $favorisService;
    }

    public static function afficherErreur($errorMessage = "", $statusCode = ""): Response {
        return parent::afficherErreur($errorMessage, "favoris");
    }

    public function afficherListe(): Response {
        $favoris = $this->favorisService->recupererFavoris();
        return ControleurFavoris::afficherVue('base.html.twig', [
            "favoris" => $favoris,
            "pagetitle" => "Liste des favoris",
            "cheminVueBody" => "favoris/liste.php"
        ]);
    }

    public static function creerDepuisFormulaire(): void {
    }
}