<?php

namespace Navigator\Controleur;

use Navigator\Lib\ConnexionUtilisateurInterface;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\UtilisateurServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControleurUtilisateurAPI {


    public function __construct(
        private readonly UtilisateurServiceInterface   $utilisateurService,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateur) {
    }

    public function afficherDetail($idUser): Response {
        $utilisateur = $this->utilisateurService->afficherDetailUtilisateur($idUser);
        return new JsonResponse(json_encode($utilisateur), Response::HTTP_OK, [], true);
    }

    public function connecter(Request $request): Response {
        try {
            $login = $request->get("login");
            $password = $request->get("password");
            $idUtilisateur = $this->utilisateurService->verifierIdentifiantUtilisateur($login, $password);
            $this->connexionUtilisateur->connecter($idUtilisateur);
            return new JsonResponse([], Response::HTTP_OK);

        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        } catch (\JsonException $exception) {
            return new JsonResponse(
                ["error" => "Corps de la requête mal formé"],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}