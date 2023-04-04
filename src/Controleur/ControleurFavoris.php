<?php

namespace Navigator\Controleur;

use Navigator\Service\FavorisServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class ControleurFavoris extends ControleurGenerique {


    public function __construct(private readonly FavorisServiceInterface $favorisService) {}

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