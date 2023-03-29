<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$requete = Request::createFromGlobals();
$response = Navigator\Controleur\RouteurURL::traiterRequete($requete);
$response->send();