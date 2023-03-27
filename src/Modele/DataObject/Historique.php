<?php

namespace Navigator\Modele\DataObject;

class Historique extends AbstractDataObject
{

    private $login;
    private array $historique;

    public function __construct(string $login, array $historique)
    {
        $this->login = $login;
        $this->historique = $historique;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    /**
     * @return array
     */
    public function getHistorique(): array
    {
        return $this->historique;
    }

    /**
     * @param array $historique
     */
    public function setHistorique(array $historique): void
    {
        $this->historique = $historique;
    }

    public function exporterEnFormatRequetePreparee(): array
    {
        return [
            "login" => $this->login,
            "historique" => $this->historique
        ];
    }
}