<?php

namespace Navigator\Controleur;

use Navigator\Lib\MessageFlash;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\NoeudRoutierServiceInterface;
use Navigator\Service\PlusCourtCheminServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurNoeudRoutierAPI extends ControleurGenerique {


    public function __construct(
        private readonly PlusCourtCheminServiceInterface $plusCourtCheminService,
        private readonly NoeudRoutierServiceInterface    $noeudRoutierService) {
    }

    public static function afficherErreur($errorMessage = "", $statusCode = ""): Response {
        return parent::afficherErreur($errorMessage, "noeudCommune");
    }

    /**
     * Call the database to get the nearest node to the coordinates entered by the user
     * @param $long
     * @param $lat
     */
    public function getNoeudProche($long, $lat): Response {
        try {
            $information = $this->noeudRoutierService->getNoeudRoutierProche($lat, $long);
            return new JsonResponse(json_encode($information), Response::HTTP_OK, [], true);
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }

    /**
     * Call the A* algorithm to find the shortest path between cities
     * @return void
     */
    public function calculChemin(): Response {
        $nbFields = $_POST['nbField'];
        $noeudList = [];
        for ($i = 0; $i < $nbFields; $i++) {
            if (isset($_POST["gid$i"]) && $_POST["gid$i"] != "")
                $noeudList['gid' . $i] = $_POST["gid$i"];
            else
                // detect pattern " (56879)" or " (2B096) and remove it
                $noeudList['commune' . $i] = preg_replace('/\s\(\w+\d+\)/', '', $_POST["commune$i"]);
        }
        try {
            $villes = $this->noeudRoutierService->getVillesItinary($nbFields, $noeudList);

            foreach ($villes as $ville)
                $parameters['noeudsList'][] = $ville->getGid();

            $now = microtime(true);
            $datas = $this->plusCourtCheminService->aStarDistance($villes);
            $parameters["time"] = microtime(true) - $now;
            $parameters["distance"] = $datas[0];
            $parameters["chemin"] = count($datas[1]) > 0 ? $this->noeudRoutierService->calculerItineraire($datas[1]) : [];
            $parameters["temps"] = $datas[2];
            $parameters["nbCommunes"] = count($noeudList);
            $parameters["nomCommuneDepart"] = array_shift($noeudList);
            $parameters["nomCommuneArrivee"] = end($noeudList);
            return new JsonResponse(json_encode($parameters), Response::HTTP_OK, [], true);
        } catch (ServiceException $exception) {
            $parameters["error"] = $exception->getMessage();
            $parameters["distance"] = -1; // for js
            return new JsonResponse(json_encode($parameters), Response::HTTP_OK, [], true);
        }
    }

    /**
     * Call the database to get the list of cities that start with the text entered by the user
     * @param $text
     * @param ControleurNoeudCommune $controleurNoeudCommune
     * @return Response
     */
    public function recupererListeCommunes($text): Response {
        $noeudsCommunes = $this->noeudRoutierService->getNomCommunes($text);
        return new JsonResponse(json_encode($noeudsCommunes), Response::HTTP_OK, [], true);
    }

    public function recupererCoordonneesCommunes($commune): Response {
        try {
            // if commune is a number, it's a gid
            if (is_numeric($commune))
                $noeudsCommunes = $this->noeudRoutierService->getCoordNoeudByGid($commune);
            else
                $noeudsCommunes = $this->noeudRoutierService->getCoordNoeudCommune($commune);
            return new JsonResponse(json_encode($noeudsCommunes), Response::HTTP_OK, [], true);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter("danger", $exception->getMessage());
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }
}