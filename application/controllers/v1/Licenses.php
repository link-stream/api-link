<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Licenses extends RestController {

    private $error;
    private $bucket;
    private $s3_path;
    private $s3_coverart;
    private $s3_audio;
    private $temp_dir;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model(array('User_model', 'License_model'));
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        //$this->load->helper('email');
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_coverart = 'Coverart';
        $this->s3_audio = 'Audio';
        $this->temp_dir = $this->general_library->get_temp_dir();
    }

//    public function related_album_get($user_id = null, $track_type = null) {
//        $data = array();
//        if (!empty($user_id)) {
//            if (!$this->general_library->header_token($user_id)) {
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//            }
//            $register_user = $this->User_model->fetch_user_by_id($user_id);
//            if (!empty($register_user)) {
//                $title = (!empty($this->input->get('title'))) ? $this->input->get('title') : '';
//                $related_audio = $this->Album_model->fetch_related_album_by_user_id($user_id, $title, $track_type);
//                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $related_audio), RestController::HTTP_OK);
//            } else {
//                $this->error = 'User Not Found.';
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            }
//        } else {
//            $this->error = 'Provide User ID.';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
//
//    private function image_decode_put($image) {
//        preg_match("/^data:image\/(.*);base64/i", $image, $match);
//        $ext = (!empty($match[1])) ? $match[1] : '.png';
//        $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
//        //upload image to server 
//        file_put_contents($this->temp_dir . '/' . $image_name, file_get_contents($image));
//        //SAVE S3
//        $this->s3_push($image_name, $this->s3_coverart);
//        return $image_name;
//    }
//
//    private function audio_decode_put($file) {
//        preg_match("/^data:image\/(.*);base64/i", $file, $match);
//        $ext = (!empty($match[1])) ? $match[1] : '.png';
//        $file_name = md5(uniqid(rand(), true)) . '.' . $ext;
//        //upload image to server 
//        file_put_contents($this->temp_dir . '/' . $file_name, file_get_contents($file));
//        //SAVE S3
//        $this->s3_push($file_name, $this->s3_audio);
//        return $file_name;
//    }
//
//    private function s3_push($file_name, $s3_folder) {
//        //SAVE S3
//        $source = $this->temp_dir . '/' . $file_name;
//        $destination = $this->s3_path . $s3_folder . '/' . $file_name;
//        $this->aws_s3->s3push($source, $destination, $this->bucket);
//        unlink($this->temp_dir . '/' . $file_name);
//    }
//    private function album_clean($audio, $images = true) {
//        $audio['scheduled'] = true;
//        if ($audio['publish_at'] == '0000-00-00 00:00:00' || empty($audio['publish_at'])) {
//            $audio['scheduled'] = false;
//        }
//        $audio['date'] = ($audio['scheduled']) ? substr($audio['publish_at'], 0, 10) : '';
//        $audio['time'] = ($audio['scheduled']) ? substr($audio['publish_at'], 11) : '';
//        //Coverart
//        $path = $this->s3_path . $this->s3_coverart;
//        $audio['data_image'] = '';
//        if ($images) {
//            if (!empty($audio['coverart'])) {
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
//                //$link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
//                if (!empty($data_image)) {
//                    $img_file = $audio['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
//                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
//                    $audio['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['coverart']);
//                }
//            }
//        }
//        unset($audio['publish_at']);
//        //unset($audio['timezone']);
//        return $link;
//    }
//
    public function index_get($id = null, $license_id = null) {
        if (!empty($id)) {
            if (!$this->general_library->header_token($id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $register_user = $this->User_model->fetch_user_by_id($id);
            if (!empty($register_user)) {
                $licenses_reponse = array();
                $licenses = $this->License_model->fetch_licenses_by_user_id($id, $license_id);
                if (!empty($licenses)) {
                    foreach ($licenses as $license) {
                        //Define if user can use the license (PENDING) ***** 
                        $license['license_available'] = true;
                        $licenses_reponse[] = $license;
                    }
                } else {
                    if (!empty($license_id)) {
                        $this->error = 'License Not Found.';
                        $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                    } else {
                        $licenses = $this->License_model->fetch_licenses();
                        foreach ($licenses as $license) {
                            $license['user_id'] = $id;
                            $license['status_id'] = '1';
                            unset($license['id']);
                            $license_id = $this->License_model->insert_license($license);
                            $license['id'] = $license_id;
                            //Define if user can use the license (PENDING) ***** 
                            $license['license_available'] = true;
                            $licenses_reponse[] = $license;
                        }
                    }
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $licenses_reponse), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

//    
//    public function index_post() {
//        $email = strip_tags($this->input->post('email'));
//        $user_name = strip_tags($this->input->post('user_name'));
//        $platform = strip_tags($this->input->post('platform'));
//        if ((!empty($email) || !empty($user_name)) && !empty($platform)) {
//            $register_user = $this->Link_model->fetch_user_by_search(array('email' => $email));
//            if (empty($register_user)) {
//                $user = array();
//                $user['user_name'] = $user_name;
//                $user['first_name'] = (!empty($this->input->post('first_name'))) ? $this->input->post('first_name') : '';
//                $user['last_name'] = (!empty($this->input->post('last_name'))) ? $this->input->post('last_name') : '';
//                $user['display_name'] = (!empty($this->input->post('display_name'))) ? $this->input->post('display_name') : '';
//                $user['email'] = $email;
//                $user['email_confirmed'] = '1';
//                $user['password'] = (!empty($this->input->post('password'))) ? $this->general_library->encrypt_txt($this->input->post('password')) : '';
//                $user['status_id'] = '3';
//                $user['plan_id'] = '1';
//                $user['url'] = (!empty($this->input->post('url'))) ? $this->input->post('url') : '';
//                $user['phone'] = (!empty($this->input->post('phone'))) ? $this->input->post('phone') : '';
//                $user['image'] = (!empty($this->input->post('image'))) ? $this->input->post('image') : '';
//                $user['banner'] = (!empty($this->input->post('banner'))) ? $this->input->post('banner') : '';
//                $user['about'] = (!empty($this->input->post('about'))) ? $this->input->post('about') : '';
////            $user['youtube'] = (!empty($this->input->post('youtube'))) ? $this->input->post('youtube') : '';
////            $user['facebook'] = (!empty($this->input->post('facebook'))) ? $this->input->post('facebook') : '';
////            $user['instagram'] = (!empty($this->input->post('instagram'))) ? $this->input->post('instagram') : '';
////            $user['twitter'] = (!empty($this->input->post('twitter'))) ? $this->input->post('twitter') : '';
////            $user['soundcloud'] = (!empty($this->input->post('soundcloud'))) ? $this->input->post('soundcloud') : '';
//                $user['email_paypal'] = (!empty($this->input->post('email_paypal'))) ? $this->input->post('email_paypal') : '';
//                $user['platform'] = $platform;
//                $user['platform_id'] = (!empty($this->input->post('platform_id'))) ? $this->input->post('platform_id') : '';
//                $user['platform_token'] = (!empty($this->input->post('platform_token'))) ? $this->input->post('platform_token') : '';
//                $user['bio'] = (!empty($this->input->post('bio'))) ? $this->input->post('bio') : '';
//                $user['id'] = $this->Link_model->insert_user($user);
//                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The user info has been created successfully.', 'id' => $user['id']), RestController::HTTP_OK);
//            } else {
//                $this->error = 'The given email already exists.';
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            }
//        } else {
//            $this->error = 'Provide complete user info to add';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
//
//    public function index_put($id = null) {
//        if (!empty($id)) {
//            $register_user = $this->Link_model->fetch_user_by_id($id);
//            if (!empty($register_user)) {
//                //$user = array();
//                if (!empty($this->put('user_name'))) {
//                    $register_user['user_name'] = $this->put('user_name');
//                }
//                if (!empty($this->put('first_name'))) {
//                    $register_user['first_name'] = $this->put('first_name');
//                }
//                if (!empty($this->put('last_name'))) {
//                    $register_user['last_name'] = $this->put('last_name');
//                }
//                if (!empty($this->put('display_name'))) {
//                    $register_user['display_name'] = $this->put('display_name');
//                }
//                if (!empty($this->put('email'))) {
//                    $register_user['email'] = $this->put('email');
//                }
//                if (!empty($this->put('email_confirmed'))) {
//                    $register_user['email_confirmed'] = $this->put('email_confirmed');
//                }
//                if (!empty($this->put('password'))) {
//                    $register_user['password'] = $this->general_library->encrypt_txt($this->put('password'));
//                }
//                if (!empty($this->put('status_id'))) {
//                    $register_user['status_id'] = $this->put('status_id');
//                }
//                if (!empty($this->put('plan_id'))) {
//                    $register_user['plan_id'] = $this->put('plan_id');
//                }
//                if (!empty($this->put('url'))) {
//                    $register_user['url'] = $this->put('url');
//                }
//                if (!empty($this->put('phone'))) {
//                    $register_user['phone'] = $this->put('phone');
//                }
//                if (!empty($this->put('image'))) {
//                    //$register_user['image'] = $this->put("image");
//                    $image = $this->put("image");
//                    preg_match("/^data:image\/(.*);base64/i", $image, $match);
//                    $ext = (!empty($match[1])) ? $match[1] : '.png';
//                    $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
//                    $register_user['image'] = $image_name;
//                    //upload image to server 
//                    $source = $this->get_temp_dir();
//                    file_put_contents($source . '/' . $image_name, file_get_contents($image));
//                    //SAVE S3
//                    //$bucket = 'files.link.stream';
//                    //$path = (ENV == 'live') ? 'Prod/' : 'Dev/';
//                    $dest_folder = 'Profile';
//                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
//                    $s3_source = $source . '/' . $image_name;
//                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
//                    unlink($source . '/' . $image_name);
//                }
//                if (!empty($this->put('banner'))) {
//                    //$register_user['banner'] = $this->put("banner");
//                    $banner = $this->put("banner");
//                    preg_match("/^data:image\/(.*);base64/i", $banner, $match);
//                    $ext = (!empty($match[1])) ? $match[1] : '.png';
//                    $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
//                    $register_user['banner'] = $image_name;
//                    //upload image to server 
//                    $source = $this->get_temp_dir();
//                    file_put_contents($source . '/' . $image_name, file_get_contents($banner));
//                    //SAVE S3
//                    //$bucket = 'files.link.stream';
//                    //$path = (ENV == 'live') ? 'Prod/' : 'Dev/';
//                    $dest_folder = 'Profile';
//                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
//                    $s3_source = $source . '/' . $image_name;
//                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
//                    unlink($source . '/' . $image_name);
//                }
//                if (!empty($this->put('about'))) {
//                    $register_user['about'] = $this->put('about');
//                }
//                if (!empty($this->put('email_paypal'))) {
//                    $register_user['email_paypal'] = $this->put('email_paypal');
//                }
//                if (!empty($this->put('bio'))) {
//                    $register_user['bio'] = $this->put('bio');
//                }
//                if (!empty($this->put('city'))) {
//                    $register_user['city'] = $this->put('city');
//                }
//                if (!empty($this->put('country'))) {
//                    $register_user['country'] = $this->put('country');
//                }
//                //if (!empty($user)) {
//                $this->Link_model->update_user($id, $register_user);
//                //}
//                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The user info has been updated successfully.', 'data' => $register_user), RestController::HTTP_OK);
//            } else {
//                $this->error = 'User Not Found.';
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            }
//        } else {
//            $this->error = 'Provide User ID.';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
}
