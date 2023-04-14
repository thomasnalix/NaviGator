# NaviGator

NaviGator est un projet de développement d'une application Web codée en PHP pour calculer l'itinéraire le plus court entre plusieurs communes.

## Table des matières

- [Présentation](#présentation)
- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Images](#images)
- [Contributions](#Contributions)

## Présentation

Ce projet est une application Web développée en PHP qui permet de calculer l'itinéraire le plus court entre plusieurs communes ou noeudRoutier en utilisant l'algorithme A*. L'application offre également des fonctionnalités supplémentaires telles que la gestion des utilisateurs, l'historique des trajets, le ping lors d'un clique sur la carte d'une destination et l'estimation de la consommation en fonction du véhicule choisi.

## Fonctionnalités

- Calcul de l'itinéraire le plus court entre plusieurs communes en utilisant l'algorithme A*.
- Gestion des utilisateurs (inscription, connexion, déconnexion).
- Historique des trajets effectués.
- Estimation de la consommation en fonction du véhicule choisi.
- Affichage et calcul des étapes du trajet (ex: Montpellier -> Paris -> Brest).
- Mise en cache par département pour optimiser les performances.

## Technologies utilisées

- PHP
- JavaScript
- Twig (moteur de templates)
- PostgreSQL (base de données)
- PostGIS (extension pour la manipulation de données géométriques)

## Installation

1. Cloner le dépôt Git : `git clone https://github.com/thomasnalix/NaviGator.git`
2. Installer les dépendances avec Composer : `composer install`
3. Importer la base de données PostgreSQL avec les données géométriques.
4. Configurer les paramètres de connexion à la base de données dans le fichier de configuration.
5. Configurer l'environnement de développement (xDebug, PHPUnit, etc.) selon les besoins.
6. Accéder à l'application via un navigateur web.

## Utilisation

1. S'inscrire ou se connecter à l'application.
2. Saisir les communes de départ et d'arrivée.
3. Choisir les options supplémentaires telles que le véhicule, les étapes du trajet, etc.
4. Cliquer sur le bouton "Calculer l'itinéraire".
5. Afficher le résultat avec l'itinéraire le plus court, la distance, le temps estimé et la consommation en carburant.
6. Consulter l'historique des trajets effectués.

## Images
![CalculNaviGator](Navigator1.png)

## Contributions

### Maxence
#### Pourcentage d’investissement - 34%
> Durant ce projet, j'ai réalisé plusieurs tâches. La première a été de participer à la phase d'analyse. Une grande partie de mon temps a été consacrée à la recherche et à l'implémentation d'une structure de données adaptée à notre algorithme. J'ai pu en implémenter un bon nombre, dont le binary search tree, le tas de Fibonacci et enfin la PriorityQueue. À côté de cela, je me suis occupé de mettre en place le système de cache des départements. En parallèle, j'ai également essayé une autre approche pour le cache en essayant de me passer de la base de données pour accéder aux voisins en pré calculant l'ensemble des départements dans un fichier JSON à l'aide d'un script Python. Pour finir, j'ai implémenté l'API des voitures en calculant la consommation étant donné le trajet. J'ai également eu le temps de m'occuper de l'autocomplétion des villes et d'aider à droite à gauche les membres de l'équipe. Enfin, j'ai réalisé les tests sur le service du plus court chemin.

---

### Loris
#### Pourcentage d’investissement - 30%
> Durant ce projet, j’ai été chargé de m’occuper de la mise en place et de la maintenance de la base de données afin de satisfaire des requêtes, changer la structure de donnée, réfléchir à des optimisations, mettre en place des index, procédures, tables, schéma E/A. 
J’ai aussi participé au PHP et au JS en m’occupant d’une partie de Twig, des services et de ses interfaces, du refactor du code et de l’entièreté de la programmation réactive. J’ai également participé au webdesign du site en mettant en place des éléments de mon portfolio dans la SAE (parallax, scroll).
Enfin j’ai aussi aidé à la création du discord ainsi qu’à son organisation architecturale.

---

### Thomas
#### Pourcentage d’investissement - 36%
> Durant le projet, j’ai eu l’occasion de m’occuper de la réalisation de l’algorithme A* ainsi que de son heuristique et des différentes évolutions de celui-ci, j’ai également eu comme mission d’intégrer les premier jeux de test (mock) et l’architecture vu en complément web tel que le routage, les dépendances, les services, twig, … Je me suis également occupé de l’UI et l’UX du site en passant par le design, les script js, les contrôler et la sécurité (cas d’erreurs) de l’utilisateur. Je me suis aussi investi dans l’optimisation côté sql des requêtes ainsi que des vues. Pour finir, je me suis occupé de toute la partie historique de l’utilisateur. Pour finir, je me suis investi dans beaucoup des différentes étapes d'optimisation.
