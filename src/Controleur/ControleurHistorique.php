<?php

namespace Navigator\Controleur;

use Navigator\Lib\Conteneur;
use Navigator\Service\HistoriqueServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurHistorique extends ControleurGenerique {

    private HistoriqueServiceInterface $historiqueService;

    public function __construct(HistoriqueServiceInterface $historiqueService) {
        $this->historiqueService = $historiqueService;
    }

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

    public function addToHistory(): Response {
        // get the user's login
        $login = Conteneur::recupererService('UserSession').getLoginUtilisateurConnecte();
        $this->historiqueService->ajouterTrajet($login, $_POST['noeudsRoutier'], $_POST['datas']);

        return new Response(json_encode([
            "success" => true,
            "message" => "Trajet ajouté à l'historique"
        ]));
    }

    public static function creerDepuisFormulaire(): void {
    }
}