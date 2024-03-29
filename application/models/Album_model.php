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
class Album_model extends CI_Model {

    //put your code here
    public function __construct() {
        parent::__construct();
    }

    public function insert_streamy($data) {
        $this->db->insert('st_audio', $data);
        return $this->db->insert_id();
    }

    public function update_streamy($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_audio', $data);
    }

    public function fetch_streamy_by_id($id) {
        $this->db->from('st_audio');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

//    public function fetch_streamys_by_search($search, $limit = 0, $offset = 0) {
//        $this->db->select('a.*,b.id as type_id,b.type as type_name');
//        $this->db->from('st_streamy a');
//        $this->db->join('st_streamy_type b', 'a.type_id = b.id');
//        if (!empty($search['user'])) {
//            $this->db->where('user_id', $search['user']); //By Usew
//        }
//        if (!empty($search['type'])) {
//            $this->db->where('a.type_id', $search['type']); //By Type 
//        }
//        if (!empty($search['genre'])) {
//            $this->db->where('a.genre_id', $search['genre']); //By Type 
//        }
//        if (!empty($search['name'])) {
//            $this->db->like('a.name', $search['name']); //By Type 
//        }
//        if (!empty($search['public'])) {
//            $this->db->where('public', $search['public']); //By Public
//        }
//        if (!empty($search['status'])) {
//            $this->db->where('status_id', $search['status']); //By Status
//        }
//        if (!empty($search['sort_col'])) {
//            $this->db->order_by($search['sort_col'], $search['sort_dir']);
//        }
//        if (!empty($limit)) {
//            $this->db->limit($limit, $offset);
//        }
//        $query = $this->db->get();
//        $result = $query->result_array();
//        $query->free_result();
//        return $result;
//    }
//
//    public function fetch_streamys_count_by_search($search) {
//        $this->db->select('count(*) as Count');
//        $this->db->from('st_streamy');
//        if (!empty($search['user'])) {
//            $this->db->where('user_id', $search['user']); //By Usew
//        }
//        if (!empty($search['type'])) {
//            $this->db->where('type_id', $search['type']); //By Type 
//        }
//        if (!empty($search['genre'])) {
//            $this->db->where('genre_id', $search['genre']); //By Type 
//        }
//        if (!empty($search['name'])) {
//            $this->db->like('name', $search['name']); //By Type 
//        }
//        if (!empty($search['public'])) {
//            $this->db->where('public', $search['public']); //By Public
//        }
//        if (!empty($search['status'])) {
//            $this->db->where('status_id', $search['status']); //By Status
//        }
//        $query = $this->db->get();
//        $row = $query->row();
//        $query->free_result();
//        return $row->Count;
//    }

    public function fetch_genres() {
        $this->db->from('st_genre');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_genre_by_id($id) {
        $this->db->from('st_genre');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function insert_early_access($data) {
        $this->db->insert('st_early_access', $data);
        return $this->db->insert_id();
    }

//    public function fetch_types() {
//        $this->db->from('st_streamy_type');
//        $this->db->where('active', 1); //By Status
//        $query = $this->db->get();
//        $result = $query->result_array();
//        $query->free_result();
//        return $result;
//    }
//    public function fetch_types_by_id($id) {
//        $this->db->from('st_streamy_type');
//        $this->db->where('id', $id);
//        $query = $this->db->get();
//        $result = $query->row_array();
//        $query->free_result();
//        return $result;
//    }
//    public function fetch_content_order($user_id) {
//        $this->db->from('st_user_streamy_order');
//        $this->db->where('user_id', $user_id); //By Status
//        $query = $this->db->get();
//        $result = $query->result_array();
//        $query->free_result();
//        return $result;
//    }

    public function fetch_related_album_by_user_id($user_id, $title, $track_type, $deleted = false) {
        $this->db->select('id, title');
        $this->db->from('st_album');
        $this->db->where('user_id', $user_id);
        if (!empty($track_type)) {
            $this->db->where('track_type', $track_type);
        }
        if (!empty($title)) {
            $this->db->like('title', $title);
        }
        if (!$deleted) {
            $this->db->where('status_id <> ', '3');
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_track_types() {
        $this->db->from('st_track_type');
        $this->db->where('active ', '1');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_streamys_by_user_id($user_id, $audio_id, $deleted = false, $limit = 0, $offset = 0) {
        $this->db->from('st_audio');
        $this->db->where('user_id', $user_id);
        if (!$deleted) {
            $this->db->where('status_id <> ', '3');
        }
        if (!empty($audio_id)) {
            $this->db->where('id', $audio_id);
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

    public function fetch_max_streamys_sort($user_id) {
        $this->db->select('MAX(sort) as Max');
        $this->db->from('st_audio');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
        $row = $query->row();
        $query->free_result();
        return $row->Max;
    }

    public function fetch_related_links($id, $table) {
        $this->db->from('st_related_link');
        $this->db->where('content_id', $id);
        $this->db->where('content_table', $table);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_timezones($zone) {
        $this->db->select('id, zone');
        $this->db->from('st_timezone');
        if (!empty($zone)) {
            $this->db->where('zone', $zone);
        }
        $this->db->order_by('zone');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_timezones_by_id($id) {
        $this->db->from('st_timezone');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function insert_timezones($data) {
        $this->db->insert('st_timezone', $data);
        return $this->db->insert_id();
    }

    public function fetch_ip_log($ip) {
        $this->db->select('ip');
        $this->db->from('st_ip_log');
        if (!empty($ip)) {
            $this->db->where('ip', $ip);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_ip_log($data) {
        $this->db->insert('st_ip_log', $data);
        //return $this->db->insert_id();
    }

    public function fetch_audio_key() {
        $this->db->from('st_audio_key');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_album_audio($data) {
        $this->db->insert('st_album_audio', $data);
        //return $this->db->insert_id();
    }

    public function delete_album_audio($id) {
        $this->db->where('id_audio', $id);
        $this->db->delete('st_album_audio');
    }

    public function fetch_album_audio_by_id($id) {
        $this->db->select('id_album');
        $this->db->from('st_album_audio');
        $this->db->where('id_audio', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

}
