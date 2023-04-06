<?php

namespace Navigator\Service;

interface TrajetsServiceInterface {

    public function getHistory($login):array;

    public function getTrajet($idTrajet):string;

}