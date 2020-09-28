<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Marketing extends RestController {

    private $error;
    private $bucket;
    private $s3_path;
    private $s3_folder;
    private $temp_dir;
    private $server_url = '';

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model('Marketing_model');
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        //$this->load->helper('email');
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_folder = 'Media';
        $this->temp_dir = $this->general_library->get_temp_dir();
        $this->server_url = 'https://s3.us-east-2.amazonaws.com/files.link.stream/';
    }

//    public function example_get($id = null) {
//        if (!empty($id)) {
//            if (!$this->general_library->header_token($id)) {
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//            }
//            //ACTIONS
//            
//            //END ACTIONS
//        } else {
//            $this->error = 'Provide User ID.';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }

    private function message_clean($message) {
        $message['scheduled'] = true;
        if ($message['publish_at'] == '0000-00-00 00:00:00' || empty($message['publish_at'])) {
            $message['scheduled'] = false;
        }
        $message['date'] = ($message['scheduled']) ? substr($message['publish_at'], 0, 10) : '';
        $message['time'] = ($message['scheduled']) ? substr($message['publish_at'], 11) : '';
        unset($message['publish_at']);
        unset($message['publish_end']);
        return $message;
    }

    private function image_decode_put($image, $user_id = null) {
        preg_match("/^data:image\/(.*);base64/i", $image, $match);
        $ext = (!empty($match[1])) ? $match[1] : 'png';
        $image_name = $user_id . '_' . md5(uniqid(rand(), true)) . '.' . $ext;
        //upload image to server 
        file_put_contents($this->temp_dir . '/' . $image_name, file_get_contents($image));
        //SAVE S3
        $this->s3_push($image_name, $this->s3_folder);
        return $image_name;
    }

    private function s3_push($file_name, $s3_folder) {
        //SAVE S3
        $source = $this->temp_dir . '/' . $file_name;
        $destination = $this->s3_path . $s3_folder . '/' . $file_name;
        $this->aws_s3->s3push($source, $destination, $this->bucket);
        unlink($this->temp_dir . '/' . $file_name);
    }

    public function messages_get($id = null, $message_id = null) {
        if (!empty($id)) {
            if (!$this->general_library->header_token($id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            //ACTIONS
            $page = (!empty($this->input->get('page'))) ? intval($this->input->get('page')) : 0;
            $page_size = (!empty($this->input->get('page_size'))) ? intval($this->input->get('page_size')) : 0;
            if (!is_int($page) || !is_int($page_size)) {
                $this->error = 'Parameters page and page_size can only have integer values';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
            $offset = ($page > 0) ? (($page - 1) * $page_size) : 0;
            $limit = $page_size;
            $messages = $this->Marketing_model->fetch_messages_by_user_id($id, $message_id, false, $limit, $offset);
            $messages_reponse = [];
            foreach ($messages as $message) {
                $message_cleaned = $this->message_clean($message);
                $messages_reponse[] = $message_cleaned;
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $messages_reponse), RestController::HTTP_OK);
            //END ACTIONS
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function messages_post() {
        $message = array();
        $message['user_id'] = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $message['status'] = 'Draft';
        $message['type'] = (!empty($this->input->post('type'))) ? $this->input->post('type') : '';
        if ((!empty($message['user_id']) && !empty($message['type']))) {
            if (!$this->general_library->header_token($message['user_id'])) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            if ($message['type'] != 'Email' && $message['type'] != 'SMS') {
                $this->error = 'Provide a Valid Type';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_UNAUTHORIZED);
            }
            $message['campaing_name'] = (!empty($this->input->post('campaing_name'))) ? $this->input->post('campaing_name') : '';
            $message['send_to'] = (!empty($this->input->post('send_to'))) ? $this->input->post('send_to') : '';
            $message['reply_to_name'] = (!empty($this->input->post('reply_to_name'))) ? $this->input->post('reply_to_name') : '';
            $message['reply_to'] = (!empty($this->input->post('reply_to'))) ? $this->input->post('reply_to') : '';
            $message['subject'] = (!empty($this->input->post('subject'))) ? $this->input->post('subject') : '';
            $message['content'] = (!empty($this->input->post('content'))) ? $this->input->post('content') : '';
            $scheduled = (!empty($this->input->post('scheduled'))) ? true : false;
            if ($scheduled) {
                $date = (!empty($this->input->post('date'))) ? substr($this->input->post('date'), 0, 10) : '0000-00-00';
                $time = (!empty($this->input->post('time'))) ? $this->input->post('time') : '00:00:00';
                $message['publish_at'] = $date . ' ' . $time;
            } else {
                $date = '0000-00-00';
                $time = '00:00:00';
                $message['publish_at'] = $date . ' ' . $time;
            }
            $message['status'] = (!empty($this->input->post('status'))) ? $this->input->post('status') : $message['status'];
            $message['logo'] = (!empty($this->input->post('logo'))) ? $this->input->post('logo') : '';
            $message['artwork'] = (!empty($this->input->post('artwork'))) ? $this->input->post('artwork') : '';
            $message['button_color'] = (!empty($this->input->post('button_color'))) ? $this->input->post('button_color') : '';
            $message['background_color'] = (!empty($this->input->post('background_color'))) ? $this->input->post('background_color') : '';
            $message['background_image'] = (!empty($this->input->post('background_image'))) ? $this->input->post('background_image') : '';
            $message['id'] = $this->Marketing_model->insert_message($message);
            $message_cleaned = $this->message_clean($message);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The message has been created successfully.', 'id' => $message['id'], 'data' => $message_cleaned), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide complete message info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function messages_put($id = null) {
        if (!empty($id)) {
            $message = $this->Marketing_model->fetch_message_by_id($id);
            if (!empty($message)) {
                if (!$this->general_library->header_token($message['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                if (!empty($this->put('campaing_name'))) {
                    $message['campaing_name'] = $this->put('campaing_name');
                }
                if (!empty($this->put('send_to'))) {
                    $message['send_to'] = $this->put('send_to');
                }
                if (!empty($this->put('reply_to_name'))) {
                    $message['reply_to_name'] = $this->put('reply_to_name');
                }
                if (!empty($this->put('reply_to'))) {
                    $message['reply_to'] = $this->put('reply_to');
                }
                if (!empty($this->put('subject'))) {
                    $message['subject'] = $this->put('subject');
                }
                if (!empty($this->put('content'))) {
                    $message['content'] = $this->put('content');
                }
                if (!empty($this->put('campaing_name'))) {
                    $message['campaing_name'] = $this->put('campaing_name');
                }
                if ($this->put('scheduled') !== null) {
                    $scheduled = (!empty($this->put('scheduled'))) ? true : false;
                    if ($scheduled) {
                        $date = (!empty($this->put('date'))) ? substr($this->put('date'), 0, 10) : '0000-00-00';
                        $time = (!empty($this->put('time'))) ? $this->put('time') : '00:00:00';
                        $message['publish_at'] = $date . ' ' . $time;
                    } else {
                        $date = '0000-00-00';
                        $time = '00:00:00';
                        $message['publish_at'] = $date . ' ' . $time;
                    }
                }
                if (!empty($this->put('logo'))) {
                    $message['logo'] = $this->put('logo');
                }
                if (!empty($this->put('artwork'))) {
                    $message['artwork'] = $this->put('artwork');
                }
                if (!empty($this->put('button_color'))) {
                    $message['button_color'] = $this->put('button_color');
                }
                if (!empty($this->put('background_color'))) {
                    $message['background_color'] = $this->put('background_color');
                }
                if (!empty($this->put('background_image'))) {
                    $message['background_image'] = $this->put('background_image');
                }
                if (!empty($this->put('status'))) {
                    $message['status'] = $this->put('status');
                }
                $this->Marketing_model->update_message($id, $message);
                $message_cleaned = $this->message_clean($message);
                //REPONSE
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The message info has been updated successfully.', 'data' => $message_cleaned), RestController::HTTP_OK);
            } else {
                $this->error = 'Message Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Message ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function messages_delete($id = null) {
        if (!empty($id)) {
            $message = $this->Marketing_model->fetch_message_by_id($id);
            if (!empty($message)) {
                if (!$this->general_library->header_token($message['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $this->Marketing_model->update_message($id, ['status' => 'Deleted']);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The message has been deleted successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Meessage Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Message ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function messages_sent_to_get($id = null) {
        if (!empty($id)) {
            if (!$this->general_library->header_token($id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            //ACTIONS
            $list = [
                'all-subscribers' => 'All Subscribers in Audience',
                'new-subscribers' => 'New Subscribers',
                'purchase' => 'Has made a purchase',
                'no-purchase' => "Hasn't Purchased yet"
            ];
            //ADD Tags Subscriber to $list
            $tags_list = $this->Marketing_model->fetch_subscribers_tags_by_user_id($id);
            foreach ($tags_list as $tags) {
                if (!empty($tags['tags'])) {
                    $tags_ar = explode(',', $tags['tags']);
                    foreach ($tags_ar as $tag) {
                        $list[trim(strtolower($tag))] = trim(strtolower($tag));
                    }
                }
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $list), RestController::HTTP_OK);
            //END ACTIONS
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function messages_report_get($id = null, $message_id = null) {
        if (!empty($id)) {
            if (!$this->general_library->header_token($id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            if (empty($message_id)) {
                $this->error = 'Provide Message ID.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
            $message = $this->Marketing_model->fetch_message_by_id($message_id);
            if (empty($message)) {
                $this->error = 'Message not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            } else {
                $message_cleaned = $this->message_clean($message);
                //ACTIONS
                $data = [
                    'Message' => $message_cleaned,
                    'Overview' => [
                        'Total' => '1000',
                        'Open_rate' => '24%',
                        'Click_rate' => '4.66%',
                        'Orders' => '2',
                        'Revenue' => '$33',
                        'Unsubscribed' => '1',
                        'Hours' => [
                            '0000' => ['Open' => '20', 'Click' => '1'],
                            '0100' => ['Open' => '20', 'Click' => '1'],
                            '0200' => ['Open' => '20', 'Click' => '1'],
                            '0300' => ['Open' => '20', 'Click' => '1'],
                            '1500' => ['Open' => '20', 'Click' => '1']
                        ],
                    ],
                    'Activity' => [
                        '05/23/2020 15:37' => 'Open',
                        '05/23/2020 15:00' => 'Open',
                        '05/23/2020 14:21' => 'Click: https://www.linkstream.com/',
                        '05/23/2020 14:20' => 'Open',
                        '05/23/2020 13:37' => 'Open',
                        '05/23/2020 13:00' => 'Open',
                        '05/23/2020 12:20' => 'Open',
                    ]
                ];
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $data), RestController::HTTP_OK);
                //END ACTIONS 
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function tags_get($id = null) {
        if (!empty($id)) {
            if (!$this->general_library->header_token($id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            //ACTIONS
            $list = [];
            //ADD Tags Subscriber to $list
            $tags_list = $this->Marketing_model->fetch_subscribers_tags_by_user_id($id);
            foreach ($tags_list as $tags) {
                if (!empty($tags['tags'])) {
                    $tags_ar = explode(',', $tags['tags']);
                    foreach ($tags_ar as $tag) {
                        $list[trim(strtolower($tag))] = trim(strtolower($tag));
                    }
                }
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $list), RestController::HTTP_OK);
            //END ACTIONS
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function subscribers_get($id = null, $subscriber_id = null) {
        if (!empty($id)) {
            if (!$this->general_library->header_token($id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            //ACTIONS
            $page = (!empty($this->input->get('page'))) ? intval($this->input->get('page')) : 0;
            $page_size = (!empty($this->input->get('page_size'))) ? intval($this->input->get('page_size')) : 0;
            $search = (!empty($this->input->get('search'))) ? $this->input->get('search', TRUE) : '';
            if (!is_int($page) || !is_int($page_size)) {
                $this->error = 'Parameters page and page_size can only have integer values';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
            $offset = ($page > 0) ? (($page - 1) * $page_size) : 0;
            $limit = $page_size;
            $subscribers = $this->Marketing_model->fetch_subscribers_by_user_id($id, $subscriber_id, $search, false, $limit, $offset);
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $subscribers), RestController::HTTP_OK);
            //END ACTIONS
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function subscribers_post() {
        $subscriber = [];
        $subscriber['user_id'] = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $subscriber['email'] = (!empty($this->input->post('email'))) ? $this->input->post('email') : '';
        $subscriber['phone'] = (!empty($this->input->post('phone'))) ? $this->input->post('phone') : '';
        if ((!empty($subscriber['user_id']) && (!empty($subscriber['email']) || !empty($subscriber['phone'])))) {
            if (!$this->general_library->header_token($subscriber['user_id'])) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $subscriber['name'] = (!empty($this->input->post('name'))) ? $this->input->post('name') : '';
            $subscriber['birthday'] = (!empty($this->input->post('birthday'))) ? $this->input->post('birthday') : '';
            $subscriber['gender'] = (!empty($this->input->post('gender'))) ? $this->input->post('gender') : '';
            $subscriber['tags'] = (!empty($this->input->post('tags'))) ? $this->input->post('tags') : '';
            $subscriber['note'] = (!empty($this->input->post('note'))) ? $this->input->post('note') : '';
            $subscriber['email_status'] = (!empty($subscriber['email'])) ? 'subscribed' : 'not subscribed';
            $subscriber['sms_status'] = (!empty($subscriber['phone'])) ? 'subscribed' : 'not subscribed';
            $subscriber['id'] = $this->Marketing_model->insert_subscriber($subscriber);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The subscriber has been created successfully.', 'id' => $subscriber['id'], 'data' => $subscriber), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide complete subscriber info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function subscribers_put($id = null) {
        if (!empty($id)) {
            $subscriber = $this->Marketing_model->fetch_subscriber_by_id($id);
            if (!empty($subscriber)) {
                if (!$this->general_library->header_token($subscriber['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                if (!empty($this->put('email'))) {
                    $subscriber['email'] = $this->put('email');
                }
                if (!empty($this->put('phone'))) {
                    $subscriber['phone'] = $this->put('phone');
                }
                if (!empty($this->put('name'))) {
                    $subscriber['name'] = $this->put('name');
                }
                if (!empty($this->put('birthday'))) {
                    $subscriber['birthday'] = $this->put('birthday');
                }
                if (!empty($this->put('gender'))) {
                    $subscriber['gender'] = $this->put('gender');
                }
                if (!empty($this->put('tags'))) {
                    $subscriber['tags'] = $this->put('tags');
                }
                if (!empty($this->put('note'))) {
                    $subscriber['note'] = $this->put('note');
                }
                $subscriber['email_status'] = (!empty($subscriber['email'])) ? 'subscribed' : 'not subscribed';
                $subscriber['sms_status'] = (!empty($subscriber['phone'])) ? 'subscribed' : 'not subscribed';

                $this->Marketing_model->update_subscriber($id, $subscriber);
                //REPONSE
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The subscriber info has been updated successfully.', 'data' => $subscriber), RestController::HTTP_OK);
            } else {
                $this->error = 'Subscriber Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Subscriber ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function subscribers_action_bulk_post() {
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $action = (!empty($this->input->post('action'))) ? $this->input->post('action') : '';
        $list = (!empty($this->input->post('list'))) ? $this->input->post('list') : ''; //id list
        if (!empty($user_id) && !empty($action) && !empty($list)) {
            if ($action == 'unsubscribe' || $action == 'resubscribe') {
                $list = json_decode($list, true);
                foreach ($list as $item) {
                    $status = ($action == 'unsubscribe') ? 'unsubscribed' : 'subscribed';
                    $this->Marketing_model->update_subscriber($item['id'], ['email_status' => $status, 'sms_status' => $status]);
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'Subscribers updated successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Provide a valid action';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide complete subscriber info to update';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function user_media_files_get($id = null, $media_id = null) {
        if (!empty($id)) {
            if (!$this->general_library->header_token($id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            //ACTIONS
            $files = $this->Marketing_model->fetch_media_files_by_user_id($id, $media_id);
            $response = [];
            foreach ($files as $file) {
                $file['image_url'] = $this->server_url . $file['image_url'] . '/';
                $response[] = $file;
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $response), RestController::HTTP_OK);
            //END ACTIONS
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function user_media_files_post() {
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $media = (!empty($this->input->post('media'))) ? $this->input->post('media') : '';
        if ((!empty($user_id) && !empty($media))) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $media_file = [];
            $media_file['image_name'] = $this->image_decode_put($media, $user_id);
            $media_file['image_url'] = $this->s3_path . $this->s3_folder;
            $media_file['user_id'] = $user_id;
            $media_file['status'] = 'ACTIVE';
            $media_file['id'] = $this->Marketing_model->insert_media_file($media_file);
            $media_file['image_url'] = $this->server_url . $media_file['image_url'] . '/';
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The media has been created successfully.', 'data' => $media_file), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide complete media info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function user_media_files_delete($id = null) {
        if (!empty($id)) {
            $message = $this->Marketing_model->fetch_media_files_by_id($id);
            if (!empty($message)) {
                if (!$this->general_library->header_token($message['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $this->Marketing_model->update_media_files($id, ['status' => 'DELETED']);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The media has been deleted successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Media Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Media ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function subscribers_import_post() {
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $list = (!empty($this->input->post('list'))) ? $this->input->post('list') : '';
        if (!empty($user_id) && !empty($list)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $list = json_decode($list, true);
            foreach ($list as $subscriber) {
//                $subscriber['email'] = (!empty($this->input->post('email'))) ? $this->input->post('email') : '';
//                $subscriber['phone'] = (!empty($this->input->post('phone'))) ? $this->input->post('phone') : '';
//                $subscriber['name'] = (!empty($this->input->post('name'))) ? $this->input->post('name') : '';
//                $subscriber['birthday'] = (!empty($this->input->post('birthday'))) ? $this->input->post('birthday') : '';
//                $subscriber['tags'] = (!empty($this->input->post('tags'))) ? $this->input->post('tags') : '';
//                $subscriber['email_status'] = (!empty($subscriber['email'])) ? 'subscribed' : 'not subscribed';
//                $subscriber['sms_status'] = (!empty($subscriber['phone'])) ? 'subscribed' : 'not subscribed';
                $subscriber['user_id'] = $user_id;
                $subscriber['id'] = $this->Marketing_model->insert_subscriber($subscriber);
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'Subscribers import successfully.'), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide complete subscriber info to import';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    //
    //
    //
    //
//    private function image_decode_put($image) {
//        preg_match("/^data:image\/(.*);base64/i", $image, $match);
//        $ext = (!empty($match[1])) ? $match[1] : '.png';
//        $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
//        //upload image to server 
//        file_put_contents($this->temp_dir . '/' . $image_name, file_get_contents($image));
//        //SAVE S3
//        $this->s3_push($image_name);
//        return $image_name;
//    }



    private function link_clean($link, $images = true) {
        $link['scheduled'] = true;
        if ($link['publish_at'] == '0000-00-00 00:00:00' || empty($link['publish_at'])) {
            $link['scheduled'] = false;
        }
        $link['date'] = ($link['scheduled']) ? substr($link['publish_at'], 0, 10) : '';
        $link['time'] = ($link['scheduled']) ? substr($link['publish_at'], 11) : '';
        $link['end_date'] = ($link['scheduled']) ? (($link['publish_end'] != '0000-00-00 00:00:00') ? substr($link['publish_end'], 0, 10) : '') : '';
        $link['end_time'] = ($link['scheduled']) ? (($link['publish_end'] != '0000-00-00 00:00:00') ? substr($link['publish_end'], 11) : '') : '';



        //Coverart
        $path = $this->s3_path . $this->s3_folder;
        $link['data_image'] = '';
        if ($images) {
            if (!empty($link['coverart'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $link['coverart']);
                //$link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
                if (!empty($data_image)) {
                    $img_file = $link['coverart'];
                    file_put_contents($this->temp_dir . '/' . $link['coverart'], $data_image);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $link['coverart']) . ';base64,' . base64_encode($data_image);
                    $link['data_image'] = $src;
                    unlink($this->temp_dir . '/' . $link['coverart']);
                }
            }
        }
        unset($link['publish_at']);
        unset($link['publish_end']);
        //unset($link['timezone']);
        return $link;
    }

    public function index_get($id = null, $link_id = null) {
        if (!empty($id)) {
            if (!$this->general_library->header_token($id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $page = (!empty($this->input->get('page'))) ? intval($this->input->get('page')) : 0;
            $page_size = (!empty($this->input->get('page_size'))) ? intval($this->input->get('page_size')) : 0;
            //$limit = 0;
            //$offset = 0;
            if (!is_int($page) || !is_int($page_size)) {
                $this->error = 'Parameters page and page_size can only have integer values';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            } else {
                $offset = ($page > 0) ? (($page - 1) * $page_size) : 0;
                $limit = $page_size;
                $links = $this->Link_model->fetch_links_by_user_id($id, $link_id, false, $limit, $offset);
                $links_reponse = array();
                foreach ($links as $link) {
                    $link_reponse = $this->link_clean($link);
                    $links_reponse[] = $link_reponse;
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $links_reponse), RestController::HTTP_OK);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_post() {
        $link = array();
        $link['user_id'] = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $link['status_id'] = '1';
        $link['title'] = (!empty($this->input->post('title'))) ? $this->input->post('title') : '';
        $link['url'] = (!empty($this->input->post('url'))) ? $this->input->post('url') : '';
        if ((!empty($link['user_id']) || !empty($link['title'])) && !empty($link['url'])) {
            if (!$this->general_library->header_token($link['user_id'])) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $link['public'] = (!empty($this->input->post('public'))) ? $this->input->post('public') : '1';
            $scheduled = (!empty($this->input->post('scheduled'))) ? true : false;
            if ($scheduled) {
                $date = (!empty($this->input->post('date'))) ? substr($this->input->post('date'), 0, 10) : '0000-00-00';
                $time = (!empty($this->input->post('time'))) ? $this->input->post('time') : '00:00:00';
                $link['publish_at'] = $date . ' ' . $time;
                $end_date = (!empty($this->input->post('end_date'))) ? substr($this->input->post('end_date'), 0, 10) : '0000-00-00';
                $end_time = (!empty($this->input->post('end_time'))) ? $this->input->post('end_time') : '00:00:00';
                $link['publish_end'] = $end_date . ' ' . $end_time;
            } else {
                $date = '0000-00-00';
                $time = '00:00:00';
                $link['publish_at'] = $date . ' ' . $time;
                $link['publish_end'] = $date . ' ' . $time;
            }
            if (!empty($this->input->post('image'))) {
                $image = $this->input->post("image");
                $link['coverart'] = $this->image_decode_put($image);
            }
            $link['sort'] = $this->get_last_link_sort($link['user_id']);
            $id = $this->Link_model->insert_link($link);
            //REPONSE
            $link_response = $this->Link_model->fetch_link_by_id($id);
            $link_response = $this->link_clean($link_response);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The link has been created successfully.', 'id' => $id, 'data' => $link_response), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide complete link info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function get_last_link_sort($user_id) {
        $max = $this->Link_model->fetch_max_link_sort($user_id);
        $sort = (empty($max)) ? '1' : ($max + 1);
        return $sort;
    }

    public function index_put($id = null) {
        if (!empty($id)) {
            $link = $this->Link_model->fetch_link_by_id($id);
            if (!empty($link)) {
                if (!$this->general_library->header_token($link['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                if (!empty($this->put('title'))) {
                    $link['title'] = $this->put('title');
                }
                if (!empty($this->put('url'))) {
                    $link['url'] = $this->put('url');
                }
                if (!empty($this->put('public'))) {
                    $link['public'] = $this->put('public');
                }
                if ($this->put('scheduled') !== null) {
                    $scheduled = (!empty($this->put('scheduled'))) ? true : false;
                    if ($scheduled) {
                        $date = (!empty($this->put('date'))) ? substr($this->put('date'), 0, 10) : '0000-00-00';
                        $time = (!empty($this->put('time'))) ? $this->put('time') : '00:00:00';
                        $link['publish_at'] = $date . ' ' . $time;
                        $end_date = (!empty($this->put('end_date'))) ? substr($this->put('end_date'), 0, 10) : '0000-00-00';
                        $end_time = (!empty($this->put('end_time'))) ? $this->put('end_time') : '00:00:00';
                        $link['publish_end'] = $end_date . ' ' . $end_time;
                    } else {
                        $date = '0000-00-00';
                        $time = '00:00:00';
                        $link['publish_at'] = $date . ' ' . $time;
                        $link['publish_end'] = $date . ' ' . $time;
                    }
                }

                if ($this->put('image') !== null) {
                    if (!empty($this->put('image'))) {
                        $image = $this->put("image");
                        $link['coverart'] = $this->image_decode_put($image);
                    } else {
                        $link['coverart'] = '';
                    }
                }
                $this->Link_model->update_link($id, $link);
                //REPONSE
                $link_response = $this->link_clean($link);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Link info has been updated successfully.', 'data' => $link_response), RestController::HTTP_OK);
            } else {
                $this->error = 'Link Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Link ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function sort_links_post() {
//        echo json_encode(array(
//        array('id' => '10', 'sort' => '1'),
//        array('id' => '1', 'sort' => '2'),
//        ));
        $id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        if (!$this->general_library->header_token($id)) {
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
        }
        $list = (!empty($this->input->post('list'))) ? $this->input->post('list') : '';
        if (!empty($list)) {
            $links = json_decode($list, true);
            foreach ($links as $link) {
                $id = $link['id'];
                $sort = $link['sort'];
                $this->Link_model->update_link($id, array('sort' => $sort));
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The information of the links has been updated correctly'), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide list of links.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_delete($id = null) {
        if (!empty($id)) {
            $link = $this->Link_model->fetch_link_by_id($id);
            if (!empty($link)) {
                if (!$this->general_library->header_token($link['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $this->Link_model->update_link($id, ['status_id' => '3']);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Link has been deleted successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Link Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Link ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

}
