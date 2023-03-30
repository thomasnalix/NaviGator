<?php

namespace Navigator\Controleur;

use Navigator\Lib\MessageFlash;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\NoeudCommuneServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurNoeudCommune extends ControleurGenerique {

    private NoeudCommuneServiceInterface $noeudCommuneService;

    public function __construct(NoeudCommuneServiceInterface $noeudCommuneService) {
        $this->noeudCommuneService = $noeudCommuneService;
    }

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
        return parent::afficherErreur($errorMessage, "noeudCommune");
    }


    /**
     * Call the database to get the list of cities that start with the text entered by the user
     * @param $text
     * @return void
     */
    public function recupererListeCommunes($text): Response {
        try {
            $noeudsCommunes = $this->noeudCommuneService->getNomCommunes($text);
            return new JsonResponse(json_encode($noeudsCommunes),Response::HTTP_OK, [], true);
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    public function recupererCoordonneesCommunes($commune): Response {
        try {
            $noeudsCommunes = $this->noeudCommuneService->getCoordNoeudCommune($commune);
            return new JsonResponse(json_encode($noeudsCommunes),Response::HTTP_OK, [], true);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter("danger",$exception->getMessage());
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Load the page to find the shortest path between cities
     * @return Response
     */
    public static function plusCourtChemin(): Response {
        $parameters = [
            "pagetitle" => "Plus court chemin",
        ];
        return ControleurNoeudCommune::afficherTwig('noeudCommune/plusCourtChemin.html.twig', $parameters);
    }
}