<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class C_notification extends CI_Controller {

    private $temp_dir;

    public function __construct() {
        parent::__construct();
        //Models
        // $this->load->model("User_model");
        //$this->load->model("Streamy_model");
        $this->load->model(array('Marketing_model'));
        //Libraries
        $this->load->library(array('Aws_ses', 'Aws_pinpoint'));
        $this->temp_dir = $this->general_library->get_temp_dir();
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

//    public function action_open() {
//        if (!empty($this->input->get('ref_id'))) {
//            $ref_id = $this->input->get('ref_id');
//            $this->Marketing_model->update_open_action($ref_id);
//        }
//        $imagen_url = (ENV != 'live') ? 'https://dev-link-vue.link.stream/static/img/open.jpg' : 'https://linkstream/static/img/open.jpg';
//        header("Content-Type: image/jpeg"); // it will return image 
//        $logo = file_get_contents($imagen_url);
//        echo $logo;
//    }
//    
    //Messages

    public function messages_cron() {
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        @ob_end_clean();
        set_time_limit(0);
        ob_implicit_flush(1);
        $lockFile = $this->temp_dir . DIRECTORY_SEPARATOR . 'messages_cron.lock';
        if (is_file($lockFile)) {
            if (((time() - filemtime($lockFile)) / 60) > 10) {
                unlink($lockFile);
            }
            die('Messages Cron is Running');
        }
        touch($lockFile);
        $this->messages_pending();
        $this->messages_scheduled();
        unlink($lockFile);
    }

    private function messages_pending() {
        $campaign_limit = 5;
        $messages = $this->Marketing_model->fetch_messages('Pending', null, $campaign_limit);
        foreach ($messages as $message) {
            if ($message['type'] == 'SMS') {
                $this->send_sms_bulk($message);
            } elseif ($message['type'] == 'Email') {
                $this->send_email_bulk($message);
            }
        }
    }

    private function messages_scheduled() {
        $date = date('Y-m-d 00:00:00');
        $campaign_limit = 5;
        $messages = $this->Marketing_model->fetch_messages('Scheduled', $date, $campaign_limit);
        foreach ($messages as $message) {
            if ($message['type'] == 'SMS') {
                $this->send_sms_bulk($message);
            } elseif ($message['type'] == 'Email') {
                $this->send_email_bulk($message);
            }
        }
    }

    private function send_sms_bulk($message) {
        $id = $message['id'];
        $user_id = $message['user_id'];
        $send_to = $message['send_to'];
        $content = $message['content'];
        $title = $message['campaing_name'];
        $segment = (!empty($send_to)) ? $send_to : 'all-subscribers';
        $subscribers = $this->Marketing_model->fetch_subscribers_by_segment($user_id, $segment, 'sms', false);
        $i = 0;
        foreach ($subscribers as $subscriber) {
            if (!empty($subscriber['phone'])) {
                $ref_id = uniqid($id);
                if ($this->aws_pinpoint->send($subscriber['phone'], $content)) {
                    $i++;
                    //LOG
                    $messages_log = [
                        'ref_id' => $ref_id,
                        'user_id' => $user_id,
                        'subscriber_id' => $subscriber['id'],
                        'message_id' => $id,
                        'message_type' => 'sms',
                        'message_title' => $title,
                        'log' => 'Was Sent sms ' . $title,
                        'open' => 0,
                        'click' => 0
                    ];
                    $this->Marketing_model->insert_messages_log($messages_log);
                }
            }
        }
        //UPDATE message status
        $this->Marketing_model->update_message($id, ['status' => 'Sent', 'sent_at' => date("Y-m-d H:i:s"), 'sent_to' => $i]);
    }

    private function send_email_bulk($message) {
        $id = $message['id'];
        $user_id = $message['user_id'];
        $send_to = $message['send_to'];
        $subject = $message['subject'];
        $content = $message['content'];
        $content = str_replace("{EMAIL_UTM_SOURCE}", "email_campaign", $content);
        $reply_to_name = $message['reply_to_name'];
        $reply_to = $message['reply_to'];
        $title = $message['campaing_name'];
        $segment = (!empty($send_to)) ? $send_to : 'all-subscribers';
        $subscribers = $this->Marketing_model->fetch_subscribers_by_segment($user_id, $segment, 'email', false);
        $i = 0;
        foreach ($subscribers as $subscriber) {
            if (!empty($subscriber['email'])) {
                $ref_id = uniqid($id);
                $content = str_replace("{EMAIL_REF_ID}", $ref_id, $content);
                $html_body = $content;
                if ($this->general_library->send_ses($subscriber['name'], $subscriber['email'], 'LinkStream', 'noreply@linkstream.com', $subject, $html_body, $reply_to, $reply_to_name)) {
                    $i++;
                    //LOG
                    $messages_log = [
                        'ref_id' => $ref_id,
                        'user_id' => $user_id,
                        'subscriber_id' => $subscriber['id'],
                        'message_id' => $id,
                        'message_type' => 'email',
                        'message_title' => $title,
                        'log' => 'Was Sent email ' . $title,
                        'open' => 0,
                        'click' => 0
                    ];
                    $this->Marketing_model->insert_messages_log($messages_log);
                }
//             
            }
        }
        //UPDATE message status
        $this->Marketing_model->update_message($id, ['status' => 'Sent', 'sent_at' => date("Y-m-d H:i:s"), 'sent_to' => $i]);
    }

    //Messages End

    public function payouts_cron() {
        $lockFile = $this->temp_dir . DIRECTORY_SEPARATOR . 'payouts_cron.lock';
        if (is_file($lockFile)) {
            if (((time() - filemtime($lockFile)) / 60) > 10) {
                unlink($lockFile);
            }
            die('Payouts Cron is Running');
        }
        touch($lockFile);

        //END
        unlink($lockFile);
    }

    public function split_payments_cron() {
        $lockFile = $this->temp_dir . DIRECTORY_SEPARATOR . 'split_payments_cron.lock';
        if (is_file($lockFile)) {
            if (((time() - filemtime($lockFile)) / 60) > 10) {
                unlink($lockFile);
            }
            die('Split Payments Cron is Running');
        }
        touch($lockFile);

        //END
        unlink($lockFile);
    }

    //Testing

    public function testing_cron() {
        $this->general_library->send_ses('Paul', 'paolofq@gmail.com', 'LinkStream', 'noreply@linkstream.com', 'Linkstream Cron', 'Testng Linkstream Cron: ' . date("Y-m-d H:i:s"), '', '');
    }

}
