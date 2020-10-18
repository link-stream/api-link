<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class A extends CI_Controller {

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model("Marketing_model");
        //$this->load->model("Streamy_model");
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
            $ref_id = $this->input->get('ref_id');
            //LOCATION
            $ip = $_SERVER['REMOTE_ADDR'];
            $ip = ($ip == '::1') ? '170.55.19.206' : $ip;
            $data_location = $this->general_library->ip_location($ip);
            $this->Marketing_model->update_open_action($ref_id, $ip, $data_location['country']);
        }
        $imagen_url = (ENV != 'live') ? 'https://dev-link-vue.link.stream/static/img/open.jpg' : 'https://linkstream/static/img/open.jpg';
        header("Content-Type: image/jpeg"); // it will return image 
        $logo = file_get_contents($imagen_url);
        echo $logo;
    }

}
