<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Links extends RestController {

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
        $this->load->model("Link_model");
        $this->load->model("Streamy_model");
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        $this->load->helper('email');
    }
    
    private function get_temp_dir() {
        $cronDir = sys_get_temp_dir() . '';
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            $cronDir = FCPATH . 'tmp';
        }
        if (!is_dir($cronDir)) {
            mkdir($cronDir, 0777, true);
        }
        return $cronDir;
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
                $dest_folder = 'Coverart';
                foreach ($links as $link) {
                    $link['scheduled'] = true;
                    if ($link['publish_at'] == '0000-00-00 00:00:00' || empty($link['publish_at'])) {
                        $link['scheduled'] = false;
                    }
                    $link['date'] = ($link['scheduled']) ? substr($link['publish_at'], 0, 10) : '';
                    $link['time'] = ($link['scheduled']) ? substr($link['publish_at'], 11) : '';
                    //$link['public'] = ($link['public'] == '3') ? '1' : $link['public'];
                    //$link['public'] = ($link['public'] == '3') ? '1' : $link['public'];
//                    $link['date'] = '';
//                    $link['time'] = '';
//                    if ($link['public'] == '3') {
//                        $link['timezone'] = (!empty($link['timezone'])) ? $link['timezone'] : '85';
//                        $tz = $this->Streamy_model->fetch_timezones_by_id($link['timezone']);
//                        $local_date = $this->general_library->gtm_to_local($tz['zone'], $link['publish_at']);
//                        $link['date'] = substr($local_date, 0, 10);
//                        $link['time'] = substr($local_date, 11);
//                    }
                    $path = $this->s3_path . $dest_folder;
                    $link['data_image'] = '';
                    if (!empty($link['coverart'])) {
                        $data_image = $this->aws_s3->s3_read($this->bucket, $path, $link['coverart']);
                        $link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
                    }
//                    $path = $this->s3_path . $dest_folder;
//                    $data_image = $this->aws_s3->s3_read($this->bucket, $path, $link['coverart']);
//                    $link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
//                    unset($link['coverart']);
                    unset($link['publish_at']);
                    unset($link['timezone']);
//                    unset($link['explicit_content']);
                    $links_reponse[] = $link;
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
            } else {
                $date = '0000-00-00';
                $time = '00:00:00';
                $link['publish_at'] = $date . ' ' . $time;
            }
//            if ($link['public'] == '3') {
//                $date = (!empty($this->input->post('date'))) ? $this->input->post('date') : '';
//                $time = (!empty($this->input->post('time'))) ? $this->input->post('time') : '';
//                $link['timezone'] = (!empty($this->input->post('timezone'))) ? $this->input->post('timezone') : '';
//                //$link['publish_at'] = (!empty($this->input->post('publish_at'))) ? $this->input->post('publish_at') : '';
//                $link['publish_at'] = '';
//                if (!empty($date) && !empty($time) && !empty($link['timezone'])) {
//                    $tz = $this->Streamy_model->fetch_timezones_by_id($link['timezone']);
//                    if (!empty($tz)) {
//                        $timezone = $tz['zone'];
//                        $link['publish_at'] = $this->general_library->local_to_gtm($timezone, $date, $time);
//                    }
//                }
//            }
            $dest_folder = 'Coverart';
            if (!empty($this->input->post('image'))) {
                $image = $this->input->post("image");
                preg_match("/^data:image\/(.*);base64/i", $image, $match);
                $ext = (!empty($match[1])) ? $match[1] : '.png';
                $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
                $link['coverart'] = $image_name;
                //upload image to server 
                $source = $this->get_temp_dir();
                file_put_contents($source . '/' . $image_name, file_get_contents($image));
                //SAVE S3
                $destination = $this->s3_path . $dest_folder . '/' . $image_name;
                $s3_source = $source . '/' . $image_name;
                $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
                unlink($source . '/' . $image_name);
            }
            $link['sort'] = $this->get_last_link_sort($link['user_id']);
            $id = $this->Link_model->insert_link($link);
            //REPONSE
            $link_response = $this->Link_model->fetch_link_by_id($id);
            $link_response['scheduled'] = true;
            if ($link_response['publish_at'] == '0000-00-00 00:00:00' || empty($link_response['publish_at'])) {
                $link_response['scheduled'] = false;
            }
            $link_response['date'] = ($link_response['scheduled']) ? substr($link_response['publish_at'], 0, 10) : '';
            $link_response['time'] = ($link_response['scheduled']) ? substr($link_response['publish_at'], 11) : '';
            $link_response['data_image'] = '';
            $path = $this->s3_path . $dest_folder;
            if (!empty($link_response['coverart'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $link_response['coverart']);
                $link_response['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
            }
            unset($link_response['publish_at']);
            unset($link_response['timezone']);
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
                $dest_folder = 'Coverart';
                if (!empty($this->put('status_id'))) {
                    $link['status_id'] = $this->put('status_id');
                }
                if (!empty($this->put('title'))) {
                    $link['title'] = $this->put('title');
                }
                if (!empty($this->put('url'))) {
                    $link['url'] = $this->put('url');
                }
//                if (!empty($this->put('coverart'))) {
//                    $video['coverart'] = $this->put('coverart');
//                }
                if (!empty($this->put('public'))) {
                    $link['public'] = $this->put('public');
                }
                if ($link['public'] == '3') {
                    $date = (!empty($this->put('date'))) ? $this->put('date') : '';
                    $time = (!empty($this->put('time'))) ? $this->put('time') : '';
                    $link['timezone'] = (!empty($this->put('timezone'))) ? $this->put('timezone') : '';
                    //$video['publish_at'] = (!empty($this->input->post('publish_at'))) ? $this->input->post('publish_at') : '';
                    $link['publish_at'] = '';
                    if (!empty($date) && !empty($time) && !empty($link['timezone'])) {
                        $tz = $this->Streamy_model->fetch_timezones_by_id($link['timezone']);
                        if (!empty($tz)) {
                            $timezone = $tz['zone'];
                            $link['publish_at'] = $this->general_library->local_to_gtm($timezone, $date, $time);
                        }
                    }
                } else {
                    $link['publish_at'] = '';
                }
//                if (!empty($this->put('publish_at'))) {
//                    $link['publish_at'] = $this->put('publish_at');
//                }
//                if (!empty($this->put('timezone'))) {
//                    $link['timezone'] = $this->put('timezone');
//                }
                if (!empty($this->put('sort'))) {
                    $link['sort'] = $this->put('sort');
                }
                if (!empty($this->put('image'))) {
                    $image = $this->put("image");
                    preg_match("/^data:image\/(.*);base64/i", $image, $match);
                    $ext = (!empty($match[1])) ? $match[1] : '.png';
                    $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
                    $link['coverart'] = $image_name;
                    //upload image to server 
                    $source = $this->get_temp_dir();
                    file_put_contents($source . '/' . $image_name, file_get_contents($image));
                    //SAVE S3
                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
                    $s3_source = $source . '/' . $image_name;
                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
                    unlink($source . '/' . $image_name);
                }
//                if (!empty($this->put('genre_id'))) {
//                    $video['genre_id'] = $this->put('genre_id');
//                }
//                if (!empty($this->put('related_track'))) {
//                    $video['related_track'] = $this->put('related_track');
//                }
//                if (!empty($this->put('explicit_content'))) {
//                    $video['explicit_content'] = $this->put('explicit_content');
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
                //if (!empty($user)) {
                $this->Link_model->update_link($id, $link);
                $path = $this->s3_path . $dest_folder;
                $link['data_image'] = '';
                if (!empty($link['coverart'])) {
                    $data_image = $this->aws_s3->s3_read($this->bucket, $path, $link['coverart']);
                    $link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
                }
                $link['date'] = ($link['public'] == '3') ? $date : '';
                $link['time'] = ($link['public'] == '3') ? $time : '';
                //}
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Link info has been updated successfully.', 'data' => $link), RestController::HTTP_OK);
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
        //print_r($list);
        if (!empty($list)) {
            $links = json_decode($list, true);
            //print_r($videos);
            foreach ($links as $link) {
                $id = $link['id'];
                $sort = $link['sort'];
                //echo $id . ' ' . $sort . '<br>';
                $this->Link_model->update_link($id, array('sort' => $sort));
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The information of the links has been updated correctly'), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide list of links.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

}
