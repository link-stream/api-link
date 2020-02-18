<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user_model
 *
 * @author paolo
 */
class User_model extends CI_Model {

    //put your code here
    //put your code here
    public function __construct() {
        parent::__construct();
    }

    public function insert_user($data) {
        $this->db->insert('st_user', $data);
        return $this->db->insert_id();
    }

    public function update_user($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_user', $data);
    }

    public function fetch_user_by_id($id) {
        $this->db->from('st_user');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function fetch_user_by_search($search) {
        $this->db->from('st_user');
        if (!empty($search['id'])) {
            $this->db->where('id', $search['id']); //By Id
        }
        if (!empty($search['email'])) {
            $this->db->where('email', $search['email']); //By Email
        }
        if (!empty($search['password'])) {
            $this->db->where('password', $search['password']); //By Password
        }
        if (!empty($search['user_name'])) {
            $this->db->where('user_name', $search['user_name']); //By Password
        }
        if (!empty($search['platform_id'])) {
            $this->db->where('platform_id', $search['platform_id']); //By Password
        }
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function insert_user_log($data) {
        $this->db->insert('st_user_log', $data);
        //return $this->db->insert_id();
    }

    public function fetch_user_log_by_user($user_id) {
        $this->db->from('st_user_log');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

}
