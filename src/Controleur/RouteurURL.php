<?php
namespace Navigator\Controleur;

use Navigator\Configuration\ConfigurationBDDPostgreSQL;
use Navigator\Lib\ConnexionUtilisateurJWT;
use Navigator\Lib\ConnexionUtilisateurSession;
use Navigator\Lib\Conteneur;
use Navigator\Modele\Repository\ConnexionBaseDeDonnees;
use Navigator\Modele\Repository\NoeudCommuneRepository;
use Navigator\Modele\Repository\NoeudRoutierRepository;
use Navigator\Modele\Repository\UtilisateurRepository;
use Navigator\Service\NoeudCommuneService;
use Navigator\Service\NoeudRoutierService;
use Navigator\Service\UtilisateurService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class RouteurURL {



    public static function traiterRequete(Request $requete): Response {



        /* =========================================================================== */
        /* =============================== DEPENDANCES =============================== */
        /* =========================================================================== */

        $conteneur = new ContainerBuilder();
        $conteneur->register('config_bdd', ConfigurationBDDPostgreSQL::class);

        $connexionBaseService = $conteneur->register('connexion_base', ConnexionBaseDeDonnees::class);
        $connexionBaseService->setArguments([new Reference('config_bdd')]);

        /* ------------------------------ NOEUD COMMUNE ------------------------------ */
        $noeudCommuneRepositoryService = $conteneur->register('noeud_commune_repository',NoeudCommuneRepository::class);
        $noeudCommuneRepositoryService->setArguments([new Reference('connexion_base')]);

        $noeudCommuneService = $conteneur->register('noeud_commune_service', NoeudCommuneService::class);
        $noeudCommuneService->setArguments([new Reference('noeud_commune_repository')]);

        $noeudCommuneControleurService = $conteneur->register('noeud_commune_controleur',ControleurNoeudCommune::class);
        $noeudCommuneControleurService->setArguments([new Reference('noeud_commune_service')]);


        /* ------------------------------- NOEUD ROUTIER ------------------------------- */
        $noeudRoutierRepositoryService = $conteneur->register('noeud_routier_repository',NoeudRoutierRepository::class);
        $noeudRoutierRepositoryService->setArguments([new Reference('connexion_base')]);

        $noeudRoutierServiceService = $conteneur->register('noeud_routier_service',NoeudRoutierService::class);
        $noeudRoutierServiceService->setArguments([new Reference('noeud_routier_repository'), new Reference('noeud_commune_repository')]);

        $noeudRoutierControleurService = $conteneur->register('noeud_routier_controleur',ControleurNoeudRoutier::class);
        $noeudRoutierControleurService->setArguments([new Reference('noeud_routier_service')]);

        /* -------------------------------  UTILISATEUR  ------------------------------ */
        // TODO: louche cette histoire
        $utilisateurSessionService = $conteneur->register('utilisateur_session',ConnexionUtilisateurSession::class);
        $utilisateurJWTService = $conteneur->register('utilisateur_jwt',ConnexionUtilisateurJWT::class);

        $utilisateurRepositoryService = $conteneur->register('utilisateur_repository',UtilisateurRepository::class);
        $utilisateurRepositoryService->setArguments([new Reference('connexion_base')]);

        $utilisateurServiceService = $conteneur->register('utilisateur_service',UtilisateurService::class);
        $utilisateurServiceService->setArguments([new Reference('utilisateur_repository'), new Reference('utilisateur_session')]);

        $utilisateurControleurService = $conteneur->register('utilisateur_controleur',ControleurUtilisateurAPI::class);
        $utilisateurControleurService->setArguments([new Reference('utilisateur_service'), new Reference('utilisateur_session')]);

        /* =========================================================================== */
        /* ================================ ROUTES =================================== */
        /* =========================================================================== */

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

        // ROUTE MAP
        $route = new Route("/map", ["_controller" => "noeud_commune_controleur::plusCourtChemin"]);
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

        // ROUTE PAGE PERSO
        $route = new Route("/utilisateur/{idUser}", ["_controller" => "\Navigator\Controleur\ControleurUtilisateur::afficherDetail"]);
        $routes->add("pagePerso", $route);
        $route->setMethods(["GET"]);

        /* =========================================================================== */
        /* =============================== API ROUTES ================================ */
        /* =========================================================================== */

        // Route getnoeudProche
        $route = new Route("/noeudProche/lon/{long}/lat/{lat}", ["_controller" => "noeud_routier_controleur::getNoeudProche"]);
        $routes->add("getNoeudProche", $route);
        $route->setMethods(["GET"]);

        // recupererListeCommunes
        $route = new Route("/communes/{text}", ["_controller" => "noeud_commune_controleur::recupererListeCommunes"]);
        $routes->add("recupererListeCommunes", $route);
        $route->setMethods(["GET"]);

        // recupererCoordCommune
        $route = new Route("/communes/coord/{commune}", ["_controller" => "noeud_commune_controleur::recupererCoordonneesCommunes"]);
        $routes->add("recupererCoordonneesCommunes", $route);
        $route->setMethods(["GET"]);

        // Start calcul
        $route = new Route("/calculChemin", ["_controller" => "noeud_routier_controleur::calculChemin"]);
        $routes->add("calculChemin", $route);
        $route->setMethods(["POST"]);

        // info utilisateur
        $route = new Route("/utilisateurs/{idUser}", ["_controller" => "utilisateur_controleur::afficherDetail"]);
        $routes->add("afficherDetail", $route);
        $route->setMethods(["GET"]);



        //$requete = new Request($_GET,$_POST,[],$_COOKIE,$_FILES,$_SERVER);
        $contexteRequete = (new RequestContext())->fromRequest($requete);


        $assistantUrl = new UrlHelper(new RequestStack(), $contexteRequete);
        $assistantUrl->getAbsoluteUrl("assets/css/styles.css");
        $generateurUrl = new UrlGenerator($routes, $contexteRequete);


        /* =========================================================================== */
        /* ================================ Load Twig ================================ */
        /* =========================================================================== */
        $twigLoader = new FilesystemLoader(__DIR__ . '/../vue/');
        $twig = new Environment(
            $twigLoader,
            [
                'autoescape' => 'html',
                'strict_variables' => true
            ]
        );

        /* =========================================================================== */
        /* ================================= SERVICES ================================ */
        /* =========================================================================== */

        Conteneur::ajouterService("twig", $twig);
        Conteneur::ajouterService("assistantUrl", $assistantUrl);
        Conteneur::ajouterService("generateurUrl", $generateurUrl);
        Conteneur::ajouterService("userSession", new ConnexionUtilisateurSession());
        Conteneur::ajouterService("userJWT", new ConnexionUtilisateurJWT());

        // recupererService("generateurUrl");
        $callable = function ($nomRoute, $parametres = []) {
            return Conteneur::recupererService("generateurUrl")->generate($nomRoute, $parametres);
        };
        $twig->addFunction(new TwigFunction("generateurUrl", $callable));

        $callable = function ($nomRoute, $parametres = []) {
            return Conteneur::recupererService("assistantUrl")->getAbsoluteUrl($nomRoute, $parametres);
        };
        $twig->addFunction(new TwigFunction("assistantUrl", $callable));

        $callable = function () {
            return Conteneur::recupererService("userSession")->estConnecte();
        };
        $twig->addFunction(new TwigFunction("estConnecte", $callable));


        try {
            $associateurUrl = new UrlMatcher($routes, $contexteRequete);
            $donneesRoute = $associateurUrl->match($requete->getPathInfo());
            $requete->attributes->add($donneesRoute);

            $resolveurDeControleur = new ContainerControllerResolver($conteneur);
            $controleur = $resolveurDeControleur->getController($requete);

            $resolveurDArguments = new ArgumentResolver();
            $arguments = $resolveurDArguments->getArguments($requete, $controleur);

            $reponse = call_user_func_array($controleur, $arguments);
        } catch (MethodNotAllowedException $exception) {
            $reponse = ControleurGenerique::afficherErreur($exception->getMessage(), 405);
        } catch (NotFoundHttpException $exception) {
            $reponse = ControleurGenerique::afficherErreur($exception->getMessage(), 404);
        } catch (\Exception $exception) {
            $reponse = ControleurGenerique::afficherErreur($exception->getMessage()) ;
        }
        return $reponse;

    }
}