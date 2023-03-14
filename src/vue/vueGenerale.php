<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $pagetitle ?></title>
    <link rel="stylesheet" href="../ressources/css/navstyle.css">
    <link rel="stylesheet" href="https://openlayers.org/en/v6.6.1/css/ol.css" type="text/css">
    <script src="https://openlayers.org/en/v6.6.1/build/ol.js"></script>
</head>
<body>
<header>
    <nav>
        <a href="controleurFrontal.php?action=afficherListe&controleur=utilisateur">Utilisateurs</a>
        <a href="controleurFrontal.php?action=afficherListe&controleur=noeudCommune">Communes</a>
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