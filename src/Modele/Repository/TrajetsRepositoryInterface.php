<?php

namespace Navigator\Modele\Repository;

interface TrajetsRepositoryInterface {

    public function getHistory($login);

    public function getTrajet($idTrajet);
}