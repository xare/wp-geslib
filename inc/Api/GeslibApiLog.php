<?php 

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiDbManager;

class GeslibApiLog {

    public function store2Log($filename){
        $geslibApiDbManager = new GeslibApiDbManager();
        $geslibApiDbManager->insertLogData($filename);
    }
}