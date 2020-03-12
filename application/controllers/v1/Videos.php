<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Videos extends RestController {

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
        $this->load->model("Video_model");
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        $this->load->helper('email');
    }

    public function index_get($id = null) {
        if (!empty($id)) {
            $videos = $this->Video_model->fetch_video_by_user_id($id, false, 0, 0);
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $videos), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_post() {
        $video = array();
        $video['user_id'] = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $video['status_id'] = '1';
        $video['title'] = (!empty($this->input->post('title'))) ? $this->input->post('title') : '';
        $video['url'] = (!empty($this->input->post('url'))) ? $this->input->post('url') : '';
        if ((!empty($video['user_id']) || !empty($video['title'])) && !empty($video['url'])) {
            //$video['coverart'] = (!empty($this->input->post('coverart'))) ? $this->input->post('coverart') : '';
            $video['public'] = (!empty($this->input->post('public'))) ? $this->input->post('public') : '';
            $video['publish_at'] = (!empty($this->input->post('publish_at'))) ? $this->input->post('publish_at') : '';
            $video['sort'] = $this->get_last_video_sort($video['user_id']);
            $video['genre_id'] = (!empty($this->input->post('genre_id'))) ? $this->input->post('genre_id') : '';
            $video['related_track'] = (!empty($this->input->post('related_track'))) ? $this->input->post('related_track') : '';
            $video['explicit_content'] = (!empty($this->input->post('explicit_content'))) ? $this->input->post('explicit_content') : '';
            $video['id'] = $this->Video_model->insert_video($video);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The video has been created successfully.', 'id' => $video['id']), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide complete video info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function get_last_video_sort($user_id) {
        $max = $this->Video_model->fetch_max_video_sort($user_id);
        $sort = (empty($max)) ? '1' : ($max + 1);
        return $sort;
    }

    public function index_put($id = null) {
        if (!empty($id)) {
            $register_user = $this->Link_model->fetch_user_by_id($id);
            if (!empty($register_user)) {
                //$user = array();
                if (!empty($this->put('user_name'))) {
                    $register_user['user_name'] = $this->put('user_name');
                }
                if (!empty($this->put('first_name'))) {
                    $register_user['first_name'] = $this->put('first_name');
                }
                if (!empty($this->put('last_name'))) {
                    $register_user['last_name'] = $this->put('last_name');
                }
                if (!empty($this->put('display_name'))) {
                    $register_user['display_name'] = $this->put('display_name');
                }
                if (!empty($this->put('email'))) {
                    $register_user['email'] = $this->put('email');
                }
                if (!empty($this->put('email_confirmed'))) {
                    $register_user['email_confirmed'] = $this->put('email_confirmed');
                }
                if (!empty($this->put('password'))) {
                    $register_user['password'] = $this->general_library->encrypt_txt($this->put('password'));
                }
                if (!empty($this->put('status_id'))) {
                    $register_user['status_id'] = $this->put('status_id');
                }
                if (!empty($this->put('plan_id'))) {
                    $register_user['plan_id'] = $this->put('plan_id');
                }
                if (!empty($this->put('url'))) {
                    $register_user['url'] = $this->put('url');
                }
                if (!empty($this->put('phone'))) {
                    $register_user['phone'] = $this->put('phone');
                }
                if (!empty($this->put('image'))) {
                    //$register_user['image'] = $this->put("image");
                    $image = $this->put("image");
                    preg_match("/^data:image\/(.*);base64/i", $image, $match);
                    $ext = (!empty($match[1])) ? $match[1] : '.png';
                    $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
                    $register_user['image'] = $image_name;
                    //upload image to server 
                    $source = $this->get_temp_dir();
                    file_put_contents($source . '/' . $image_name, file_get_contents($image));
                    //SAVE S3
                    //$bucket = 'files.link.stream';
                    //$path = (ENV == 'live') ? 'Prod/' : 'Dev/';
                    $dest_folder = 'Profile';
                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
                    $s3_source = $source . '/' . $image_name;
                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
                    unlink($source . '/' . $image_name);
                }
                if (!empty($this->put('banner'))) {
                    //$register_user['banner'] = $this->put("banner");
                    $banner = $this->put("banner");
                    preg_match("/^data:image\/(.*);base64/i", $banner, $match);
                    $ext = (!empty($match[1])) ? $match[1] : '.png';
                    $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
                    $register_user['banner'] = $image_name;
                    //upload image to server 
                    $source = $this->get_temp_dir();
                    file_put_contents($source . '/' . $image_name, file_get_contents($banner));
                    //SAVE S3
                    //$bucket = 'files.link.stream';
                    //$path = (ENV == 'live') ? 'Prod/' : 'Dev/';
                    $dest_folder = 'Profile';
                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
                    $s3_source = $source . '/' . $image_name;
                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
                    unlink($source . '/' . $image_name);
                }
                if (!empty($this->put('about'))) {
                    $register_user['about'] = $this->put('about');
                }
                if (!empty($this->put('email_paypal'))) {
                    $register_user['email_paypal'] = $this->put('email_paypal');
                }
                if (!empty($this->put('bio'))) {
                    $register_user['bio'] = $this->put('bio');
                }
                if (!empty($this->put('city'))) {
                    $register_user['city'] = $this->put('city');
                }
                if (!empty($this->put('country'))) {
                    $register_user['country'] = $this->put('country');
                }
                //if (!empty($user)) {
                $this->Link_model->update_user($id, $register_user);
                //}
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The user info has been updated successfully.', 'data' => $register_user), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

}
