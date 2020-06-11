<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of streamy
 *
 * @author paolo
 */
class Log_model extends CI_Model {

    //put your code here
    public function __construct() {
        parent::__construct();
    }
    
    public function insert_user_action_log($data) {
        $this->db->insert('st_user_action_log', $data);
        //return $this->db->insert_id();
    }

    public function insert_content_log($data) {
        $this->db->insert('st_content_log', $data);
        //return $this->db->insert_id();
    }

    

}
