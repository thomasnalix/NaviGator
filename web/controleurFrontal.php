<?php
use App\PlusCourtChemin\Lib\Psr4AutoloaderClass;

require_once __DIR__ . '/../vendor/autoload.php';

// Syntaxe alternative
// The null coalescing operator returns its first operand if it exists and is not null
$action = $_REQUEST['action'] ?? 'afficherAccueil';

$controleur = $_REQUEST['controleur'] ?? "generique";

$controleurClassName = 'App\PlusCourtChemin\Controleur\Controleur' . ucfirst($controleur);

if (class_exists($controleurClassName)) {
    if (in_array($action, get_class_methods($controleurClassName))) {
        $controleurClassName::$action();
    } else {
        $controleurClassName::afficherErreur("Erreur d'action");
    }
} else {
    App\PlusCourtChemin\Controleur\ControleurGenerique::afficherErreur("Erreur de contrôleur");
}

