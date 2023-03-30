<?php

namespace Navigator\Controleur;

use Navigator\Lib\Conteneur;
use Navigator\Lib\MessageFlash;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ControleurGenerique {

    protected static function afficherVue(string $cheminVue, array $parametres = []): Response {
        extract($parametres);
        $messagesFlash = MessageFlash::lireTousMessages();
        ob_start();
        require __DIR__ . "/../vue/$cheminVue";
        $corpsReponse = ob_get_clean();
        return new Response($corpsReponse);
    }

    protected static function afficherTwig(string $cheminVue, array $parametres = []): Response {
        $twig = Conteneur::recupererService("twig");
        return new Response($twig->render($cheminVue, $parametres));
    }

    protected static function rediriger(string $nomRoute, array $params = []) : RedirectResponse {
        $generateurUrl = Conteneur::recupererService("generateurUrl");
        $url = "Location: " .$generateurUrl->generate($nomRoute, $params);
        return new RedirectResponse($url);
    }

    public static function afficherErreur($errorMessage = "", $statusCode = 400): Response {
        $reponse = ControleurGenerique::afficherVue('vueGenerale.php', [
            "pagetitle" => "Problème",
            "cheminVueBody" => "erreur.php",
            "errorMessage" => $errorMessage
        ]);

        $reponse->setStatusCode($statusCode);
        return $reponse;
    }

    public static function afficherAccueil() : Response {
        return ControleurGenerique::afficherTwig('accueil.html.twig', [
            "pagetitle" => "Accueil",
        ]);
    }

}