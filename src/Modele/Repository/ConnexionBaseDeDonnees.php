<?php

namespace Navigator\Modele\Repository;

use Navigator\Configuration\Configuration;
use Navigator\Configuration\ConfigurationBDDPostgreSQL;
use PDO;

class ConnexionBaseDeDonnees implements ConnexionBaseDeDonneesInterface {
    private PDO $pdo;

    public function getPdo(): PDO {
        return $this->pdo;
    }

    public function __construct() {
        $configuration = new Configuration(new ConfigurationBDDPostgreSQL());
        $configurationBDD = $configuration->getConfigurationBDD();

        // Connexion à la base de données
        $this->pdo = new PDO(
            $configurationBDD->getDSN(),
            $configurationBDD->getLogin(),
            $configurationBDD->getMotDePasse(),
            $configurationBDD->getOptions()
        );

        // On active le mode d'affichage des erreurs, et le lancement d'exception en cas d'erreur
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

}