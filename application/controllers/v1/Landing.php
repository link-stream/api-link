<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Landing extends RestController {

    private $error;
    private $bucket;
    private $s3_path;

    public function __construct() {
        parent::__construct();
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'prod/' : 'dev/';
        //Models
        $this->load->model(array('User_model', 'Audio_model', 'Streamy_model'));
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        $this->load->helper('email');
    }

    public function early_access_sms() {
        $email = $this->input->post('email', TRUE);
        $phone = $this->input->post('phone', TRUE);
        if (!empty($email)) {
            $data = array();
            $body = $this->load->view('email/email_coming_soon', $data, true);
            $this->general_library->send_ses($email, $email, 'Streamy', 'noreply@linkstream.com', "You're In! Free Early Access Confirmed", $body);
        }
        if (!empty($phone)) {
            $this->load->library('Aws_pinpoint');
            $this->aws_pinpoint->send($phone, "Welcome! Let's bring social media and streaming music together in 2020. Thanks for registering, we are giving free Pro accounts to all early birds ;) Stay tuned in, we will notify you as soon as you can start your stream! Reply “STOP” to cancel this reminder.");
        }
        $this->Streamy_model->insert_early_access(array('email' => $email, 'phone' => $phone));
        echo json_encode(array('status' => 'Success'));
    }

    public function early_access_post() {
        $email = (!empty($this->input->post('email'))) ? $this->input->post('email', TRUE) : '';
        if (!empty($email)) {
            $this->Streamy_model->insert_early_access(['email' => $email, 'phone' => '']);
            $data = [];
            $body = $this->load->view('app/email/email-coming-soon', $data, true);
            $this->general_library->send_ses($email, $email, 'LinkStream', 'noreply@linkstream.com', "You're In! Free Early Access Confirmed", $body);

            $this->response(['status' => 'success', 'env' => ENV], RestController::HTTP_OK);
        } else {
            $this->error = 'Provide email to add';
            $this->response(['status' => 'false', 'env' => ENV, 'error' => $this->error], RestController::HTTP_BAD_REQUEST);
        }
    }

}
