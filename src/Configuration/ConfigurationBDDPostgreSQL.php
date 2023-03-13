<?php

namespace App\PlusCourtChemin\Configuration;

use Exception;
use PDO;

class ConfigurationBDDPostgreSQL implements ConfigurationBDDInterface {
    private string $nomBDD = "iut";
    private string $hostname = "localhost";

    public function getLogin(): string {
        return "postgres";
    }

    public function getMotDePasse(): string{
        return "05092022";
    }

    public function getDSN() : string {
        return "pgsql:host={$this->hostname};dbname={$this->nomBDD};options='--client_encoding=UTF8'";
    }

    public function getOptions() : array {
        return array();
    }
}