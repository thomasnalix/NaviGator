<?php

use Navigator\Lib\ConnexionUtilisateur;
$generateurUrl = Navigator\Lib\Conteneur::recupererService("generateurUrl");
$assistantUrl = Navigator\Lib\Conteneur::recupererService("assistantUrl");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $pagetitle ?></title>
    <link rel="icon" type="image/png" href="../ressources/img/favicon.png" />
    <link rel="stylesheet" href="../ressources/css/main.css">
    <link rel="stylesheet" href="https://openlayers.org/en/v6.6.1/css/ol.css" type="text/css">
    <script src="https://openlayers.org/en/v6.6.1/build/ol.js"></script>
    <script src="../ressources/js/scroll.js"></script>
    <script src="../ressources/js/mousemove.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<header>
    <nav>
        <a class="gator" href="<?= $generateurUrl->generate("navigator"); ?>"><img class="logo" src="../ressources/img/logo_void.png" alt="logo"></a>
        <a href="<?= $generateurUrl->generate("map"); ?>">Calcul</a>

        <?php
        if (!ConnexionUtilisateur::estConnecte()) {
            ?>
            <a href="<?= $generateurUrl->generate("creerDepuisFormulaire"); ?>">Inscription</a>
            <a href="<?= $generateurUrl->generate("connecter"); ?>">Connexion</a>
            <?php
        } else {
            $loginHTML = htmlspecialchars(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            $loginURL = rawurlencode(ConnexionUtilisateur::getLoginUtilisateurConnecte());
            ?>
            <a href="<?= $generateurUrl->generate("pagePerso", ["idUser" => $loginURL]); ?>">Mon compte</a>
            <a href="<?= $generateurUrl->generate("deconnecter"); ?>">Déconnexion</a>
        <?php } ?>
    </nav>
    <div>
        <?php
        foreach (["success", "info", "warning", "danger"] as $type) {
            foreach ($messagesFlash[$type] as $messageFlash) {
                echo <<<HTML
                    <div class="alert alert-$type">
                        $messageFlash
                    </div>
                    HTML;
            }
        }
        ?>
    </div>
</header>
<main>
    <?php
    /**
     * @var string $cheminVueBody
     */
    require __DIR__ . "/{$cheminVueBody}";
    ?>
</main>
</body>

</html>