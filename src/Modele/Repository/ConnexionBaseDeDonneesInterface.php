<?php

namespace Navigator\Modele\Repository;

use PDO;

interface ConnexionBaseDeDonneesInterface {
    public function getPdo(): PDO;
}