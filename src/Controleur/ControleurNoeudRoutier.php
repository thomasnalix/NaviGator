<?php

namespace Navigator\Controleur;

use Navigator\Lib\MessageFlash;
use Navigator\Lib\PlusCourtChemin;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\NoeudRoutierServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurNoeudRoutier extends ControleurGenerique {

    private NoeudRoutierServiceInterface $noeudRoutierService;

    public function __construct(NoeudRoutierServiceInterface $noeudRoutierService) {
        $this->noeudRoutierService = $noeudRoutierService;
    }

    public static function afficherErreur($errorMessage = "", $controleur = ""): Response {
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
            if ($_POST["gid$i"] != "")
                $noeudList['gid' . $i] = $_POST["gid$i"];
            else
                $noeudList['commune' . $i] = preg_replace('/\s.*/', '', $_POST["commune$i"]);
        }
        try {
            $villes = $this->noeudRoutierService->getVillesItinary($nbFields, $noeudList);

            $pcc = new PlusCourtChemin($villes, $this->noeudRoutierService);
            $datas = $pcc->aStarDistance();
            $parameters["distance"] = $datas[0];
            $parameters["temps"] = $datas[2];
            $parameters["gas"] = $datas[3];

            if (count($datas[1]) > 0)
                $parameters["chemin"] = $this->noeudRoutierService->calculerItineraire($datas[1]);
            else
                $parameters["chemin"] = [];
            $parameters["nbCommunes"] = count($noeudList);
            $parameters["nomCommuneDepart"] = array_shift($noeudList);
            $parameters["nomCommuneArrivee"] = end($noeudList);

            return new JsonResponse(json_encode($parameters), Response::HTTP_OK, [], true);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter("danger",$exception->getMessage());
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }
}