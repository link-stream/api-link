<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Link
 *
 * @author paolo
 */
class Link_model extends CI_Model {

    //put your code here
    public function __construct() {
        parent::__construct();
    }

    public function insert_link($data) {
        $this->db->insert('st_link', $data);
        return $this->db->insert_id();
    }

    public function update_link($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_link', $data);
    }

    public function fetch_link_by_id($id) {
        $this->db->from('st_link');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function fetch_links_by_search($search, $limit = 0, $offset = 0) {
        $this->db->from('st_link');
        if (!empty($search['id'])) {
            $this->db->where('id', $search['id']); //By Usew
        }
        if (!empty($search['user'])) {
            $this->db->where('user_id', $search['user']); //By Usew
        }
        if (!empty($search['title'])) {
            $this->db->like('title', $search['title']); //By Type 
        }
        if (!empty($search['status'])) {
            $this->db->where('status_id', $search['status']); //By Status
        }
        if (!empty($search['sort_col'])) {
            $this->db->order_by($search['sort_col'], $search['sort_dir']);
        }
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_links_count_by_search($search) {
        $this->db->select('count(*) as Count');
        $this->db->from('st_link');
        if (!empty($search['id'])) {
            $this->db->where('id', $search['id']); //By Usew
        }
        if (!empty($search['user'])) {
            $this->db->where('user_id', $search['user']); //By Usew
        }
        if (!empty($search['title'])) {
            $this->db->like('title', $search['title']); //By Type 
        }
        if (!empty($search['status'])) {
            $this->db->where('status_id', $search['status']); //By Status
        }
        $query = $this->db->get();
        $row = $query->row();
        $query->free_result();
        return $row->Count;
    }

    public function fetch_links_by_user_id($user_id, $link_id, $deleted = false, $limit = 0, $offset = 0) {
        $this->db->from('st_link');
        $this->db->where('user_id', $user_id);
        if (!$deleted) {
            $this->db->where('status_id <> ', '3');
        }
        if (!empty($link_id)) {
            $this->db->where('id', $link_id);
        }
        $this->db->order_by('sort');
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_max_link_sort($user_id) {
        $this->db->select('MAX(sort) as Max');
        $this->db->from('st_link');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
        $row = $query->row();
        $query->free_result();
        return $row->Max;
    }

}
