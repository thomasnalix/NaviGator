<?php

namespace Navigator\Modele\DataObject;

class Favoris extends AbstractDataObject {


    /**
     * @param $login
     * @param array $favoris
     */
    public function __construct(private $login, private array $favoris) {
    }

    /**
     * @return mixed
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login): void {
        $this->login = $login;
    }

    /**
     * @return array
     */
    public function getFavoris(): array {
        return $this->favoris;
    }

    /**
     * @param array $favoris
     */
    public function setFavoris(array $favoris): void {
        $this->favoris = $favoris;
    }

    public function exporterEnFormatRequetePreparee(): array {
        return [
            "login" => $this->login,
            "favoris" => $this->favoris
        ];
    }
}