<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Audios extends RestController {

    private $error;
    private $bucket;
    private $s3_path;
    private $s3_coverart;
    private $s3_audio;
    private $s3_folder;
    private $temp_dir;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model(array('User_model', 'Audio_model', 'Album_model'));
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
        $this->s3_folder = 'Profile';
        $this->temp_dir = $this->general_library->get_temp_dir();
    }

    public function track_type_get() {
        $genres = $this->Audio_model->fetch_track_types();
        $this->response(array('status' => 'success', 'env' => ENV, 'data' => $genres), RestController::HTTP_OK);
    }

    public function audio_key_get() {
        $audio_key = $this->Audio_model->fetch_audio_key();
        $this->response(array('status' => 'success', 'env' => ENV, 'data' => $audio_key), RestController::HTTP_OK);
    }

    public function related_track_get($user_id = null) {
        $data = array();
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $register_user = $this->User_model->fetch_user_by_id($user_id);
            if (!empty($register_user)) {
                $related_audio = $this->Audio_model->fetch_related_audio_by_user_id($user_id);
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

    private function image_decode_put($image) {
        preg_match("/^data:image\/(.*);base64/i", $image, $match);
        $ext = (!empty($match[1])) ? $match[1] : 'png';
        $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
        //upload image to server 
        file_put_contents($this->temp_dir . '/' . $image_name, file_get_contents($image));
        //SAVE S3
        $this->s3_push($image_name, $this->s3_coverart);
        return $image_name;
    }

    private function audio_decode_put($file) {
        preg_match("/^data:audio\/(.*);base64/i", $file, $match);
        $ext = (!empty($match[1])) ? $match[1] : 'mp3';
        $file_name = md5(uniqid(rand(), true)) . '.' . $ext;
        //upload image to server 
        file_put_contents($this->temp_dir . '/' . $file_name, file_get_contents($file));
        //SAVE S3
        $this->s3_push($file_name, $this->s3_audio);
        return $file_name;
    }

    private function file_decode_put($file) {
        preg_match("/^data:file\/(.*);base64/i", $file, $match);
        $ext = (!empty($match[1])) ? $match[1] : 'zip';
        $file_name = md5(uniqid(rand(), true)) . '.' . $ext;
        //upload image to server 
        file_put_contents($this->temp_dir . '/' . $file_name, file_get_contents($file));
        //SAVE S3
        $this->s3_push($file_name, $this->s3_audio);
        return $file_name;
    }

    private function s3_push($file_name, $s3_folder) {
        //SAVE S3
        $source = $this->temp_dir . '/' . $file_name;
        $destination = $this->s3_path . $s3_folder . '/' . $file_name;
        $this->aws_s3->s3push($source, $destination, $this->bucket);
        unlink($this->temp_dir . '/' . $file_name);
    }

    private function audio_clean($audio, $audio_id = null, $images = true) {
        $audio['scheduled'] = true;
        if ($audio['publish_at'] == '0000-00-00 00:00:00' || empty($audio['publish_at'])) {
            $audio['scheduled'] = false;
        }
        $audio['date'] = ($audio['scheduled']) ? substr($audio['publish_at'], 0, 10) : '';
        $audio['time'] = ($audio['scheduled']) ? substr($audio['publish_at'], 11) : '';
        $audio['beat_packs'] = '';
        $audio['licenses'] = '';
        $audio['collaborators'] = '';  
        $audio['marketing'] = '';
        $audio['data_image'] = '';
        $audio['data_untagged_mp3'] = '';
        $audio['data_untagged_wav'] = '';
        $audio['data_track_stems'] = '';
        $audio['data_tagged_file'] = '';
        //Coverart
        $path = $this->s3_path . $this->s3_coverart;
        if ($images) {
            if (!empty($audio['coverart'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
                //$link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
                if (!empty($data_image)) {
                    $img_file = $audio['coverart'];
                    file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
                    $audio['data_image'] = $src;
                    unlink($this->temp_dir . '/' . $audio['coverart']);
                }
            }
        }
        $audio['licenses'] = $this->Audio_model->fetch_audio_license_by_id($audio['id']);
        if (!empty($audio_id)) {
            $audio['beat_packs'] = $this->Album_model->fetch_album_audio_by_id($audio_id);
            $audio['collaborators'] = [];
            $path = $this->s3_path . $this->s3_folder;
            $collaborators = $this->Audio_model->fetch_audio_collaborator_by_id($audio_id);
            foreach ($collaborators as $collaborator) {
                $collaborator['data_image'] = '';
                if (!empty($collaborator['image'])) {
                    $data_image = $this->aws_s3->s3_read($this->bucket, $path, $collaborator['image']);
                    if (!empty($data_image)) {
                        $img_file = $collaborator['image'];
                        file_put_contents($this->temp_dir . '/' . $collaborator['image'], $data_image);
                        $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $collaborator['image']) . ';base64,' . base64_encode($data_image);
                        $collaborator['data_image'] = $src;
                        unlink($this->temp_dir . '/' . $collaborator['image']);
                    }
                }
                $audio['collaborators'][] = $collaborator;
            }
            $audio['marketing'] = $this->Audio_model->fetch_audio_marketing_by_id($audio_id);
            $path = $this->s3_path . $this->s3_audio;
            if (!empty($audio['untagged_mp3'])) {
                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
                if (!empty($data_file)) {
                    $img_file = $audio['untagged_mp3'];
                    file_put_contents($this->temp_dir . '/' . $audio['untagged_mp3'], $data_file);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $audio['untagged_mp3']) . ';base64,' . base64_encode($data_file);
                    $audio['data_untagged_mp3'] = $src;
                    unlink($this->temp_dir . '/' . $audio['untagged_mp3']);
                }
            }
            if (!empty($audio['untagged_wav'])) {
                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
                if (!empty($data_file)) {
                    $img_file = $audio['untagged_wav'];
                    file_put_contents($this->temp_dir . '/' . $audio['untagged_wav'], $data_file);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $audio['untagged_wav']) . ';base64,' . base64_encode($data_file);
                    $audio['data_untagged_wav'] = $src;
                    unlink($this->temp_dir . '/' . $audio['untagged_wav']);
                }
            }
            if (!empty($audio['track_stems'])) {
                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
                if (!empty($data_file)) {
                    $img_file = $audio['track_stems'];
                    file_put_contents($this->temp_dir . '/' . $audio['track_stems'], $data_file);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $audio['track_stems']) . ';base64,' . base64_encode($data_file);
                    $audio['data_track_stems'] = $src;
                    unlink($this->temp_dir . '/' . $audio['track_stems']);
                }
            }
            if (!empty($audio['tagged_file'])) {
                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['tagged_file']);
                if (!empty($data_file)) {
                    $img_file = $audio['tagged_file'];
                    file_put_contents($this->temp_dir . '/' . $audio['tagged_file'], $data_file);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $audio['tagged_file']) . ';base64,' . base64_encode($data_file);
                    $audio['data_tagged_file'] = $src;
                    unlink($this->temp_dir . '/' . $audio['tagged_file']);
                }
            }
        }
        unset($audio['publish_at']);
        //unset($audio['timezone']);
        return $audio;
    }

    public function index_get($id = null, $track_type = null, $audio_id = null) {
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
                $streamys = $this->Audio_model->fetch_streamys_by_user_id($id, $track_type, $audio_id, false, $limit, $offset);
                $streamys_reponse = array();
                $dest_folder = 'Coverart';
                $audios = [];
                foreach ($streamys as $streamy) {
                    //$streamy['related_link'] = $this->Audio_model->fetch_related_links($streamy['id']);
                    $audios[] = $this->audio_clean($streamy, $audio_id);
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $audios), RestController::HTTP_OK);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
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
            $audio['track_type'] = (!empty($this->input->post('track_type'))) ? $this->input->post('track_type') : '';
            $audio['genre_id'] = (!empty($this->input->post('genre_id'))) ? $this->input->post('genre_id') : '';
            $audio['tags'] = (!empty($this->input->post('tags'))) ? $this->input->post('tags') : '';
            $audio['bpm'] = (!empty($this->input->post('bpm'))) ? $this->input->post('bpm') : '';
            $audio['key_id'] = (!empty($this->input->post('key_id'))) ? $this->input->post('key_id') : '';
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
            $audio['sort'] = $this->get_last_audio_sort($audio['user_id']);
            //List
            $beat_packs = (!empty($this->input->post('beat_packs'))) ? json_decode($this->input->post('beat_packs'), TRUE) : '';
            //List
            $collaborators = (!empty($this->input->post('collaborators'))) ? json_decode($this->input->post('collaborators'), TRUE) : '';
            //List
            $licenses = (!empty($this->input->post('licenses'))) ? json_decode($this->input->post('licenses'), TRUE) : '';
            //Marketing
            $marketing = (!empty($this->input->post('marketing'))) ? json_decode($this->input->post('marketing'), TRUE) : '';
            //Audios
            if (!empty($this->input->post('untagged_mp3'))) {
                $untagged_mp3 = $this->input->post("untagged_mp3");
                $audio['untagged_mp3'] = $this->audio_decode_put($untagged_mp3);
            }
            if (!empty($this->input->post('untagged_wav'))) {
                $untagged_wav = $this->input->post("untagged_wav");
                $audio['untagged_wav'] = $this->audio_decode_put($untagged_wav);
            }
            if (!empty($this->input->post('track_stems'))) {
                $track_stems = $this->input->post("track_stems");
                $audio['track_stems'] = $this->file_decode_put($track_stems);
            }
            if (!empty($this->input->post('tagged_file'))) {
                $tagged_file = $this->input->post("tagged_file");
                $audio['tagged_file'] = $this->audio_decode_put($tagged_file);
            }
            $id = $this->Audio_model->insert_audio($audio);
            if (!empty($beat_packs)) {
                //$beat_packs = ['1','2'];
                foreach ($beat_packs as $beat_packs) {
                    $this->Album_model->insert_album_audio(['id_album' => $beat_packs, 'id_audio' => $id]);
                }
            }
            if (!empty($collaborators)) {
                foreach ($collaborators as $collaborator) {
                    $collaborator['audio_id'] = $id;
                    $this->Audio_model->insert_audio_collaborator($collaborator);
                }
            }
            if (!empty($licenses)) {
                foreach ($licenses as $license) {
                    $license['audio_id'] = $id;
                    $this->Audio_model->insert_audio_license($license);
                }
            }
            if (!empty($marketing)) {
                foreach ($marketing as $item) {
                    $item['audio_id'] = $id;
                    $this->Audio_model->insert_audio_marketing($item);
                }
            }
            //REPONSE
            $audio_response = $this->Audio_model->fetch_audio_by_id($id);
            $audio_response = $this->audio_clean($audio_response);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The audio or beat has been created successfully.', 'id' => $id, 'data' => $audio_response), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide complete audio or beat info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function get_last_audio_sort($user_id) {
        $max = $this->Audio_model->fetch_max_audio_sort($user_id);
        $sort = (empty($max)) ? '1' : ($max + 1);
        return $sort;
    }

    public function index_put($id = null) {
        if (!empty($id)) {
            $audio = $this->Audio_model->fetch_audio_by_id($id);
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
                if (!empty($this->put('bpm'))) {
                    $audio['bpm'] = $this->put('bpm');
                }
                if (!empty($this->put('key_id'))) {
                    $audio['key_id'] = $this->put('key_id');
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
                //Audios
                if ($this->put('untagged_mp3') !== null) {
                    if (!empty($this->put('untagged_mp3'))) {
                        $untagged_mp3 = $this->put("untagged_mp3");
                        $audio['untagged_mp3'] = $this->audio_decode_put($untagged_mp3);
                    } else {
                        $audio['untagged_mp3'] = '';
                    }
                }
                if ($this->put('untagged_wav') !== null) {
                    if (!empty($this->put('untagged_wav'))) {
                        $untagged_wav = $this->put("untagged_wav");
                        $audio['untagged_wav'] = $this->audio_decode_put($untagged_wav);
                    } else {
                        $audio['untagged_wav'] = '';
                    }
                }
                if ($this->put('track_stems') !== null) {
                    if (!empty($this->put('track_stems'))) {
                        $track_stems = $this->put("track_stems");
                        $audio['track_stems'] = $this->audio_decode_put($track_stems);
                    } else {
                        $audio['track_stems'] = '';
                    }
                }
                if ($this->put('tagged_file') !== null) {
                    if (!empty($this->put('tagged_file'))) {
                        $tagged_file = $this->put("tagged_file");
                        $audio['tagged_file'] = $this->audio_decode_put($tagged_file);
                    } else {
                        $audio['tagged_file'] = '';
                    }
                }
                //List
                $beat_packs = (!empty($this->put('beat_packs'))) ? json_decode($this->put('beat_packs'), TRUE) : '';
                //List
                $collaborators = (!empty($this->put('collaborators'))) ? json_decode($this->put('collaborators'), TRUE) : '';
                //List
                $licenses = (!empty($this->put('licenses'))) ? json_decode($this->put('licenses'), TRUE) : '';
                //Marketing
                $marketing = (!empty($this->put('marketing'))) ? json_decode($this->put('marketing'), TRUE) : '';
                //
                $this->Album_model->delete_album_audio($id);
                if (!empty($beat_packs)) {
                    //$beat_packs = ['1','2'];
                    foreach ($beat_packs as $beat_packs) {
                        $this->Album_model->insert_album_audio(['id_album' => $beat_packs, 'id_audio' => $id]);
                    }
                }
                $this->Audio_model->delete_audio_collaborator($id);
                if (!empty($collaborators)) {
                    foreach ($collaborators as $collaborator) {
                        $collaborator['audio_id'] = $id;
                        $this->Audio_model->insert_audio_collaborator($collaborator);
                    }
                }
                $this->Audio_model->delete_audio_license($id);
                if (!empty($licenses)) {
                    foreach ($licenses as $license) {
                        $license['audio_id'] = $id;
                        $this->Audio_model->insert_audio_license($license);
                    }
                }
                $this->Audio_model->delete_audio_marketing($id);
                if (!empty($marketing)) {
                    foreach ($marketing as $item) {
                        $item['audio_id'] = $id;
                        $this->Audio_model->insert_audio_marketing($item);
                    }
                }
                $this->Audio_model->update_streamy($id, $audio);
                $audio['date'] = $date;
                $audio['time'] = $time;
                $audio['scheduled'] = $scheduled;
                $audio['public'] = ($audio['public'] == '3') ? '1' : $audio['public'];
                //$video_response = $this->video_clean($video);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Audio or Beat info has been updated successfully.', 'data' => $audio), RestController::HTTP_OK);
            } else {
                $this->error = 'Audio Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Audio ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function sort_audios_post() {
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
                $this->Audio_model->update_streamy($id, array('sort' => $sort));
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The information of the audios has been updated correctly'), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide list of audios.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_delete($id = null) {
        if (!empty($id)) {
            $audio = $this->Audio_model->fetch_audio_by_id($id);
            if (!empty($audio)) {
                if (!$this->general_library->header_token($audio['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $this->Audio_model->update_streamy($id, ['status_id' => '3']);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Audio has been deleted successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Audio Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Audio ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

}
