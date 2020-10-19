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
class Marketing_model extends CI_Model {

    //put your code here
    public function __construct() {
        parent::__construct();
    }

    public function fetch_messages_by_user_id($user_id, $messages_id, $deleted = false, $limit = 0, $offset = 0) {
        $this->db->from('st_marketing_messages');
        $this->db->where('user_id', $user_id);
        if (!$deleted) {
            $this->db->where('status <> ', 'Deleted');
        }
        if (!empty($messages_id)) {
            $this->db->where('id', $messages_id);
        }
        $this->db->order_by('id', 'DESC');
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_message($data) {
        $this->db->insert('st_marketing_messages', $data);
        return $this->db->insert_id();
    }

    public function fetch_message_by_id($id) {
        $this->db->from('st_marketing_messages');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function update_message($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_marketing_messages', $data);
    }

    public function fetch_subscribers_by_user_id($user_id, $subscriber_id, $search, $deleted = false, $limit = 0, $offset = 0) {
        $this->db->from('st_marketing_subscribers');
        $this->db->where('user_id', $user_id);
//        if (!$deleted) {
//            $this->db->where('status <> ', 'Deleted');
//        }
        if (!empty($subscriber_id)) {
            $this->db->where('id', $subscriber_id);
        }
        if (!empty($search)) {
            $this->db->where("(`name` LIKE '%$search%' OR `email` LIKE '%$search%' OR `phone` LIKE '%$search%')", null, false);
        }
        $this->db->order_by('name');
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_subscriber($data) {
        $this->db->insert('st_marketing_subscribers', $data);
        return $this->db->insert_id();
    }

    public function fetch_subscriber_by_id($id) {
        $this->db->from('st_marketing_subscribers');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function update_subscriber($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_marketing_subscribers', $data);
    }

    public function fetch_subscribers_tags_by_user_id($user_id) {
        $this->db->select('tags');
        $this->db->from('st_marketing_subscribers');
        $this->db->where('user_id', $user_id);
        $this->db->group_by('tags');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_subscriber_log_by_id($id) {
        $this->db->from('st_marketing_messages_log');
        $this->db->where('subscriber_id', $id);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_message_log_by_id($message_id) {
        $this->db->from('st_marketing_messages_log');
        $this->db->where('message_id', $message_id);
        $this->db->order_by('id');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_media_files_by_user_id($user_id, $media_id) {
        $this->db->from('st_user_media_files');
        $this->db->where('user_id', $user_id);
        if (!empty($media_id)) {
            $this->db->where('id', $media_id);
        }
        $this->db->where('status', 'ACTIVE');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_media_file($data) {
        $this->db->insert('st_user_media_files', $data);
        return $this->db->insert_id();
    }

    public function fetch_media_files_by_id($id) {
        $this->db->from('st_user_media_files');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function update_media_files($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('st_user_media_files', $data);
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

    //PUBLIC PROFILE
    public function fetch_links_by_profile($user_id, $link_id, $tag, $sort = 'default', $limit = 0, $offset = 0) {
        $this->db->from('st_link');
        $this->db->where('user_id', $user_id);
        $this->db->where('status_id <> ', '3');
        $this->db->where('public', '1');
        if (!empty($link_id)) {
            $this->db->where('id', $link_id);
        }
        if (!empty($tag)) {
            $this->db->like('title', $tag);
        }
        if ($sort == 'default') {
            $this->db->order_by('sort');
        } elseif ($sort == 'new') {
            $this->db->order_by('id', 'DESC');
        } else {
            $this->db->order_by('sort');
        }
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    //Messages Cron
    public function fetch_messages($status, $date, $limit = 0, $offset = 0) {
        $this->db->from('st_marketing_messages');
        $this->db->where('status', $status);
        if (!empty($date)) {
            $this->db->where('publish_at <= ', $date);
        }
        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_sms_subscribers_by_user_id($user_id, $segment) {
        $this->db->select('id, phone');
        $this->db->from('st_marketing_subscribers');
        $this->db->where('user_id', $user_id);
        $this->db->where('sms_status', 'subscribed');
        $this->db->order_by('id');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_email_subscribers_by_user_id($user_id, $segment) {
        $this->db->select('id, email, name');
        $this->db->from('st_marketing_subscribers');
        $this->db->where('user_id', $user_id);
        $this->db->where('email_status', 'subscribed');
        $this->db->order_by('id');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_subscribers_by_segment($user_id, $segment, $type, $count = false) {
//        $list = [
//            'all-subscribers' => 'All Subscribers in Audience',
//            'new-subscribers' => 'New Subscribers',
//            'purchase' => 'Has made a purchase',
//            'no-purchase' => "Hasn't Purchased yet"
//        ];
        $sql = '';
        if ($segment == 'all-subscribers') {
            if ($count) {
                $sql .= "SELECT count(*) as Count ";
            } else {
                $sql .= "SELECT id, email, name, phone ";
            }
            $sql .= "FROM st_marketing_subscribers ";
            $sql .= "WHERE ";
            $sql .= "user_id =  '" . $user_id . "' ";
            if ($type == 'email') {
                $sql .= "AND email_status = 'subscribed' ";
            } else {
                $sql .= "AND sms_status = 'subscribed' ";
            }
            $sql .= "ORDER BY id";
        } elseif ($segment == 'new-subscribers') {
            $date = date('Y-m-d 00:00:00', strtotime(date('Y-m-d 00:00:00', strtotime('-15 days'))));
            if ($count) {
                $sql .= "SELECT count(*) as Count ";
            } else {
                $sql .= "SELECT id, email, name, phone ";
            }
            $sql .= "FROM st_marketing_subscribers ";
            $sql .= "WHERE ";
            $sql .= "user_id =  '" . $user_id . "' ";
            if ($type == 'email') {
                $sql .= "AND email_status = 'subscribed' ";
            } else {
                $sql .= "AND sms_status = 'subscribed' ";
            }
            $sql .= "AND created_at >= '" . $date . "' ";
            $sql .= "ORDER BY id";
        } elseif ($segment == 'purchase') {
            
        } elseif ($segment == 'no-purchase') {
            
        } elseif (!empty($segment)) {
            if ($count) {
                $sql .= "SELECT count(*) as Count ";
            } else {
                $sql .= "SELECT id, email, name, phone ";
            }
            $sql .= "FROM st_marketing_subscribers ";
            $sql .= "WHERE ";
            $sql .= "user_id =  '" . $user_id . "' ";
            if ($type == 'email') {
                $sql .= "AND email_status = 'subscribed' ";
            } else {
                $sql .= "AND sms_status = 'subscribed' ";
            }
            $sql .= "AND tags LIKE '%" . $segment . "%' ";
            $sql .= "ORDER BY id";
        }
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function insert_messages_log($data) {
        $this->db->insert('st_marketing_messages_log', $data);
        return $this->db->insert_id();
    }

    public function update_open_action($ref_id, $ip, $country, $country_code) {
        //STEP 1
        $this->db->select('message_id, open');
        $this->db->where('ref_id', $ref_id);
        $query = $this->db->get('st_marketing_messages_log');
        $result = $query->row_array();
        //STEP 2
        if ($result['open'] != '1') {
            $this->db->set('open', '1');
            $this->db->set('open_at', date('Y-m-d H:i:s'));
            $this->db->set('open_ip', $ip);
            $this->db->set('open_country', $country);
            $this->db->set('open_country_code', $country_code);
            //$this->db->set('open', '1', FALSE);
            //$this->db->set('open_at', date('Y-m-d H:i:s'), FALSE);
            $this->db->where('ref_id', $ref_id);
            $this->db->update('st_marketing_messages_log');
            //STEP 3
            $this->db->set('open', 'open + 1', FALSE);
            $this->db->where('id', $result['message_id']);
            $this->db->update('st_marketing_messages');
        }
    }

    public function update_click_action($ref_id) {
        //STEP 1
        $this->db->select('message_id, click');
        $this->db->where('ref_id', $ref_id);
        $query = $this->db->get('st_marketing_messages_log');
        $result = $query->row_array();
        //STEP 2
        if ($result['click'] != '1') {
            $this->db->set('click', '1');
            $this->db->set('click_at', date('Y-m-d H:i:s'));
//            $this->db->set('open_ip', $ip);
//            $this->db->set('open_country', $country);
            //$this->db->set('open', '1', FALSE);
            //$this->db->set('open_at', date('Y-m-d H:i:s'), FALSE);
            $this->db->where('ref_id', $ref_id);
            $this->db->update('st_marketing_messages_log');
            //STEP 3
            $this->db->set('click', 'click + 1', FALSE);
            $this->db->where('id', $result['message_id']);
            $this->db->update('st_marketing_messages');
        }
    }

    public function fetch_message_open_data($message_id, $date) {
        $sql = 'SELECT HOUR(CONVERT_TZ(open_at,"GMT","America/New_York")) as OPEN_HOURS, Count(*) as OPEN
FROM st_marketing_messages_log
where open = "1" and open_at >= "' . $date . '" GROUP BY OPEN_HOURS';
        //print_r($sql);
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_message_click_data($message_id, $date) {
        $sql = 'SELECT HOUR(CONVERT_TZ(click_at,"GMT","America/New_York")) as CLICK_HOURS, Count(*) as CLICK
FROM st_marketing_messages_log
where click = "1" and click_at >= "' . $date . '" GROUP BY CLICK_HOURS';
        //print_r($sql);
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_message_hours_data($message_id, $date) {
        $sql = 'SELECT HOURS, SUM(OPEN) as OPEN,SUM(CLICK) as CLICK FROM (
SELECT HOUR(CONVERT_TZ(open_at,"GMT","America/New_York")) as HOURS, Count(*) as OPEN, 0 as CLICK
FROM st_marketing_messages_log
where open = "1" and open_at >= "' . $date . '" GROUP BY HOURS
UNION
SELECT HOUR(CONVERT_TZ(click_at,"GMT","America/New_York")) as HOURS, 0 as OPEN,Count(*) as CLICK
FROM st_marketing_messages_log
where click = "1" and click_at >= "' . $date . '" GROUP BY HOURS
) A  GROUP BY HOURS';
        //print_r($sql);
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_country_open_data($message_id) {
        $sql = 'SELECT open_country as Country, count(*) as Count  FROM st_marketing_messages_log
WHERE message_id = "' . $message_id . '" and open_country is not null
group by open_country order by Count Desc';
        //print_r($sql);
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_email_revenue($message_id) {
        $sql = "SELECT count(*) as Count, sum(total) as Total FROM st_marketing_messages_log a inner join st_user_invoice b
on a.ref_id = b.ref_id
WHERE message_id = '" . $message_id . "' and open = '1'";
        $query = $this->db->query($sql);
        $result = $query->row_array();
        $query->free_result();
        return $result;
    }

    public function fetch_open_activity($message_id) {
        $this->db->select('open_at as action_date,"Open" as action ');
        $this->db->from('st_marketing_messages_log');
        $this->db->where('message_id', $message_id);
        $this->db->where('open', '1');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function fetch_click_activity($message_id) {
        $this->db->select('click_at as action_date,"Click" as action ');
        $this->db->from('st_marketing_messages_log');
        $this->db->where('message_id', $message_id);
        $this->db->where('click', '1');
        $query = $this->db->get();
        $result = $query->result_array();
        $query->free_result();
        return $result;
    }

    public function update_revenue_message_log($ref_id, $amount) {
        //STEP 1
        $this->db->select('message_id');
        $this->db->where('ref_id', $ref_id);
        $query = $this->db->get('st_marketing_messages_log');
        $result = $query->row_array();
        //STEP 2
        $this->db->set('revenue', 'revenue +' . $amount, FALSE);
        $this->db->where('id', $result['message_id']);
        $this->db->update('st_marketing_messages');
    }

}
