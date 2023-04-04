<?php

namespace Navigator\Modele\DataObject;

class Historique extends AbstractDataObject {


    public function __construct(private string $login, private array $historique) {
    }

    /**
     * @return string
     */
    public function getLogin(): string {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): void {
        $this->login = $login;
    }

    /**
     * @return array
     */
    public function getHistorique(): array {
        return $this->historique;
    }

    /**
     * @param array $historique
     */
    public function setHistorique(array $historique): void {
        $this->historique = $historique;
    }

    public function exporterEnFormatRequetePreparee(): array {
        return [
            "login" => $this->login,
            "historique" => $this->historique
        ];
    }
}