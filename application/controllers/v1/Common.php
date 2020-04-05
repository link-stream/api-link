<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Common extends RestController {

    private $error;
    private $bucket;
    private $s3_path;

    public function __construct() {
        parent::__construct();
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        //Models
        $this->load->model("User_model");
        $this->load->model("Streamy_model");
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        $this->load->helper('email');
    }

    public function genres_get() {
        $genres = $this->Streamy_model->fetch_genres();
        $this->response(array('status' => 'success', 'env' => ENV, 'data' => $genres), RestController::HTTP_OK);
    }

    public function visibility_get($user_id = null) {
        if (!empty($user_id)) {
            $register_user = $this->User_model->fetch_user_by_id($user_id);
            if (!empty($register_user)) {
                //$visibility = array('1' => 'Public', '2' => 'Private', '3' => 'Scheduled');
                $visibility = array('1' => 'Public', '3' => 'Scheduled');
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $visibility), RestController::HTTP_OK);
//                if ($register_user['plan_id'] == '1') {
//                    $visibility = array('1' => 'Public', '2' => 'Private');
//                    $this->response(array('status' => 'success', 'env' => ENV, 'data' => $visibility), RestController::HTTP_OK);
//                } else {
//                    $visibility = array('1' => 'Public', '2' => 'Private', '3' => 'Scheduled');
//                    $visibility = array('1' => 'Public', '3' => 'Scheduled');
//                    $this->response(array('status' => 'success', 'env' => ENV, 'data' => $visibility), RestController::HTTP_OK);
//                }
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function timezones_get($ip = null) {
        if (empty($ip)) {
            $timezones = $this->Streamy_model->fetch_timezones('');
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $timezones), RestController::HTTP_OK);
        } else {
            $ip = ($ip == '::1') ? '170.55.19.206' : $ip;
            //IP LOG & Location Library -- Pending
            $location = file_get_contents('http://ip-api.com/json/' . $ip);
            $data_loc = json_decode($location, true);
            $data_time_zone = (empty($data_loc['timezone'])) ? $data_loc['timezone'] : 'America/New_York';
            $timezones = $this->Streamy_model->fetch_timezones($data_time_zone);
            if (empty($timezones)) {
                $id = $this->Streamy_model->insert_timezones(array('zone' => $data_time_zone));
                $timezones = array(array('id' => $id, 'zone' => $data_time_zone));
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $timezones), RestController::HTTP_OK);
        }
    }

}
