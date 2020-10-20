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
        $this->db->select('id, user_name, email, image');
        $this->db->from('st_user');
        $this->db->where('status_id <> ', '2');
        if (!empty($search)) {
            $this->db->like('user_name', $search);
            $this->db->or_like('email', $search);
        }
        $this->db->limit(25);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_user_purchase($data) {
        $this->db->insert('st_user_invoice', $data);
        return $this->db->insert_id();
    }

    public function update_user_purchase($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_user_invoice', $data);
    }

    public function fetch_user_purchases($user_id) {
        $this->db->from('st_user_invoice');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'COMPLETED');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_user_purchase_details($data) {
        $this->db->insert('st_user_invoice_detail', $data);
        return $this->db->insert_id();
    }

    public function fetch_user_purchases_details($invoice_id) {
        $this->db->select('a.*, b.display_name, c.track_type');
        $this->db->from('st_user_invoice_detail a');
        $this->db->join('st_user b', 'a.producer_id = b.id');
        $this->db->join('st_track_type c', 'a.item_track_type = c.id');
        $this->db->where('invoice_id', $invoice_id);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_user_purchases_details_2($invoice_id) {
        $this->db->select('a.*, b.display_name');
        $this->db->from('st_user_invoice_detail a');
        $this->db->join('st_user b', 'a.producer_id = b.id');
        //$this->db->join('st_track_type c', 'a.item_track_type = c.id');
        $this->db->where('invoice_id', $invoice_id);
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_payment_method($data) {
        $this->db->insert('st_user_payment_method', $data);
        return $this->db->insert_id();
    }

    public function update_payment_method($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_user_payment_method', $data);
    }

    public function update_payment_method_by_user_id($user_id, $data) {
        $this->db->where('user_id', $user_id);
        $this->db->update('st_user_payment_method', $data);
    }

    public function fetch_payment_method_by_id($id) {
        $this->db->from('st_user_payment_method');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function fetch_payment_method_by_user_id($user_id) {
        $this->db->from('st_user_payment_method');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'ACTIVE');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_notification_by_user_id($user_id) {
        $this->db->from('st_user_notification');
        $this->db->where('user_id', $user_id);
        //$this->db->where('status', 'ACTIVE');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_notification($data) {
        $this->db->insert('st_user_notification', $data);
        return $this->db->insert_id();
    }

    public function fetch_notification_by_id($id) {
        $this->db->from('st_user_notification');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function update_notification($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_user_notification', $data);
    }

    //Subscriptions
    public function fetch_user_subscriptions_by_user_id($user_id, $processor = 'Stripe') {
        $this->db->from('st_user_subscriptions');
        $this->db->where('user_id', $user_id);
        $this->db->where('status', 'ACTIVE');
        $this->db->where('processor', $processor);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function update_subscriptions_by_user_id($user_id, $data) {
        $this->db->where('user_id', $user_id);
        $this->db->update('st_user_subscriptions', $data);
    }

    public function insert_user_subscriptions($data) {
        $this->db->insert('st_user_subscriptions', $data);
        return $this->db->insert_id();
    }

    public function insert_user_connect($data) {
        $this->db->insert('st_user_connect', $data);
        return $this->db->insert_id();
    }

    public function update_connect_by_user_id($user_id, $account_id, $data) {
        $this->db->where(array('user_id' => $user_id, 'account_id' => $account_id));
        $this->db->update('st_user_connect', $data);
    }

    public function fetch_stripe_account_by_user_id($user_id, $processor = 'Stripe') {
        $this->db->from('st_user_connect');
        $this->db->where('user_id', $user_id);
        $this->db->where('status <> ', 'DECLINED');
        $this->db->where('processor', $processor);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function fetch_user_orders($user_id) {
        $sql = "SELECT b.id, b.invoice_number,b.created_at,c.first_name,c.last_name,sum(a.item_amount) as total, count(*) as items FROM st_user_invoice_detail a 
inner join st_user_invoice b on a.invoice_id = b.id
inner join st_user c on b.user_id = c.id
where a.producer_id = '" . $user_id . "' group by b.invoice_number order by b.id desc ";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_user_order_detail($user_id, $invoice_id) {
        $sql = "SELECT a.id, b.invoice_number,b.created_at,c.first_name,c.last_name,a.item_title,a.item_amount,a.item_track_type,a.license_id,b.billingCC6,b.billingCC,c.email,a.item_id
FROM st_user_invoice_detail a 
inner join st_user_invoice b on a.invoice_id = b.id
inner join st_user c on b.user_id = c.id
where a.producer_id = '" . $user_id . "' and b.id = '" . $invoice_id . "' ";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_confirmation_detail_item($item_id, $item_track_type) {
        if ($item_track_type == 'beat' || $item_track_type == 'kit') {
            $sql = "SELECT coverart,display_name,first_name,last_name,url 
FROM st_audio a inner join st_user b 
on a.user_id = b.id
where a.id =  '" . $item_id . "' ";
        } else {
            $sql = "SELECT coverart,display_name,first_name,last_name,url 
FROM st_album a inner join st_user b 
on a.user_id = b.id
where a.id =  '" . $item_id . "' ";
        }



//        $sql = "SELECT a.id, b.invoice_number,b.created_at,c.first_name,c.last_name,a.item_title,a.item_amount,a.item_track_type,a.license_id,b.billingCC6,b.billingCC,c.email,a.item_id
//FROM st_user_invoice_detail a 
//inner join st_user_invoice b on a.invoice_id = b.id
//inner join st_user c on b.user_id = c.id
//where a.producer_id = '" . $user_id . "' and b.id = '" . $invoice_id . "' ";
        $query = $this->db->query($sql);
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

}
