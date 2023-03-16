<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\DataContainer;

interface DataStructure
{

    function insert(DataContainer $data) : void;

    function search(DataContainer $data) : bool;

    function delete(DataContainer $data) : void;

    function getMinNode($current = null) : ?DataContainer;

    function isEmpty() : bool;

}