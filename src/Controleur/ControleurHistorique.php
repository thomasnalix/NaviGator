<?php

namespace Navigator\Controleur;

use Navigator\Lib\Conteneur;
use Navigator\Lib\MessageFlash;
use Navigator\Service\Exception\ServiceException;
use Navigator\Service\HistoriqueServiceInterface;
use Navigator\Service\TrajetsServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurHistorique extends ControleurGenerique {


    public function __construct(
        private readonly HistoriqueServiceInterface $historiqueService,
        private readonly TrajetsServiceInterface $trajetsService
    ) { }

    public static function afficherErreur($errorMessage = "", $statusCode = ""): Response {
        return parent::afficherErreur($errorMessage, "historique");
    }

    public function afficherListe(): Response {
        $historique = $this->historiqueService->recupererHistorique();
        return ControleurHistorique::afficherVue('base.html.twig', [
            "historique" => $historique,
            "pagetitle" => "Liste des trajets",
            "cheminVueBody" => "historique/liste.php"
        ]);
    }

    public function addToHistory(): JsonResponse {
        $login = Conteneur::recupererService('userSession')->getLoginUtilisateurConnecte();
        try {
            $this->historiqueService->ajouterTrajet($login, $_POST['noeudsList'], $_POST['datas']);
        } catch (ServiceException $e) {
            return new JsonResponse(["message" => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse(["message" => "Trajet ajouté à l'historique"], Response::HTTP_OK);
    }

    public function getHistory(): Response {
        $login = Conteneur::recupererService('userSession')->getLoginUtilisateurConnecte();
        try {
            $trajets = $this->trajetsService->getHistory($login);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("navigator");
        }
        return self::afficherTwig('historique/historique.html.twig', [
            "trajets" => $trajets,
            "pagetitle" => "Liste des trajets"
        ]);
    }
}