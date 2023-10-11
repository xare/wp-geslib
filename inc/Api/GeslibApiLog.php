<?php

namespace Inc\Geslib\Api;

use Inc\Geslib\Api\GeslibApiDbManager;


class GeslibApiLog {

    private $db;

    public function __construct(){
        $this->db = new GeslibApiDbManager();
    }

    /* public function store2Log($filename){
        $this->db->insertLogData($filename);
    } */

    public function getQueuedFile(){
        return $this->db->getLogQueuedFile();
    }

    public function setStatus( $log_id, $status ){
        return $this->db->setLogStatus( $log_id, $status );
    }

}