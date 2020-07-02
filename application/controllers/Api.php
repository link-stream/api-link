<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Api extends REST_Controller {

    private $error;
    
    public function __construct() {
        parent::__construct();
        $this->error = '';
//        $this->load->model("flapp_model");
//        $this->load->model("dmv_portal_model");
//        $this->load->model("pa_qc_model");
    }


    private function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    
    public function index_get() {
        die('Nothing here');
    }

    //https://www.etags.com/web_services/api_dev/received_deals
    public function received_deals_post() {
        $params = json_decode(file_get_contents('php://input'), TRUE);
        $this->error = '';
        if (empty($params['dateIni']) || empty($params['dateEnd']) || empty($params['productCode'])) {
            $this->error = 'dateIni, dateEnd and productCode are Required';
        } elseif ($params['productCode'] != 'T21PA') {
            $this->error = 'productCode ' . $params['productCode'] . ' not available';
        } elseif (!$this->validateDate($params['dateIni'], 'Y-m-d') || !$this->validateDate($params['dateEnd'], 'Y-m-d')) {
            $this->error = 'Invalid dateIni or dateEnd';
        }

        if (!empty($this->error)) {
            //Api Log
//            $this->dmv_portal_model->api_log_insert(array('action' => 'received_deals', 'status' => 'false', 'note' => $error, 'log' => json_encode($this->post())));
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), 404);
        } else {
//            $data = array();
//            $data[] = array('t21id' => '973', 'received_date' => $params['dateIni']);
//            $data = $this->pa_qc_model->get_received_deals($params['dateIni'], $params['dateEnd']);
//            $this->dmv_portal_model->api_log_insert(array('action' => 'received_deals', 'status' => 'success', 'note' => '', 'log' => json_encode($this->post())));
            $this->response(array('status' => 'success', 'env' => ENV, 'dateIni' => $params['dateIni'], 'dateEnd' => $params['dateEnd'], 'productCode' => $params['productCode'], 'data' => $data), 200);
        }
    }

    public function received_deals_get() {
        echo "No GET Service";
    }

    //https://www.etags.com/web_services/api_dev/get_docs
    public function get_docs_post() {
        $params = json_decode(file_get_contents('php://input'), TRUE);
        $error = '';
        if (empty($params['AccountNumber']) || empty($params['ProductCode']) || empty($params['T21Id'])) {
            $error = 'AccountNumber, ProductCode and T21Id are Required';
        } elseif ($params['ProductCode'] != 'T21PA') {
            $error = 'productCode ' . $params['ProductCode'] . ' not available';
        }

        if (!empty($error)) {
            //Api Log
            $this->dmv_portal_model->api_log_insert(array('action' => 'get_docs', 'status' => 'false', 'note' => $error, 'log' => json_encode($this->post())));
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $error), 404);
        } else {
            $this->load->library("aws_s3");
            $data = array('PDF' => '', 'Message' => '');
            //GET LAST DATA_ID BY T21ID
            $data_row = $this->pa_qc_model->get_pa_qc_by_t21id($params['T21Id']);
            if (!empty($data_row)) {
                $file = $params['T21Id'] . '_' . $data_row['data_id'] . '.pdf';
                $path = FCPATH . 'processed/';

                $filename = $path . $file;
                if (is_file($filename)) {
                    $file = file_get_contents($filename);
                    if ($file) {
                        $pdf = $file;
                    }
                } else {
                    $filename = 'processed/' . $file;
                    $path = '';
                    $bucket = 'qc.archive.etags.com';
                    if ($this->aws_s3->fetch_file($filename, $path, $bucket) === false) {
                        $data['Message'] = 'No Documents Found';
                        unlink(FCPATH . 'processed/' . $file);
                    } else {
                        $pdf_name = base_url() . 'processed/' . $file;
                    }
                    $pdf = file_get_contents($pdf_name);
                }
                $data['PDF'] = base64_encode($pdf);
            } else {
                $data['Message'] = 'No Documents Found';
            }
            $this->dmv_portal_model->api_log_insert(array('action' => 'get_docs', 'status' => 'success', 'note' => '', 'log' => json_encode($this->post())));
            $this->response(array('status' => 'success', 'env' => ENV, 'AccountNumber' => $params['AccountNumber'], 'ProductCode' => $params['ProductCode'], 'T21Id' => $params['T21Id'], 'data' => $data), 200);
        }
    }

    public function get_docs_get() {
        echo "No GET Service";
    }

}
