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
        if (!empty($search['url'])) {
            $this->db->where('url', $search['url']); //By Password
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

    public function fetch_user_status() {
        $this->db->from('st_user_status');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_user_plan() {
        $this->db->from('st_user_plan');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_user_url_availability($user_id, $url) {
        $this->db->from('st_user');
        $this->db->where('id <> ', $user_id);
        $this->db->where('url', $url);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function create_token($user_id) {
        $tmp_token = bin2hex(random_bytes(64));
        $st_user_token = array(
            'user_id' => $user_id,
            'token' => $tmp_token,
            'expires' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime('+8 hour')))),
        );
        $this->db->insert('st_user_token', $st_user_token);
        $token = AUTHORIZATION::generateToken($st_user_token);
        return $token;
        //$token = AUTHORIZATION::generateToken(['user_id' => $user_id]);
        //print_r($token);
        //return $token;
    }

    public function fetch_token_by_id($user_id, $token) {
        $this->db->from('st_user_token');
        $this->db->where(array('user_id' => $user_id, 'active' => '1', 'token' => $token, 'expires >= ' => date('Y-m-d H:i:s')));
        //$this->db->order_by("id", "DESC");
        //$this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function update_token($user_id, $token, $data) {
        $this->db->where(array('user_id' => $user_id, 'token' => $token));
        $this->db->update('st_user_token', $data);
    }

    public function fetch_collaborator($search) {
        $this->db->select('id, user_name, email');
        $this->db->from('st_user');
        $this->db->where('status_id <> ', '2');
        if (!empty($search)) {
            $this->db->like('user_name', $search);
            $this->db->or_like('email', $search);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

}
