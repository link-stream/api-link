<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class A extends CI_Controller {

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model("User_model");
        $this->load->model("Streamy_model");
    }

    public function _remap($method, $params = array()) {
        $method = str_replace('-', '_', $method);
        if (method_exists(__CLASS__, $method)) {
            $count = count($params);
            if ($count == 0)
                $this->$method();
            elseif ($count == 1)
                $this->$method($params[0]);
            elseif ($count == 2)
                $this->$method($params[0], $params[1]);
            elseif ($count == 3)
                $this->$method($params[0], $params[1], $params[2]);
            elseif ($count == 4)
                $this->$method($params[0], $params[1], $params[2], $params[3]);
        } else {
            redirect('/' . $this->loc_url, 'location', 302);
        }
    }

    public function action_open() {
        if (!empty($this->input->get('ref_id'))) {
            $id = $this->input->get('ref_id');
//            $pos = strpos($id, '_');
//            $log_id = substr($id, 0, $pos);
//            $ref_id = substr($id, $pos + 1);
            //SET LATER
//            $detail = $this->c_notification_model->get_notification_by_ref($log_id, $ref_id);
//            if (empty($detail)) {
//                $this->c_notification_model->insert_notification_detail(array('log_id' => $log_id, 'ref_id' => $ref_id));
//            }
        }
        $imagen_url = (ENV != 'live') ? 'https://dev-link-vue.link.stream/static/img/open.jpg' : 'https://linkstream/static/img/open.jpg';
//        print_r(ENV);
//        echo $id;
//        exit;
        header("Content-Type: image/jpeg"); // it will return image 
        $logo = file_get_contents($imagen_url);
        echo $logo;
    }

}
