<?php

namespace Navigator\Configuration;

use Exception;
use PDO;

class ConfigurationBDDPostgreSQL implements ConfigurationBDDInterface {
    private string $nomBDD = "iut";
//    private string $hostname = "localhost";
    private string $hostname = "162.38.222.142";

    public function getLogin(): string {
//        return "postgres";
        return "nalixt";
    }

    public function getMotDePasse(): string{
        return "05092022";
//        return '05092022';
    }

    public function getDSN() : string {
        return "pgsql:host={$this->hostname};dbname={$this->nomBDD};options='--client_encoding=UTF8'";
    }

    public function getOptions() : array {
        return array();
    }
}