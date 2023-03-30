<?php

namespace Navigator\Controleur;

use Navigator\Service\Exception\ServiceException;
use Navigator\Service\UtilisateurServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControleurUtilisateurAPI {

    private UtilisateurServiceInterface $utilisateurService;

    public function __construct(UtilisateurServiceInterface $utilisateurService) {
        $this->utilisateurService = $utilisateurService;
    }

    public function afficherDetail($idUser) : Response {
        $utilisateur = $this->utilisateurService->afficherDetailUtilisateur($idUser);
        return new JsonResponse(json_encode($utilisateur), Response::HTTP_OK, [], true);
    }

    public function connecter(Request $request): Response
    {
        try {
            $login = $request->get("login");
            $password = $request->get("password");
            $idUtilisateur = $this->utilisateurService->verifierIdentifiantUtilisateur($login, $password);
            // Appel du service connexionUtilisateur

            // pour connecter l'utilisateur avec son identifiant
            // et retourner un token JWT

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