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
        <a class="gator" href="controleurFrontal.php?"><img class="logo" src="../ressources/img/logo.png" alt="logo"></a>
        <a href="controleurFrontal.php?action=afficherListe&controleur=utilisateur">Utilisateurs</a>
        <a href="controleurFrontal.php?action=plusCourtChemin&controleur=noeudCommune">Calcul</a>
            <?php

            use App\PlusCourtChemin\Lib\ConnexionUtilisateur;

            if (!ConnexionUtilisateur::estConnecte()) {
                echo <<<HTML
                        <a href="controleurFrontal.php?action=afficherFormulaireConnexion&controleur=utilisateur">
                            <img alt="login" src="../ressources/img/enter.png" width="18">
                        </a>
                    HTML;
            } else {
                $loginHTML = htmlspecialchars(ConnexionUtilisateur::getLoginUtilisateurConnecte());
                $loginURL = rawurlencode(ConnexionUtilisateur::getLoginUtilisateurConnecte());
                echo <<<HTML
                        <a href="controleurFrontal.php?action=afficherDetail&controleur=utilisateur&login=$loginURL">
                            <img alt="user" src="../ressources/img/user.png" width="18">
                            $loginHTML
                        </a>
                        <a href="controleurFrontal.php?action=deconnecter&controleur=utilisateur">
                            <img alt="logout" src="../ressources/img/logout.png" width="18">
                        </a>
                    HTML;
            }
            ?>
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