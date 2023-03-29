<?php

namespace Navigator\Controleur;

use Navigator\Lib\MessageFlash;
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
                $noeudList['commune' . $i] = substr($_POST["commune" . $i], 0, strlen($_POST["commune" . $i]) - 8);
        }
        try {
            $data = $this->noeudRoutierService->calculChemin($nbFields, $noeudList);
            return new JsonResponse(json_encode($data), Response::HTTP_OK, [], true);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter("danger",$exception->getMessage());
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }
    }
}