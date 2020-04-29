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
    private $s3_folder;
    private $temp_dir;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model("Video_model");
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        //$this->load->helper(array('email'));
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_folder = '';
        $this->temp_dir = $this->general_library->get_temp_dir();
    }

    private function video_clean($video) {
        unset($video['coverart']);
        unset($video['publish_at']);
        //unset($video['timezone']);
        //unset($video['explicit_content']);
        return $video;
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
                    $video['scheduled'] = true;
                    if ($video['publish_at'] == '0000-00-00 00:00:00' || empty($video['publish_at'])) {
                        $video['scheduled'] = false;
                    }
                    $video['date'] = ($video['scheduled']) ? substr($video['publish_at'], 0, 10) : '';
                    $video['time'] = ($video['scheduled']) ? substr($video['publish_at'], 11) : '';
                    $video['public'] = ($video['public'] == '3') ? '1' : $video['public'];
                    $video_response = $this->video_clean($video);
                    $videos_response[] = $video_response;
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
            $video['public'] = (!empty($this->input->post('public'))) ? $this->input->post('public') : '1';
            $video['sort'] = $this->get_last_video_sort($video['user_id']);
            $video['genre_id'] = (!empty($this->input->post('genre_id'))) ? $this->input->post('genre_id') : '';
            $video['related_track'] = (!empty($this->input->post('related_track'))) ? $this->input->post('related_track') : '';
            $id = $this->Video_model->insert_video($video);
            //REPONSE
            $video_response = $this->Video_model->fetch_video_by_id($id);
            $video_response['scheduled'] = false;
            $video_response['date'] = '0000-00-00';
            $video_response['time'] = '00:00:00';
            $video_response['related_track'] = empty($video['related_track']) ? '' : $video['related_track'];
            $video_response = $this->video_clean($video_response);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The video has been created successfully.', 'id' => $id, 'data' => $video_response), RestController::HTTP_OK);
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
                if (!empty($this->put('title'))) {
                    $video['title'] = $this->put('title');
                }
                if (!empty($this->put('url'))) {
                    $video['url'] = $this->put('url');
                }
                if (!empty($this->put('public'))) {
                    $video['public'] = $this->put('public');
                }
                $scheduled = (!empty($this->put('scheduled'))) ? true : false;
                if ($scheduled) {
                    $date = (!empty($this->put('date'))) ? substr($this->put('date'), 0, 10) : '0000-00-00';
                    $time = (!empty($this->put('time'))) ? $this->put('time') : '00:00:00';
                    $video['publish_at'] = $date . ' ' . $time;
                } else {
                    $date = '0000-00-00';
                    $time = '00:00:00';
                    $video['publish_at'] = $date . ' ' . $time;
                }
                if (!empty($this->put('genre_id'))) {
                    $video['genre_id'] = $this->put('genre_id');
                }
                if (!empty($this->put('related_track'))) {
                    $video['related_track'] = $this->put('related_track');
                }
                $this->Video_model->update_video($id, $video);
                $video['date'] = $date;
                $video['time'] = $time;
                $video['scheduled'] = $scheduled;
                $video['public'] = ($video['public'] == '3') ? '1' : $video['public'];
                $video['related_track'] = empty($video['related_track']) ? '' : $video['related_track'];
                $video_response = $this->video_clean($video);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Video info has been updated successfully.', 'data' => $video_response), RestController::HTTP_OK);
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
        if (!empty($list)) {
            $videos = json_decode($list, true);
            foreach ($videos as $video) {
                $id = $video['id'];
                $sort = $video['sort'];
                $this->Video_model->update_video($id, array('sort' => $sort));
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The information of the videos has been updated correctly'), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide list of videos.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_delete($id = null) {
        if (!empty($id)) {
            $video = $this->Video_model->fetch_video_by_id($id);
            if (!empty($video)) {
                if (!$this->general_library->header_token($video['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $this->Video_model->update_video($id, ['status_id' => '3']);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Video has been deleted successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Video Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Video ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

}
