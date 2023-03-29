<?php

namespace Navigator\Controleur;

use Navigator\Controleur\ControleurGenerique;

class RouteurQueryString {

    public static function traiterRequete() {

        $action = $_REQUEST['action'] ?? 'afficherAccueil';
        $controleur = $_REQUEST['controleur'] ?? "generique";
        $controleurClassName = 'Navigator\Controleur\Controleur' . ucfirst($controleur);

        if (class_exists($controleurClassName)) {
            if (in_array($action, get_class_methods($controleurClassName)))
                $controleurClassName::$action();
            else
                $controleurClassName::afficherErreur("Erreur d'action");
        } else {
            ControleurGenerique::afficherErreur("Erreur de contrôleur");
        }
    }

}