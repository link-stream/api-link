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
        $this->load->model("Streamy_model");
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        $this->load->helper('email');
    }

    public function index_get($id = null, $video_id = null) {
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
                $videos = $this->Video_model->fetch_video_by_user_id($id, $video_id, false, $limit, $offset);
                $videos_response = array();
                foreach ($videos as $video) {
                    $video['date'] = '';
                    $video['time'] = '';
                    if ($video['public'] == '3') {
                        $video['timezone'] = (!empty($video['timezone'])) ? $video['timezone'] : '85';
                        $tz = $this->Streamy_model->fetch_timezones_by_id($video['timezone']);
                        $local_date = $this->general_library->gtm_to_local($tz['zone'], $video['publish_at']);
                        $video['date'] = substr($local_date, 0, 10);
                        $video['time'] = substr($local_date, 11);
                    }
                    $videos_response[] = $video;
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $videos_response), RestController::HTTP_OK);
            }
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
            if (!$this->general_library->header_token($video['user_id'])) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            //$video['coverart'] = (!empty($this->input->post('coverart'))) ? $this->input->post('coverart') : '';
            $video['public'] = (!empty($this->input->post('public'))) ? $this->input->post('public') : '1';
            if ($video['public'] == '3') {
                $date = (!empty($this->input->post('date'))) ? $this->input->post('date') : '';
                $time = (!empty($this->input->post('time'))) ? $this->input->post('time') : '';
                $video['timezone'] = (!empty($this->input->post('timezone'))) ? $this->input->post('timezone') : '';
                //$video['publish_at'] = (!empty($this->input->post('publish_at'))) ? $this->input->post('publish_at') : '';
                $video['publish_at'] = '';
                if (!empty($date) && !empty($time) && !empty($video['timezone'])) {
                    $tz = $this->Streamy_model->fetch_timezones_by_id($video['timezone']);
                    if (!empty($tz)) {
                        $timezone = $tz['zone'];
                        $video['publish_at'] = $this->general_library->local_to_gtm($timezone, $date, $time);
                    }
                }
            }
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
            $video = $this->Video_model->fetch_video_by_id($id);
            if (!empty($video)) {
                if (!$this->general_library->header_token($video['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                if (!empty($this->put('status_id'))) {
                    $video['status_id'] = $this->put('status_id');
                }
                if (!empty($this->put('title'))) {
                    $video['title'] = $this->put('title');
                }
//                if (!empty($this->put('url'))) {
//                    $video['url'] = $this->put('url');
//                }
//                if (!empty($this->put('coverart'))) {
//                    $video['coverart'] = $this->put('coverart');
//                }
                if (!empty($this->put('public'))) {
                    $video['public'] = $this->put('public');
                }
                if ($video['public'] == '3') {
                    $date = (!empty($this->put('date'))) ? $this->put('date') : '';
                    $time = (!empty($this->put('time'))) ? $this->put('time') : '';
                    $video['timezone'] = (!empty($this->put('timezone'))) ? $this->put('timezone') : '';
                    //$video['publish_at'] = (!empty($this->input->post('publish_at'))) ? $this->input->post('publish_at') : '';
                    $video['publish_at'] = '';
                    if (!empty($date) && !empty($time) && !empty($video['timezone'])) {
                        $tz = $this->Streamy_model->fetch_timezones_by_id($video['timezone']);
                        if (!empty($tz)) {
                            $timezone = $tz['zone'];
                            $video['publish_at'] = $this->general_library->local_to_gtm($timezone, $date, $time);
                        }
                    }
                } else {
                    $video['publish_at'] = '';
                }
//                if (!empty($this->put('publish_at'))) {
//                    $video['publish_at'] = $this->put('publish_at');
//                }
//                if (!empty($this->put('timezone'))) {
//                    $video['timezone'] = $this->put('timezone');
//                }
                if (!empty($this->put('sort'))) {
                    $video['sort'] = $this->put('sort');
                }
                if (!empty($this->put('genre_id'))) {
                    $video['genre_id'] = $this->put('genre_id');
                }
                if (!empty($this->put('related_track'))) {
                    $video['related_track'] = $this->put('related_track');
                }
                if (!empty($this->put('explicit_content'))) {
                    $video['explicit_content'] = $this->put('explicit_content');
                }
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
                $this->Video_model->update_video($id, $video);
                $video['date'] = ($video['public'] == '3') ? $date : '';
                $video['time'] = ($video['public'] == '3') ? $time : '';
//                if ($video['public'] == '3') {
//                    $tz = $this->Streamy_model->fetch_timezones_by_id($video['timezone']);
//                    $local_date = $this->general_library->gtm_to_local($tz['zone'], $video['publish_at']);
//                    $video['date'] = substr($local_date, 0, 10);
//                    $video['time'] = substr($local_date, 11);
//                }
                //}
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Video info has been updated successfully.', 'data' => $video), RestController::HTTP_OK);
            } else {
                $this->error = 'Video Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Video ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function sort_videos_post() {
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
            $videos = json_decode($list, true);
            //print_r($videos);
            foreach ($videos as $video) {
                $id = $video['id'];
                $sort = $video['sort'];
                //echo $id . ' ' . $sort . '<br>';
                $this->Video_model->update_video($id, array('sort' => $sort));
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The information of the videos has been updated correctly'), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide list of videos.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

}
