<?php

namespace Navigator\Modele\DataObject;

class Favoris extends AbstractDataObject
{

    private $login;
    private array $favoris;

    /**
     * @param $login
     * @param array $favoris
     */
    public function __construct($login, array $favoris)
    {
        $this->login = $login;
        $this->favoris = $favoris;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login): void
    {
        $this->login = $login;
    }

    /**
     * @return array
     */
    public function getFavoris(): array
    {
        return $this->favoris;
    }

    /**
     * @param array $favoris
     */
    public function setFavoris(array $favoris): void
    {
        $this->favoris = $favoris;
    }

    public function exporterEnFormatRequetePreparee(): array
    {
        return [
            "login" => $this->login,
            "favoris" => $this->favoris
        ];
    }
}