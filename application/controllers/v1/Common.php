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
            $response = array();
            foreach ($timezones as $timezone) {
                $diff = $this->timezone_dif($timezone['zone']);
                $timezone['zone'] .= ' (' . $diff . ')';
                $response[] = $timezone;
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $response), RestController::HTTP_OK);
        } else {
            $ip = (empty($ip) || $ip == '::1') ? '170.55.19.206' : $ip;
            //IP LOG
            $ip_info = $this->Streamy_model->fetch_ip_log($ip);
            if (!empty($ip_info)) {
                $data_time_zone = (!empty($ip_info['timezone'])) ? $ip_info['timezone'] : 'America/New_York';
            } else {
                //Location Library
//                $location = file_get_contents('http://ip-api.com/json/' . $ip);
//                $data_loc = json_decode($location, true);
                $data_loc = $this->general_library->ip_location($ip);
                $data_time_zone = (!empty($data_loc['timezone'])) ? $data_loc['timezone'] : 'America/New_York';
                $ip_log = array();
                $ip_log['ip'] = $ip;
                $ip_log['country'] = (!empty($data_loc['country'])) ? $data_loc['country'] : '';
                $ip_log['countryCode'] = (!empty($data_loc['countryCode'])) ? $data_loc['countryCode'] : '';
                $ip_log['region'] = (!empty($data_loc['region'])) ? $data_loc['region'] : '';
                $ip_log['regionName'] = (!empty($data_loc['regionName'])) ? $data_loc['regionName'] : '';
                $ip_log['city'] = (!empty($data_loc['city'])) ? $data_loc['city'] : '';
                $ip_log['zip'] = (!empty($data_loc['zip'])) ? $data_loc['zip'] : '';
                $ip_log['lat'] = (!empty($data_loc['lat'])) ? $data_loc['lat'] : '';
                $ip_log['lon'] = (!empty($data_loc['lon'])) ? $data_loc['lon'] : '';
                $ip_log['timezone'] = $data_time_zone;
                $this->Streamy_model->insert_ip_log($ip_log);
            }
            $timezones = $this->Streamy_model->fetch_timezones($data_time_zone);
            $response = array();
            if (empty($timezones)) {
                $id = $this->Streamy_model->insert_timezones(array('zone' => $data_time_zone));
                $diff = $this->timezone_dif($data_time_zone);
                $zone = $data_time_zone . ' (' . $diff . ')';
                $response[] = array('id' => $id, 'zone' => $zone);
            } else {
                foreach ($timezones as $timezone) {
                    $diff = $this->timezone_dif($timezone['zone']);
                    $timezone['zone'] .= ' (' . $diff . ')';
                    $response[] = $timezone;
                }
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $response), RestController::HTTP_OK);
        }
    }

    private function timezone_dif($timezone = null) {
        date_default_timezone_set($timezone);
        $local_date = date('Y-m-d H:i:s');
        $str_local_date = strtotime($local_date);
        $str_gmt_date = local_to_gmt($str_local_date);
        $gmt_date = date('Y-m-d H:i:s', $str_gmt_date);
        $t1 = $str_local_date;
        $t2 = $str_gmt_date;
        $diff = $t1 - $t2;
        $hours = $diff / ( 60 * 60 );
        if (strpos($hours, '-') === false) {
            $hours = '+' . $hours;
        }
        return $hours;
    }

//    public function get_timezone_get($timezone = null) {
//        // create a $dt object with the UTC timezone
////        $dt = new DateTime('2016-12-12 12:12:12', new DateTimeZone('UTC'));
////
////        print_r($dt);
////
////        // change the timezone of the object without changing it's time
////        $dt->setTimezone(new DateTimeZone('America/New_York'));
////
////        // format the datetime
////        $dt->format('Y-m-d H:i:s T');
////
////        print_r($dt);
////        print_r(date_default_timezone_get());
////        print_r(date('Y-m-d H:i:s'));
////        echo '<br>';
////        $str_date = strtotime(date('Y-m-d H:i:s'));
////        //print_r($str_date);
////        $gmt_date = local_to_gmt($str_date);
////        //print_r($gmt_date);
////        $gmt_date = date('Y-m-d H:i:s', $gmt_date);
////        print_r($gmt_date);
//        //*********//
//        date_default_timezone_set('America/New_York');
//        print_r(date_default_timezone_get());
//        echo '<br>';
//        $local_date = date('Y-m-d H:i:s');
//        print_r($local_date);
//        echo '<br>';
//        $str_local_date = strtotime($local_date);
//        $str_gmt_date = local_to_gmt($str_local_date);
//        $gmt_date = date('Y-m-d H:i:s', $str_gmt_date);
//        print_r($gmt_date);
//        echo '<br>';
//        $t1 = $str_local_date;
//        $t2 = $str_gmt_date;
//        $diff = $t1 - $t2;
//        $hours = $diff / ( 60 * 60 );
//        print_r($hours);
//        echo '<br>';
//    }
//
//    public function est_to_gmt($date) {
//        date_default_timezone_set('America/New_York');
//        $str_date = strtotime($date);
//        $gmt_date = local_to_gmt($str_date);
//        $gmt_date = date('Y-m-d H:i:s', $gmt_date);
//        return $gmt_date;
//    }
//
//    public function gmt_to_est($date) {
//        date_default_timezone_set('America/New_York');
//        $str_date = strtotime($date);
//        $is_summer = date('I', $str_date); //TRUE if is summer
//        $est_date = gmt_to_local($str_date, 'UM5', $is_summer);
//        $est_date = date('Y-m-d H:i:s', $est_date);
//        //$est_date = date('Y-m-d h:i:s A', $est_date);
//        //$est_date = date('Y-m-d h:i A', $est_date);
//        return $est_date;
//    }
}
