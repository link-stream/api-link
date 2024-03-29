<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of general_library
 *
 * @author Paul
 */
class General_library {

    public $ses_name = 'app_session';

    //put your code here
    public function __construct() {
        $CI = & get_instance();
        $CI->load->model('User_model');
//        $CI->load->model('returned_mail_model');
//        $CI->load->helper('cookie');
        $this->ci = $CI;
//        $this->models = array('fl' => 'dmv_portal_model', 'md' => 'maryland_model', 'ca' => 'california_model', 'pa' => 'pennsylvania_model');
//        $CI->load->library(array('ShipstationLibrary', 'aws_s3'));
    }

//    public function get_temp_dir() {
//        $cronDir = sys_get_temp_dir() . '/Cron/FL/upload/';
//        if ($_SERVER['HTTP_HOST'] == 'localhost') {
//            $cronDir = FCPATH . 'processed/';
//        }
//        if (!is_dir($cronDir)) {
//            mkdir($cronDir, 0777, true);
//        }
//        return $cronDir;
//    }

    public function get_temp_dir() {
        $cronDir = sys_get_temp_dir() . '';
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            $cronDir = FCPATH . 'tmp';
        }
        if (!is_dir($cronDir)) {
            mkdir($cronDir, 0777, true);
        }
        return $cronDir;
    }

    public function urlsafe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', '.'), $data);
        return $data;
    }

    public function urlsafe_b64decode($string) {
        $data = str_replace(array('-', '_', '.'), array('+', '/', '='), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function est_to_gmt($date) {
        date_default_timezone_set('America/New_York');
        $str_date = strtotime($date);
        $gmt_date = local_to_gmt($str_date);
        $gmt_date = date('Y-m-d H:i:s', $gmt_date);
        return $gmt_date;
    }

    public function pst_to_gmt($date) {
        date_default_timezone_set('America/Los_Angeles');
        $str_date = strtotime($date);
        $gmt_date = local_to_gmt($str_date);
        $gmt_date = date('Y-m-d H:i:s', $gmt_date);
        return $gmt_date;
    }

    public function gmt_to_est($date) {
        date_default_timezone_set('America/New_York');
        $str_date = strtotime($date);
        $is_summer = date('I', $str_date); //TRUE if is summer
        $est_date = gmt_to_local($str_date, 'UM5', $is_summer);
        $est_date = date('Y-m-d H:i:s', $est_date);
        return $est_date;
    }

    public function gmt_to_pst($date) {
        date_default_timezone_set('America/Los_Angeles');
        $str_date = strtotime($date);
        $is_summer = date('I', $str_date); //TRUE if is summer
        $pst_date = gmt_to_local($str_date, 'UM8', $is_summer);
        $pst_date = date('Y-m-d H:i:s', $pst_date);
        return $pst_date;
    }

    public function create_cookie($streamy_user) {
        $encrypted_user = $this->urlsafe_b64encode($streamy_user);
        $cookie_user = array(
            'name' => $this->ses_name,
            'value' => serialize(array('user' => $encrypted_user)),
            'expire' => 7200
        );
        $this->ci->input->set_cookie($cookie_user);
    }

    public function get_cookie() {
        $cookie = unserialize($this->ci->input->cookie($this->ses_name));
        $user_encrypt = $cookie['user'];
        $user = json_decode($this->urlsafe_b64decode($user_encrypt), true);
        return $user;
    }

    public function update_cookie($data = null) {
        if ($this->ci->input->cookie($this->ses_name) != '') {
            $cookie_value = (empty($data)) ? $this->ci->input->cookie($this->ses_name) : $data;
            $cookie_user = array(
                'name' => $this->ses_name,
                'value' => $cookie_value,
                'expire' => 7200
            );
            $this->ci->input->set_cookie($cookie_user);
        }
    }

    public function encrypt_txt($cadena) {
        $cadena_encriptada1 = md5($cadena); //Encriptacion nivel 1
        $cadena_encriptada2 = crc32($cadena_encriptada1); //Encriptacion nivel 1
        $cadena_encriptada3 = crypt($cadena_encriptada2, "xtemp"); //Encriptacion nivel 2
        $cadena_encriptada4 = sha1("xtemp" . $cadena_encriptada3); //Encriptacion nivel 3
        return $cadena_encriptada4;
    }

    public function send_ses($to_name, $to_email, $fname, $femail, $subject, $message) {
        $this->ci->load->library('email');
        $config['mailtype'] = 'html';
        $config['useragent'] = 'Post Title';
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'ssl://email-smtp.us-east-1.amazonaws.com';
        $config['smtp_user'] = 'AKIAXBDC73PH3F5A6MWM';
        $config['smtp_pass'] = 'BFtMJuOK7u0Ye8JBYXT4+kVJwyXSFOe0dr2E2Ji2sxne';
        $config['smtp_port'] = '465';
        $config['wordwrap'] = TRUE;
        $config['smtp_crypto'] = 'tsl';
        $config['newline'] = "\r\n";

        $this->ci->email->initialize($config);
        $this->ci->email->set_newline("\r\n");
        //
        $this->ci->email->from($femail, $fname);
        $this->ci->email->to($to_email, $to_name);
        $this->ci->email->subject($subject);
        $this->ci->email->message($message);
        try {
            if (@$this->ci->email->send()) {
                return true;
            } else {
                echo $this->ci->email->print_debugger();
                exit();
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function ip_location($ip) {
        //LOCATION ip-api.com
        $location = file_get_contents('http://ip-api.com/json/' . $ip);
        $data_loc = json_decode($location, true);
        return $data_loc;
    }

    public function header_token($user_id) {
//        $headers = array();
//        foreach (getallheaders() as $name => $value) {
//            $headers[$name] = $value;
//        }
        $headers = $this->ci->input->request_headers();
        $headers['Token'] = (!empty($headers['Token'])) ? $headers['Token'] : ((!empty($headers['token'])) ? $headers['token'] : '');
        if (empty($headers['Token'])) {
            return false;
        } else {
            try {
                $token_data = AUTHORIZATION::validateToken($headers['Token']);
                if (empty($token_data)) {
                    return false;
                } else {
                    if ($token_data->user_id != $user_id) {
                        return false;
                    } else {
                        $st_token = $this->ci->User_model->fetch_token_by_id($token_data->user_id, $token_data->token);
                        if (empty($st_token)) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                }
            } catch (Exception $e) {
                // Token is invalid
                // Send the unathorized access message
                return false;
            }
        }
    }

    public function unset_token() {
        $headers = $this->ci->input->request_headers();
        $headers['Token'] = (!empty($headers['Token'])) ? $headers['Token'] : ((!empty($headers['token'])) ? $headers['token'] : '');
        if (empty($headers['Token'])) {
            return false;
        } else {
            try {
                $token_data = AUTHORIZATION::validateToken($headers['Token']);
                if (empty($token_data)) {
                    return false;
                } else {
                    $this->ci->User_model->update_token($token_data->user_id, $token_data->token, array('active' => '0'));
                    return true;
                }
            } catch (Exception $e) {
                // Token is invalid
                // Send the unathorized access message
                return false;
            }
        }
    }

    public function local_to_gtm($timezone, $date, $time) {
//        $timezone = 'America/New_York';
//        $date = '2020-04-06';
//        $time = '21:10:00';
        $datetime = $date . ' ' . $time;
//        echo $datetime . ' ' . $timezone;
//        echo '<br>';
        $local_date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime, new DateTimeZone($timezone));
        $utc_date = $local_date;
        $utc_date->setTimeZone(new DateTimeZone('UTC'));
//        echo $utc_date->format('Y-m-d H:i:s') . ' UTC';
        return $utc_date->format('Y-m-d H:i:s');
    }

    public function gtm_to_local($timezone, $datetime) {
//        $timezone = 'America/New_York';
//        $datetime = '2020-04-07 01:10:00';
//        echo $datetime . ' UTC';
//        echo '<br>';
        $utc_date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime, new DateTimeZone('UTC'));
        $local_date = $utc_date;
//        echo $timezone;
//        echo '<br>';
        $local_date->setTimeZone(new DateTimeZone($timezone));
//        echo $local_date->format('Y-m-d H:i:s') . ' ' . $timezone;
//        echo '<br>';
//        echo $local_date->format('Y-m-d');
//        echo '<br>';
//        echo $local_date->format('H:i:s');
//        echo '<br>';
        return $local_date->format('Y-m-d H:i:s');
    }

//    public function get_cookie($action = null) {
//        $cookie = unserialize($this->ci->input->cookie('cs_etags'));
//        $user_encrypt = $cookie['user'];
//        $user = json_decode($this->urlsafe_b64decode($user_encrypt), true);
//        $menu_encrypt = $cookie['menu'];
//        $user['menu'] = unserialize(gzuncompress($this->urlsafe_b64decode($menu_encrypt)));
//        if (!empty($action)) {
//            //Check is this action is available to this user
//            if (strpos(serialize($user['menu']), $action) === false) {
//                redirect('/cs', 'location', 302);
//            }
//        }
//        return $user;
//    }

    public function force_ssl() {
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "on") || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')) {
            $url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            redirect($url);
            exit();
        }
    }

}
