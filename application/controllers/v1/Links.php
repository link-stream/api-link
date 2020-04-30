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
    private $s3_folder;
    private $temp_dir;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model('Link_model');
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        //$this->load->helper('email');
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_folder = 'Coverart';
        $this->temp_dir = $this->general_library->get_temp_dir();
    }

    private function image_decode_put($image) {
        preg_match("/^data:image\/(.*);base64/i", $image, $match);
        $ext = (!empty($match[1])) ? $match[1] : '.png';
        $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
        //upload image to server 
        file_put_contents($this->temp_dir . '/' . $image_name, file_get_contents($image));
        //SAVE S3
        $this->s3_push($image_name);
        return $image_name;
    }

    private function s3_push($image_name) {
        //SAVE S3
        $source = $this->temp_dir . '/' . $image_name;
        $destination = $this->s3_path . $this->s3_folder . '/' . $image_name;
        $this->aws_s3->s3push($source, $destination, $this->bucket);
        unlink($this->temp_dir . '/' . $image_name);
    }

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
                        $end_date = (!empty($this->input->put('end_date'))) ? substr($this->input->put('end_date'), 0, 10) : '0000-00-00';
                        $end_time = (!empty($this->input->put('end_time'))) ? $this->input->put('end_time') : '00:00:00';
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
