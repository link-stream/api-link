<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Albums extends RestController {

    private $error;
    private $bucket;
    private $s3_path;
    private $s3_coverart;
    private $s3_audio;
    private $temp_dir;
    private $server_url;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model(array('User_model', 'Album_model'));
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
        $this->server_url = 'https://s3.us-east-2.amazonaws.com/files.link.stream/';
    }

    public function related_album_get($user_id = null, $track_type = null) {
        $data = array();
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $register_user = $this->User_model->fetch_user_by_id($user_id);
            if (!empty($register_user)) {
                $title = (!empty($this->input->get('title'))) ? $this->input->get('title') : '';
                $related_audio = $this->Album_model->fetch_related_album_by_user_id($user_id, $title, $track_type);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $related_audio), RestController::HTTP_OK);
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
    private function image_decode_put($image) {
        preg_match("/^data:image\/(.*);base64/i", $image, $match);
        $ext = (!empty($match[1])) ? $match[1] : '.png';
        $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
        //upload image to server 
        file_put_contents($this->temp_dir . '/' . $image_name, file_get_contents($image));
        //SAVE S3
        $this->s3_push($image_name, $this->s3_coverart);
        return $image_name;
    }

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
    private function s3_push($file_name, $s3_folder) {
        //SAVE S3
        $source = $this->temp_dir . '/' . $file_name;
        $destination = $this->s3_path . $s3_folder . '/' . $file_name;
        $this->aws_s3->s3push($source, $destination, $this->bucket);
        unlink($this->temp_dir . '/' . $file_name);
    }

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
//    public function index_get($id = null, $audio_id = null) {
//        if (!empty($id)) {
//            if (!$this->general_library->header_token($id)) {
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//            }
//            $page = (!empty($this->input->get('page'))) ? intval($this->input->get('page')) : 0;
//            $page_size = (!empty($this->input->get('page_size'))) ? intval($this->input->get('page_size')) : 0;
//            //$limit = 0;
//            //$offset = 0;
//            if (!is_int($page) || !is_int($page_size)) {
//                $this->error = 'Parameters page and page_size can only have integer values';
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            } else {
//                $offset = ($page > 0) ? (($page - 1) * $page_size) : 0;
//                $limit = $page_size;
//                $streamys = $this->Audio_model->fetch_streamys_by_user_id($id, $audio_id, false, $limit, $offset);
//                $streamys_reponse = array();
//                $dest_folder = 'Coverart';
//                foreach ($streamys as $streamy) {
//                    $streamy['related_link'] = $this->Audio_model->fetch_related_links($streamy['id']);
//                    $audios[] = $streamy;
//                }
//                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $audios), RestController::HTTP_OK);
//            }
//        } else {
//            $this->error = 'Provide User ID.';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
//    
//    

    public function index_get($id = null, $audio_id = null) {
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
                $streamys = $this->Album_model->fetch_albums_by_user_id($id, $audio_id, false, $limit, $offset);
                $audios = [];
                foreach ($streamys as $streamy) {
                    $audios[] = $this->album_clean($streamy, $audio_id);
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $audios), RestController::HTTP_OK);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function album_clean($audio, $audio_id = null, $images = true) {

        $audio['scheduled'] = true;
        if ($audio['publish_at'] == '0000-00-00 00:00:00' || empty($audio['publish_at'])) {
            $audio['scheduled'] = false;
        }
        $audio['date'] = ($audio['scheduled']) ? substr($audio['publish_at'], 0, 10) : '';
        $audio['time'] = ($audio['scheduled']) ? substr($audio['publish_at'], 11) : '';
        $audio['genre_id'] = !empty($audio['genre_id']) ? $audio['genre_id'] : '';
        $audio['license_id'] = !empty($audio['license_id']) ? $audio['license_id'] : '';
        $audio['data_image'] = '';
        $audio['beats'] = '';
        //Coverart
        $path = $this->s3_path . $this->s3_coverart;
        if ($images) {
            if (!empty($audio['coverart'])) {
                $audio['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
//                if (!empty($data_image)) {
//                    $img_file = $audio['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
//                    $audio['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['coverart']);
//                }
            }
        }
        $audio['beats'] = $this->Album_model->fetch_album_audio_by_album_id($audio['id']);
        return $audio;
    }

    public function index_post() {
        $audio = array();
        $audio['user_id'] = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $audio['status_id'] = '1';
        $audio['title'] = (!empty($this->input->post('title'))) ? $this->input->post('title') : '';
        if ((!empty($audio['user_id']) || !empty($audio['title']))) {
            if (!$this->general_library->header_token($audio['user_id'])) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            if (!empty($this->input->post('image'))) {
                $image = $this->input->post("image");
                $audio['coverart'] = $this->image_decode_put($image);
            }
            $audio['public'] = (!empty($this->input->post('public'))) ? $this->input->post('public') : '1';
            $scheduled = (!empty($this->input->post('scheduled'))) ? true : false;
            if ($scheduled) {
                $date = (!empty($this->input->post('date'))) ? substr($this->input->post('date'), 0, 10) : '0000-00-00';
                $time = (!empty($this->input->post('time'))) ? $this->input->post('time') : '00:00:00';
                $audio['publish_at'] = $date . ' ' . $time;
            } else {
                $date = '0000-00-00';
                $time = '00:00:00';
                $audio['publish_at'] = $date . ' ' . $time;
            }
            $audio['genre_id'] = (!empty($this->input->post('genre_id'))) ? $this->input->post('genre_id') : '';
            $audio['track_type'] = '2';
            $audio['price'] = (!empty($this->input->post('price'))) ? $this->input->post('price') : 0.00;
            $audio['license_id'] = (!empty($this->input->post('license_id'))) ? $this->input->post('license_id') : '';
            $audio['tags'] = (!empty($this->input->post('tags'))) ? $this->input->post('tags') : '';
            $audio['sort'] = $this->get_last_album_sort($audio['user_id']);
            $audio['description'] = (!empty($this->input->post('description'))) ? $this->input->post('description') : '';
            //List
            $beat_list = (!empty($this->input->post('beats'))) ? json_decode($this->input->post('beats'), TRUE) : '';
            $id = $this->Album_model->insert_album($audio);
            if (!empty($beat_list)) {
                //$beat_packs = ['1','2'];
                $i = 1;
                foreach ($beat_list as $beat) {
                    $this->Album_model->insert_album_audio(['id_album' => $id, 'id_audio' => $beat, 'sort' => $i]);
                    $i++;
                }
            }
            //REPONSE
            $audio_response = $this->Album_model->fetch_album_by_id($id);
            $audio_response = $this->album_clean($audio_response);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Album/Beat Pack has been created successfully.', 'id' => $id, 'data' => $audio_response), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide complete album info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_put($id = null) {
        if (!empty($id)) {
            $audio = $this->Album_model->fetch_album_by_id($id);
            if (!empty($audio)) {
                if (!$this->general_library->header_token($audio['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                if (!empty($this->put('title'))) {
                    $audio['title'] = $this->put('title');
                }
                if (!empty($this->put('genre_id'))) {
                    $audio['genre_id'] = $this->put('genre_id');
                }
                if (!empty($this->put('tags'))) {
                    $audio['tags'] = $this->put('tags');
                }
//                if (!empty($this->put('bpm'))) {
//                    $audio['bpm'] = $this->put('bpm');
//                }
//                if (!empty($this->put('key_id'))) {
//                    $audio['key_id'] = $this->put('key_id');
//                }
                if ($this->put('price') !== null) {
                    $audio['price'] = $this->put('price');
                }
                if ($this->put('license_id') !== null) {
                    $audio['license_id'] = $this->put('license_id');
                }
                if ($this->put('description') !== null) {
                    $audio['description'] = $this->put('description');
                }
                if (!empty($this->put('public'))) {
                    $audio['public'] = $this->put('public');
                }
                if ($this->put('scheduled') !== null) {
                    $scheduled = (!empty($this->put('scheduled'))) ? true : false;
                    if ($scheduled) {
                        $date = (!empty($this->put('date'))) ? substr($this->put('date'), 0, 10) : '0000-00-00';
                        $time = (!empty($this->put('time'))) ? $this->put('time') : '00:00:00';
                        $audio['publish_at'] = $date . ' ' . $time;
                    } else {
                        $date = '0000-00-00';
                        $time = '00:00:00';
                        $audio['publish_at'] = $date . ' ' . $time;
                    }
                }
                if ($this->put('image') !== null) {
                    if (!empty($this->put('image'))) {
                        $image = $this->put("image");
                        $audio['coverart'] = $this->image_decode_put($image);
                    } else {
                        $audio['coverart'] = '';
                    }
                }
                //List
                $beat_list = (!empty($this->put('beats'))) ? json_decode($this->put('beats'), TRUE) : '';
                if (!empty($beat_list)) {
                    $this->Album_model->delete_album_audio_by_album($id);
                    $i = 1;
                    foreach ($beat_list as $beat) {
                        $this->Album_model->insert_album_audio(['id_album' => $id, 'id_audio' => $beat, 'sort' => $i]);
                        $i++;
                    }
                }

                //
                $this->Album_model->update_album($id, $audio);
//                $audio['date'] = $date;
//                $audio['time'] = $time;
//                $audio['scheduled'] = $scheduled;
//                $audio['public'] = ($audio['public'] == '3') ? '1' : $audio['public'];
                //REPONSE
                $audio_response = $this->Album_model->fetch_album_by_id($id);
                $audio_response = $this->album_clean($audio_response);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Album/Beat Pack info has been updated successfully.', 'data' => $audio_response), RestController::HTTP_OK);
            } else {
                $this->error = 'Album Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Album ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function get_last_album_sort($user_id) {
        $max = $this->Album_model->fetch_max_album_sort($user_id);
        $sort = (empty($max)) ? '1' : ($max + 1);
        return $sort;
    }

    public function sort_albums_post() {
        $id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        if (!$this->general_library->header_token($id)) {
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
        }
        $list = (!empty($this->input->post('list'))) ? $this->input->post('list') : '';
        if (!empty($list)) {
            $videos = json_decode($list, true);
            foreach ($videos as $video) {
                $id = $video['id'];
                $sort = $video['sort'];
                $this->Album_model->update_album($id, array('sort' => $sort));
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The information of the Album/Beat Pack has been updated correctly'), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide list of albums.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_delete($id = null) {
        if (!empty($id)) {
            $audio = $this->Album_model->fetch_album_by_id($id);
            if (!empty($audio)) {
                if (!$this->general_library->header_token($audio['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $this->Album_model->update_album($id, ['status_id' => '3']);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Album/Beat Pack has been deleted successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Album Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Album ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

//    
//    
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
