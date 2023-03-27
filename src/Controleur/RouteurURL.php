<?php
namespace Navigator\Controleur;
use Navigator\Lib\Conteneur;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


class RouteurURL {
    public static function traiterRequete() {
        $routes = new RouteCollection();
        // ROUTE ROOT
        $route = new Route("/", ["_controller" => "\Navigator\Controleur\ControleurGenerique::afficherAccueil"]);
        $routes->add("navigator", $route);

        // ROUTE CONNEXION GET
        $route = new Route("/connexion", ["_controller" => "\Navigator\Controleur\ControleurUtilisateur::afficherFormulaireConnexion",]);
        $routes->add("afficherFormulaireConnexion", $route);
        $route->setMethods(["GET"]);

        // ROUTE CONNEXION POST
        $route = new Route("/connexion", ["_controller" => "\Navigator\Controleur\ControleurUtilisateur::connecter"]);
        $routes->add("connecter", $route);
        $route->setMethods(["POST"]);

        // ROUTE DECONNEXION
        $route = new Route("/deconnexion", ["_controller" => "\Navigator\Controleur\ControleurUtilisateur::deconnecter"]);
        $routes->add("deconnecter", $route);
        $route->setMethods(["GET"]);

        // ROUTE feedy
        $route = new Route("/map", ["_controller" => "\Navigator\Controleur\ControleurNoeudCommune::plusCourtChemin"]);
        $routes->add("map", $route);
        $route->setMethods(["GET"]);

        // ROUTE INSCRIPTION GET
        $route = new Route("/inscription", ["_controller" => "\Navigator\Controleur\ControleurUtilisateur::afficherFormulaireCreation"]);
        $routes->add("afficherFormulaireCreation", $route);
        $route->setMethods(["GET"]);

        // ROUTE INSCRIPTION POST
        $route = new Route("/inscription", ["_controller" => "\Navigator\Controleur\ControleurUtilisateur::creerDepuisFormulaire"]);
        $routes->add("creerDepuisFormulaire", $route);
        $route->setMethods(["POST"]);

        // ROUTE pagePerso
        $route = new Route("/utilisateur/{idUser}", ["_controller" => "\Navigator\Controleur\ControleurUtilisateur::pagePerso"]);
        $routes->add("pagePerso", $route);
        $route->setMethods(["GET"]);

        // Route getnoeudProche
        $route = new Route("/noeudProche/lon/{long}/lat/{lat}", ["_controller" => "\Navigator\Controleur\ControleurNoeudCommune::getNoeudProche"]);
        $routes->add("getNoeudProche", $route);
        $route->setMethods(["GET"]);

        // Route calcul
        $route = new Route("/calcul/{idNoeudDepart}/{idNoeudArrivee}", ["_controller" => "\Navigator\Controleur\ControleurNoeudCommune::calcul"]);
        $routes->add("calcul", $route);
        $route->setMethods(["GET"]);

        // recupererListeCommunes
        $route = new Route("/communes/{text}", ["_controller" => "\Navigator\Controleur\ControleurNoeudCommune::recupererListeCommunes"]);
        $routes->add("recupererListeCommunes", $route);
        $route->setMethods(["GET"]);

        // recupererCoordCommune
        $route = new Route("/communes/coord/{commune}", ["_controller" => "\Navigator\Controleur\ControleurNoeudCommune::recupererCoordonneesCommunes"]);
        $routes->add("recupererCoordonneesCommunes", $route);
        $route->setMethods(["GET"]);

        // Start calcul
        $route = new Route("/calculChemin", ["_controller" => "\Navigator\Controleur\ControleurNoeudCommune::calculChemin"]);
        $routes->add("calculChemin", $route);
        $route->setMethods(["POST"]);




        $requete = new Request($_GET,$_POST,[],$_COOKIE,$_FILES,$_SERVER);
        $contexteRequete = (new RequestContext())->fromRequest($requete);


        $associateurUrl = new UrlMatcher($routes, $contexteRequete);
        $donneesRoute = $associateurUrl->match($requete->getPathInfo());

        $requete->attributes->add($donneesRoute);

        $resolveurDeControleur = new ControllerResolver();
        $controleur = $resolveurDeControleur->getController($requete);

        $resolveurDArguments = new ArgumentResolver();
        $arguments = $resolveurDArguments->getArguments($requete, $controleur);

        $assistantUrl = new UrlHelper(new RequestStack(), $contexteRequete);
        $assistantUrl->getAbsoluteUrl("assets/css/styles.css");
        $generateurUrl = new UrlGenerator($routes, $contexteRequete);

        Conteneur::ajouterService("assistantUrl", $assistantUrl);
        Conteneur::ajouterService("generateurUrl", $generateurUrl);

        call_user_func_array($controleur, $arguments);

    }
}