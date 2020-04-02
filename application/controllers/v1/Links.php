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
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        $this->load->helper('email');
    }

    public function index_get($id = null) {
        if (!empty($id)) {
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
                $links = $this->Link_model->fetch_links_by_user_id($id, false, $limit, $offset);
                $links_reponse = array();
                $dest_folder = 'Coverart';
                foreach ($links as $link) {
                    $path = $this->s3_path . $dest_folder;
                    $data_image = $this->aws_s3->s3_read($this->bucket, $path, $link['coverart']);
                    $link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
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
        $link['status_id'] = (!empty($this->input->post('status_id'))) ? $this->input->post('status_id') : '1';
        $link['title'] = (!empty($this->input->post('title'))) ? $this->input->post('title') : '';
        $link['url'] = (!empty($this->input->post('url'))) ? $this->input->post('url') : '';
        if ((!empty($link['user_id']) || !empty($link['title'])) && !empty($link['url'])) {
            $link['public'] = (!empty($this->input->post('public'))) ? $this->input->post('public') : '';
            $link['publish_at'] = (!empty($this->input->post('publish_at'))) ? $this->input->post('publish_at') : '';
            $link['timezone'] = (!empty($this->input->post('timezone'))) ? $this->input->post('timezone') : '';
            if (!empty($this->input->post('image'))) {
                $dest_folder = 'Coverart';
                $image = $this->input->post("image");
                preg_match("/^data:image\/(.*);base64/i", $image, $match);
                $ext = (!empty($match[1])) ? $match[1] : '.png';
                $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
                $link['coverart'] = $image_name;
                //upload image to server 
                $source = $this->get_temp_dir();
                file_put_contents($source . '/' . $image_name, file_get_contents($image));
                //SAVE S3
                //$dest_folder = 'Profile';
                $destination = $this->s3_path . $dest_folder . '/' . $image_name;
                $s3_source = $source . '/' . $image_name;
                $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
                unlink($source . '/' . $image_name);
            }
            $link['sort'] = $this->get_last_link_sort($video['user_id']);
            $link['id'] = $this->Link_model->insert_link($link);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The link has been created successfully.', 'id' => $link['id']), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide complete link info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function get_last_link_sort($user_id) {
        $max = $this->Link_model->fetch_max_video_sort($user_id);
        $sort = (empty($max)) ? '1' : ($max + 1);
        return $sort;
    }

    public function index_put($id = null) {
        if (!empty($id)) {
            $link = $this->Link_model->fetch_link_by_id($id);
            if (!empty($link)) {
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
                if (!empty($this->put('publish_at'))) {
                    $link['publish_at'] = $this->put('publish_at');
                }
                if (!empty($this->put('timezone'))) {
                    $link['timezone'] = $this->put('timezone');
                }
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
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $register_user['image']);
                $link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
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
