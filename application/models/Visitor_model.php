<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of visitor_model
 *
 * @author paolo
 */
class Visitor_model extends CI_Model {

    //put your code here
    //put your code here
    public function __construct() {
        parent::__construct();
    }

//    public function insert_visitor($data) {
//        $this->db->select('id');
//        $this->db->from('st_visitor');
//        $this->db->where(array('session_id' => $data['session_id']));
//        $this->db->limit(1);
//        $qry = $this->db->get();
//        $result = $qry->row_array();
//        if (empty($result)) {
//            $this->db->insert('st_visitor', $data);
//        }
//    }

    public function insert_visitor($data) {
        $this->db->insert('st_visitor', $data);
    }

    public function fetch_visitor_by_session_id($session_id) {
        $this->db->from('st_visitor');
        $this->db->where('session_id', $session_id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }
    
    public function fetch_visitor_by_search($search) {
        $this->db->from('st_visitor');
        if (!empty($search['user_id'])) {
             $this->db->where('user_id', $search['user_id']);
        }
        if (!empty($search['session_id'])) {
             $this->db->where('session_id', $search['session_id']);
        }
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }
    
    public function fetch_visitors($user_id, $date){
         $sql = 'SELECT DATE(CONVERT_TZ(transDateTime,"GMT","America/New_York")) as TransDate, count(*) as Count 
             FROM st_visitor 
where user_id = "' . $user_id . '" and transDateTime >= "' . $date . '" 
group by TransDate order by TransDate';
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }
    
    public function fetch_top_referrers($user_id, $date, $limit = 5) {
        $sql = "SELECT utm_source,count(*) as Count 
            FROM st_visitor
where user_id = '" . $user_id . "' and transDateTime >= '" . $date . "' and utm_source <> '' and utm_source is not null group by utm_source order by Count desc limit " . $limit;
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

//    public function order_id($email) {
//        $this->db->select('id');
//        $this->db->from('orders');
//        $this->db->where(array('email' => $email, 'state' => 'FL', 'oDate' => date('Y-m-d')));
//        $this->db->limit(1);
//        $qry = $this->db->get();
//        $result = $qry->row_array();
//        if (empty($result)) {
//            $this->db->insert('orders', array('email' => $email, 'state' => 'FL', 'oDate' => date('Y-m-d')));
//            return $this->db->insert_id();
//        }
//        return $result['id'];
//    }
}
