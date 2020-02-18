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
//        $CI->load->model('cs_model');
//        $CI->load->model('returned_mail_model');
//        $CI->load->helper('cookie');
        $this->ci = $CI;
//        $this->models = array('fl' => 'dmv_portal_model', 'md' => 'maryland_model', 'ca' => 'california_model', 'pa' => 'pennsylvania_model');
//        $CI->load->library(array('ShipstationLibrary', 'aws_s3'));
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

    public function get_temp_dir() {
        $cronDir = sys_get_temp_dir() . '/Cron/FL/upload/';
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            $cronDir = FCPATH . 'processed/';
        }
        if (!is_dir($cronDir)) {
            mkdir($cronDir, 0777, true);
        }
        return $cronDir;
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

//    public function get_ticket_open($user) {
//        $role = $user['role'];
//        $mid = '';
//        if ($role != '1') {
//            $mid = $user['mid'];
//        }
//        $count = $this->ci->cs_model->get_count_crm_ticket_active($mid);
//        return $count;
//    }
//
//    public function get_returned_mail_pending() {
//        $count = $this->ci->returned_mail_model->get_returned_mail_count('', '', '', 'Pending', '', '');
//        return $count;
//    }
//    public function address_verify_easypost($address_params) {
//        //Easy Post
//        $data = array();
//        require_once APPPATH . "libraries/easypost_lib/easypost.php";
//        if (ENV == 'live') {
//            \EasyPost\EasyPost::setApiKey('G591muszUSA7SSRhyLDVoQ');
//        } else {
//            \EasyPost\EasyPost::setApiKey('0U9jmgUk0gxGCPRVpziYpA');
//        }
//        $address = \EasyPost\Address::create($address_params);
//        if ($address->verifications->delivery->success) {
//            $data['status'] = 'Success';
//            $data['street1'] = $address->street1;
//            $data['street2'] = $address->street2;
//            $data['city'] = $address->city;
//            $data['state'] = $address->state;
//            $data['zip'] = $address->zip;
//            $data['country'] = $address->country;
//            $data['info'] = json_decode($address->verifications->delivery);
//        } else {
//            $data['status'] = 'False';
//            $data['info'] = json_decode($address->verifications->delivery);
//        }
//        //echo json_encode($data);
//        return $data;
//    }
//
//    public function address_verify($data) {
//        $library = FCPATH . 'system/libraries/USPS-php-api-master/USPSAddressVerify.php';
//        if (is_file($library))
//            require_once($library);
//        else
//            die('Can not locate ' . $library);
//        // Initiate and set the username provided from usps
//        $verify = new USPSAddressVerify('593ETAGS6474');
//        $adpart = array('APT', 'BSMT', 'BLDG', 'DEPT', 'FL', 'FRNT', 'HNGR', 'KEY', 'LBBY', 'LOT', 'LOWR', 'OFC', 'PH', 'PIER', 'REAR', 'RM', 'SIDE', 'SLIP', 'SPC', 'STOP', 'STE', 'TRLR', 'UNIT', 'UPPR');
//        $address = new USPSAddress;
//        $data = array_map('strtoupper', $data);
//        $data = array_map('trim', $data);
//        /* $data['shipAddress2'] = str_replace(
//          array(' APARTMENT',' BASEMENT',' BUILDING',' DEPARTMENT',' FLOOR',' FRONT',' HANGER',' KEY',' LOBBY',' LOT',' LOWER',' OFFICE',' PENTHOUSE',' PIER',' REAR',' ROOM',' SIDE',' SLIP',' SPACE',' STOP',' SUITE',' TRAILER',' UNIT',' UPPER'),
//          $adpart, $data['shipAddress2']); */
//        if (strpos($data['shipAddress'], '#') !== false && empty($data['shipAddress2']))
//            list($data['shipAddress'], $data['shipAddress2']) = explode('#', $data['shipAddress'], 2);
//
//        $data['shipAddress'] = preg_replace("/^([0-9]+)([a-z]+)/i", "\\1 \\2", $data['shipAddress']);
//
//        if (!empty($data['shipAddress2'])) {
//            $address->setFirmName('Apartment');
//            $address->setApt($data['shipAddress2']);
//            $address->setAddress($data['shipAddress']);
//            // $address->setAddress($data['shipAddress'] . ',' . $data['shipAddress2']);
//        } else {
//            // $address->setFirmName('');
//            $address->setApt($data['shipAddress2']);
//            $address->setAddress($data['shipAddress']);
//        }
//        $address->setCity($data['shipCity']);
//        $address->setState($data['shipState']);
//        $address->setZip5($data['shipZip']);
//        $address->setZip4('');
//        // Add the address object to the address verify class
//        $verify->addAddress($address);
//        // Perform the request and return result
//        //echo 'Test';
//        //print_r($verify->verify());
//        $verify->verify();
//        //echo 'Test2';
//        //print_r($verify->getArrayResponse());
//        //echo 'Test3';
//        //var_dump($verify->isError());
//        // See if it was successful
//        if ($verify->isSuccess()) {
//            // echo 'Done';
//            $response = array();
//            $address = $verify->getArrayResponse();
//            $response = $address['AddressValidateResponse']['Address'];
//            /* foreach ($adpart as $part) {
//              if (strpos($response['Address2'], ' '.$part)!==false) {
//              list($response['Address2'], $response['Address1']) = str_split($response['Address2'], strpos($response['Address2'], $part));
//              break;
//              }
//              } */
//            $response['error'] = $verify->isError();
//            $response['result'] = 'SUCCESS';
//            // print_r($response);
//            return $response;
//        } else {
//            //echo 'Error: ' . $verify->getErrorMessage();
//            $response = array();
//            $response['error'] = $verify->getErrorMessage();
//            $response['result'] = 'FAIL';
//            return $response;
//        }
//    }
//
//    public function clean_plate($plate) {
//        $plate = strtoupper(preg_replace("/[^A-Za-z0-9]/", '', $plate));
//        //$plate = str_replace(array('o', 'O'), '0', $plate);
//        return $plate;
//    }
//
//    public function clean_vin($vin) {
//        $vin = strtoupper(preg_replace("/[^A-Za-z0-9]/", '', $vin));
//        $vin = str_replace(array('O'), '0', $vin);
//        $vin = str_replace(array('I'), '1', $vin);
//        return $vin;
//    }
//
//    public function get_orders_by_email($state, $email) {
//        $load_model = $this->models[$state];
//        $this->ci->load->model($load_model);
//        $result = $this->ci->$load_model->get_orders_by_email($email);
//
//        if (empty($result) && $state == 'ca') {
//            $this->ci->load->model('california_transfer_model');
//            $result = $this->ci->california_transfer_model->get_orders_by_email($email);
//            if (!empty($result)) {
//                $result['firstname'] = $result['buyerFirstname'];
//            }
//        } elseif (!empty($result) && $state == 'fl') {
//            $result['licensePlate'] = $result['licPlateNum'];
//        }
//
//        return $result;
//    }
//
//    public function get_orders_by_email_and_plate($state, $email, $plate) {
//        $load_model = $this->models[$state];
//        $this->ci->load->model($load_model);
//        $result = $this->ci->$load_model->get_orders_by_email_and_plate($email, $plate);
//
//        if (empty($result) && $state == 'ca') {
//            $this->ci->load->model('california_transfer_model');
//            $result = $this->ci->california_transfer_model->get_orders_by_email_and_plate($email, $plate);
//            if (!empty($result)) {
//                $result['firstname'] = $result['buyerFirstname'];
//                $result['lastname'] = $result['buyerLastname'];
//            }
//        } elseif (!empty($result) && $state == 'fl') {
//            $result['licensePlate'] = $result['licPlateNum'];
//        }
//
//        return $result;
//    }
//
//    public function ship_station_order($order, $state = 'FL', $to, $carrier, $package) {
//        /*$to: etags(office) or customer(customer address)
//        //$carrier: fedex or stamps_com
//        //$package: fedex_envelope or thick_envelope or large_envelope_or_flat*/
//        if(ENV !== 'live')
//            return array('status' => true, 'shipTracking' => '9999', 'shipNotes' => 'Label created for DEV', 'msg' => '');
//
//        $status = false;
//        $createLabel = false;
//        $shipTracking = '';
//        $shipNotes = '';
//        $msg = '';
//        if (empty($order)) {
//            return array('status' => $status);
//        } else {
//
//            if ($state == 'FL') {
//                if ($carrier == 'fedex') {
//                    //fedex_standard_overnight
//                    $order['carrierCode'] = 'fedex';
//                    $order['serviceCode'] = 'fedex_standard_overnight';
//                    $order['packageCode'] = 'fedex_envelope';
//                } elseif ($carrier == 'stamps_com') {
//                    //usps_first_class_mail
//                    $order['carrierCode'] = 'stamps_com';
//                    $order['serviceCode'] = 'usps_first_class_mail';
//                    $order['packageCode'] = $package; //'thick_envelope';
//                }
//                if ($to == 'etags') {
//                    $order['shipName'] = 'ETAGS';
//                    $order['company'] = '';
//                    $order['shipAddress'] = 'P.O. Box 460218';
//                    $order['shipAddress2'] = '';
//                    $order['shipCity'] = 'Fort Lauderdale';
//                    $order['shipState'] = 'FL';
//                    $order['shipZip'] = '33346';
//                }
//                $createLabel = true;
//            } elseif ($state == 'CA') {
//                if ($order['service'] == 'title_transfer' || $order['service'] == 'duplicate_title') {
//                    //usps_first_class_mail
//                    $order['itsRefID'] = $order['id'];
//                    $order['email'] = $order['buyerEmail'];
//                    $order['phone'] = $order['buyerPhone'];
//                    $order['carrierCode'] = 'stamps_com';
//                    $order['serviceCode'] = 'usps_first_class_mail';
//                    $order['packageCode'] = 'thick_envelope';
//                    $order['shipName'] = 'ETAGS';
//                    $order['company'] = '';
//                    $order['shipAddress'] = '5811 McFadden Avenue';
//                    $order['shipAddress2'] = 'Suite 13';
//                    $order['shipCity'] = 'Huntington Beach';
//                    $order['shipState'] = 'CA';
//                    $order['shipZip'] = '92649';
//                    $createLabel = true;
//                }
//            }
//            //C0REATE LABEL
//            if (false) {
//            //if ($createLabel) {
//                //Create shipStation Order
//                $shipStationOrder = $this->ci->shipstationlibrary->addOrder2($order);
//                $order['orderId'] = $shipStationOrder->orderId;
//                $addressVerified = $shipStationOrder->shipTo->addressVerified;
//                if ($addressVerified == 'Address validation failed') {
//                    $this->ship_station_order_void($order['orderId']);
//                    $status = false;
//                    $msg = $addressVerified;
//                } else {
//                    //Create shipStation Label
//                    $label = $this->ci->shipstationlibrary->createLabelOrder($order);
//                    if (empty($label)) {
//                        $this->ship_station_order_void($order['orderId']);
//                        $status = false;
//                        $msg = 'The fedex tag or usps tag could not be created.';
//                    } else {
//                        $status = true;
//                        $shipmentId = $label->shipmentId;
//                        $trackingNumber = (!empty($label->trackingNumber)) ? $label->trackingNumber : rand(1000, 9999);
//                        $labelData = $label->labelData;
//                        //Save Label in S3
//                        $filename = $order['itsRefID'] . '_' . $trackingNumber . '.pdf';
//                        $source = $this->get_temp_dir();
//                        file_put_contents($source . $filename, base64_decode($labelData));
//                        if ($state == 'FL') {
//                            $destination = 'fl_labels/' . $filename;
//                            $bucket = 'fltags.etags.com';
//                        } elseif ($state == 'CA') {
//                            $destination = 'title_transfer_labels/' . $filename;
//                            $bucket = 'ca.etags.com';
//                        }
//                        $s3_source = $source . $filename;
//                        $this->ci->aws_s3->s3push($s3_source, $destination, $bucket);
//                        unlink($source . $filename);
//                        $shipTracking = $trackingNumber;
//                        $shipNotes = $order['orderId'] . '_' . $shipmentId;
//                    }
//                }
//                return array('status' => $status, 'shipTracking' => $shipTracking, 'shipNotes' => $shipNotes, 'msg' => $msg);
//            } else {
//                $status = true;
//                return array('status' => $status, 'shipTracking' => $shipTracking, 'shipNotes' => $shipNotes, 'msg' => $msg);
//            }
//        }
//    }
//
//    public function ship_station_order_void($orderId) {
//        if(ENV !== 'live')
//            return true;
//        $this->ci->shipstationlibrary->deleteOrder2($orderId);
//    }
//
//    public function ship_station_label_void($order) {
//        if(ENV !== 'live')
//            return true;
//        $void = false;
//        if ((!empty($order)) && !empty($order['shipTracking'])) {
//            $pos = strpos($order['shipNotes'], '_');
//            if ($pos !== false) {
//                $orderId = substr($order['shipNotes'], 0, $pos);
//                $shipmentId = substr($order['shipNotes'], $pos + 1);
//                $response = $this->ci->shipstationlibrary->voidLabel($shipmentId);
//                if ($response->approved) {
//                    $void = true;
//                    $this->ship_station_order_void($orderId);
//                }
//            }
//        }
//        return $void;
//    }
}
