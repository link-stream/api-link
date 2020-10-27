<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Profiles extends RestController {

    private $error;
    private $bucket;
    private $s3_path;
    private $s3_folder;
    private $s3_coverart;
    private $temp_dir;
    private $s3_audio;
    private $server_url;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model(array('User_model', 'Audio_model', 'Album_model', 'Video_model', 'Link_model', 'License_model', 'Visitor_model'));
        //Libraries
        $this->load->library(array('Instagram_api', 'aws_s3', 'Aws_pinpoint'));
        $this->load->library('user_agent');
        //Helpers
        //$this->load->helper(array('email'));
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'prod/' : 'dev/';
        $this->s3_folder = 'profile';
        $this->s3_coverart = 'coverart';
        $this->s3_audio = 'audio';
        $this->temp_dir = $this->general_library->get_temp_dir();
        $this->server_url = 'https://s3.us-east-2.amazonaws.com/files.link.stream/';
    }

    private function user_clean($user, $images = true) {
        unset($user['password']);
        unset($user['email_confirmed']);
        unset($user['status_id']);
        //unset($user['facebook']);
        //unset($user['instagram']);
        //unset($user['twitter']);
        //unset($user['soundcloud']);
        //unset($user['youtube']);
        unset($user['platform']);
        unset($user['platform_id']);
        unset($user['platform_token']);
        unset($user['payment_processor']);
        unset($user['payment_processor_key']);
        //NEW
        unset($user['plan_id']);
        unset($user['email_paypal']);
        unset($user['facebook']);
        unset($user['instagram']);
        unset($user['twitter']);
        unset($user['soundcloud']);
        unset($user['youtube']);
        //PENDING
        $user['followers'] = '0';
        $user['plays'] = '0';
        $user['beats'] = '0';
        //
        //Avatar & Banner
        $path = $this->s3_path . $this->s3_folder;
        $user['data_image'] = '';
        $user['data_banner'] = '';
        if ($images) {
            if (!empty($user['image'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $user['image']);
                if (!empty($data_image)) {
                    $img_file = $user['image'];
                    file_put_contents($this->temp_dir . '/' . $user['image'], $data_image);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $user['image']) . ';base64,' . base64_encode($data_image);
                    $user['data_image'] = $src;
                    unlink($this->temp_dir . '/' . $user['image']);
                }
            } else {
                $user['image'] = 'LS_avatar.png';
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $user['image']);
                if (!empty($data_image)) {
                    $img_file = $user['image'];
                    file_put_contents($this->temp_dir . '/' . $user['image'], $data_image);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $user['image']) . ';base64,' . base64_encode($data_image);
                    $user['data_image'] = $src;
                    unlink($this->temp_dir . '/' . $user['image']);
                }
            }
            if (!empty($user['banner'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $user['banner']);
                if (!empty($data_image)) {
                    $img_file = $user['banner'];
                    file_put_contents($this->temp_dir . '/' . $user['banner'], $data_image);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $user['banner']) . ';base64,' . base64_encode($data_image);
                    $user['data_banner'] = $src;
                    unlink($this->temp_dir . '/' . $user['banner']);
                }
            }
        }
        return $user;
    }

    public function index_get($url = null) {
        if (!empty($url)) {
            $register_user = $this->User_model->fetch_user_by_search(['url' => $url]);
            if (!empty($register_user)) {
                $user_response = $this->user_clean_2($register_user);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
            } else {
                $this->error = 'Profile Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Profile URL.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_post() {
        die('Nothing here');
    }

    public function index_put() {
        die('Nothing here');
    }

    public function index_delete() {
        die('Nothing here');
    }

    private function audio_clean($audio, $audio_id = null, $images = true) {
        if ($audio['track_type'] == '2') {
            if ($audio['type'] == 'beat') {
                $audio = $this->beat_clean($audio, $audio_id, $images);
            } else {
                $audio = $this->beat_pack_clean($audio, $audio_id, $images);
            }
        } elseif ($audio['track_type'] == '3') {
            $audio = $this->sound_kit_clean($audio, $audio_id, $images);
        }
        return $audio;
    }

    private function beat_clean($audio, $audio_id = null, $images = true) {

        //unset($audio['type']);
        $audio['scheduled'] = true;
        if ($audio['publish_at'] == '0000-00-00 00:00:00' || empty($audio['publish_at'])) {
            $audio['scheduled'] = false;
        }
        $audio['date'] = ($audio['scheduled']) ? substr($audio['publish_at'], 0, 10) : '';
        $audio['time'] = ($audio['scheduled']) ? substr($audio['publish_at'], 11) : '';

        $audio['genre_id'] = !empty($audio['genre_id']) ? $audio['genre_id'] : '';
        $audio['key_id'] = !empty($audio['key_id']) ? $audio['key_id'] : '';

        $audio['url_user'] = '';
        $audio['url_title'] = '';
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
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
//                //$link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
//                if (!empty($data_image)) {
//                    $img_file = $audio['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
//                    $audio['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['coverart']);
//                }
                $audio['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
                //NEW ENCRYPTED IMAGE
                $final_url = $this->general_library->encode_image_url($audio['user_id'], $this->s3_path . $this->s3_coverart . '/' . $audio['coverart']);
                $audio['data_image'] = $final_url;
                //END ENCRYPTED IMAGE
            }
        }
        $audio['licenses'] = $this->Audio_model->fetch_audio_license_by_id($audio['id']);
        if (!empty($audio_id)) {
            $user = $this->User_model->fetch_user_by_id($audio['user_id']);
            $audio['url_user'] = $user['url'];
            $audio['url_title'] = url_title($audio['title']);
            $audio['beat_packs'] = $this->Album_model->fetch_album_audio_by_id($audio_id);
            $audio['collaborators'] = [];
            $path = $this->s3_path . $this->s3_folder;
            $collaborators = $this->Audio_model->fetch_audio_collaborator_by_id($audio_id);
            foreach ($collaborators as $collaborator) {
                $collaborator['data_image'] = '';
                if (!empty($collaborator['image'])) {
//                    $data_image = $this->aws_s3->s3_read($this->bucket, $path, $collaborator['image']);
//                    if (!empty($data_image)) {
//                        $img_file = $collaborator['image'];
//                        file_put_contents($this->temp_dir . '/' . $collaborator['image'], $data_image);
//                        $src = 'data:' . mime_content_type($this->temp_dir . '/' . $collaborator['image']) . ';base64,' . base64_encode($data_image);
//                        $collaborator['data_image'] = $src;
//                        unlink($this->temp_dir . '/' . $collaborator['image']);
//                    }
                    $collaborator['data_image'] = $this->server_url . $this->s3_path . $this->s3_folder . '/' . $collaborator['image'];
                    //NEW ENCRYPTED IMAGE
                    $final_url = $this->general_library->encode_image_url($audio['user_id'], $this->s3_path . $this->s3_folder . '/' . $collaborator['image']);
                    $collaborator['data_image'] = $final_url;
                    //END ENCRYPTED IMAGE
                }
                $audio['collaborators'][] = $collaborator;
            }
            $audio['marketing'] = $this->Audio_model->fetch_audio_marketing_by_id($audio_id);
            $path = $this->s3_path . $this->s3_audio;
            if (!empty($audio['tagged_file'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['tagged_file']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['tagged_file'];
//                    file_put_contents($this->temp_dir . '/' . $audio['tagged_file'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['tagged_file']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_tagged_file'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['tagged_file']);
//                }
                $audio['data_tagged_file'] = $this->server_url . $this->s3_path . $this->s3_audio . '/' . $audio['tagged_file'];
                //NEW ENCRYPTED AUDIO
                $final_url = $this->general_library->encode_audio_url($audio['user_id'], $this->s3_path . $this->s3_audio . '/' . $audio['tagged_file']);
                $audio['data_tagged_file'] = $final_url;
                //END ENCRYPTED AUDIO
            } elseif (!empty($audio['untagged_mp3']) && empty($audio['data_tagged_file'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['untagged_mp3'];
//                    file_put_contents($this->temp_dir . '/' . $audio['untagged_mp3'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['untagged_mp3']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_untagged_mp3'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['untagged_mp3']);
//                }
                $audio['data_untagged_mp3'] = $this->server_url . $this->s3_path . $this->s3_audio . '/' . $audio['untagged_mp3'];
                //NEW ENCRYPTED AUDIO
                $final_url = $this->general_library->encode_audio_url($audio['user_id'], $this->s3_path . $this->s3_audio . '/' . $audio['untagged_mp3']);
                $audio['data_untagged_mp3'] = $final_url;
                //END ENCRYPTED AUDIO
            } elseif (!empty($audio['untagged_wav']) && empty($audio['data_untagged_mp3'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['untagged_wav'];
//                    file_put_contents($this->temp_dir . '/' . $audio['untagged_wav'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['untagged_wav']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_untagged_wav'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['untagged_wav']);
//                }
                $audio['data_untagged_wav'] = $this->server_url . $this->s3_path . $this->s3_audio . '/' . $audio['untagged_wav'];
                //NEW ENCRYPTED AUDIO
                $final_url = $this->general_library->encode_audio_url($audio['user_id'], $this->s3_path . $this->s3_audio . '/' . $audio['untagged_wav']);
                $audio['data_untagged_wav'] = $final_url;
                //END ENCRYPTED AUDIO
            }
//            if (!empty($audio['untagged_mp3'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['untagged_mp3'];
//                    file_put_contents($this->temp_dir . '/' . $audio['untagged_mp3'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['untagged_mp3']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_untagged_mp3'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['untagged_mp3']);
//                }
//            }
//            if (!empty($audio['untagged_wav'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['untagged_wav'];
//                    file_put_contents($this->temp_dir . '/' . $audio['untagged_wav'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['untagged_wav']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_untagged_wav'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['untagged_wav']);
//                }
//            }
//            if (!empty($audio['track_stems'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['track_stems'];
//                    file_put_contents($this->temp_dir . '/' . $audio['track_stems'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['track_stems']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_track_stems'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['track_stems']);
//                }
//            }
        }
        unset($audio['publish_at']);
        unset($audio['price']);
        unset($audio['samples']);
        unset($audio['description']);
        //PROFILE
        unset($audio['status_id']);
        unset($audio['sort']);
        if ($audio['public'] != '1') {
            $audio = false;
        }
        unset($audio['public']);
        if ($audio['scheduled']) {
            $current_time = date("Y-m-d H:i:s");
            if ($current_time < $audio['date'] . ' ' . $audio['time']) {
                $audio = false;
            }
        }
        unset($audio['scheduled']);
        unset($audio['date']);
        unset($audio['time']);
        return $audio;
    }

    private function sound_kit_clean($audio, $audio_id = null, $images = true) {

        $audio['scheduled'] = true;
        if ($audio['publish_at'] == '0000-00-00 00:00:00' || empty($audio['publish_at'])) {
            $audio['scheduled'] = false;
        }
        $audio['date'] = ($audio['scheduled']) ? substr($audio['publish_at'], 0, 10) : '';
        $audio['time'] = ($audio['scheduled']) ? substr($audio['publish_at'], 11) : '';
        $audio['genre_id'] = !empty($audio['genre_id']) ? $audio['genre_id'] : '';
        $audio['url_user'] = '';
        $audio['url_title'] = '';
        //$audio['kit_files_name'] = [];
        $audio['data_image'] = '';
        $audio['data_track_stems'] = '';
        $audio['data_tagged_file'] = '';
        //Coverart
        $path = $this->s3_path . $this->s3_coverart;
        if ($images) {
            if (!empty($audio['coverart'])) {
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
//                if (!empty($data_image)) {
//                    $img_file = $audio['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
//                    $audio['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['coverart']);
//                }
                $audio['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
                //NEW ENCRYPTED IMAGE
                $final_url = $this->general_library->encode_image_url($audio['user_id'], $this->s3_path . $this->s3_coverart . '/' . $audio['coverart']);
                $audio['data_image'] = $final_url;
                //END ENCRYPTED IMAGE
            }
        }
        if (!empty($audio_id)) {
            $user = $this->User_model->fetch_user_by_id($audio['user_id']);
            $audio['url_user'] = $user['url'];
            $audio['url_title'] = url_title($audio['title']);
            //$path = $this->s3_path . $this->s3_folder;
            $path = $this->s3_path . $this->s3_audio;
            $audio['kit_files_name'] = (!empty($audio['kit_files_name'])) ? json_decode($audio['kit_files_name']) : [];
//            if (!empty($audio['track_stems'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['track_stems'];
//                    file_put_contents($this->temp_dir . '/' . $audio['track_stems'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['track_stems']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_track_stems'] = $src;
//
//                    //Audio List.
//                    $zip = new ZipArchive;
//                    $res = $zip->open($this->temp_dir . '/' . $audio['track_stems']);
//                    if ($res === TRUE) {
//                        for ($i = 0; $i < $zip->numFiles; $i++) {
//                            $filename = $zip->getNameIndex($i);
//                            $pos = strpos($filename, 'MACOSX/.');
//                            if ($pos === false) {
//                                $audio['kit_files_name'][] = $filename;
//                            }
//                        }
//                    }
//                    $audio['samples'] = count($audio['kit_files_name']);
//                    $this->Audio_model->update_streamy($audio['id'], ['samples' => $audio['samples']]);
//                    //
//                    unlink($this->temp_dir . '/' . $audio['track_stems']);
//                }
//            }
            if (!empty($audio['tagged_file'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['tagged_file']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['tagged_file'];
//                    file_put_contents($this->temp_dir . '/' . $audio['tagged_file'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['tagged_file']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_tagged_file'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['tagged_file']);
//                }
                $audio['data_tagged_file'] = $this->server_url . $this->s3_path . $this->s3_audio . '/' . $audio['tagged_file'];
                //NEW ENCRYPTED AUDIO
                $final_url = $this->general_library->encode_audio_url($audio['user_id'], $this->s3_path . $this->s3_audio . '/' . $audio['tagged_file']);
                $audio['data_tagged_file'] = $final_url;
                //END ENCRYPTED AUDIO
            }
        }
        unset($audio['publish_at']);
        unset($audio['bpm']);
        unset($audio['key_id']);
        unset($audio['untagged_mp3_name']);
        unset($audio['untagged_mp3']);
        unset($audio['untagged_wav_name']);
        unset($audio['untagged_wav']);
        unset($audio['key_id']);
        //PROFILE
        unset($audio['status_id']);
        unset($audio['sort']);
        if ($audio['public'] != '1') {
            $audio = false;
        }
        unset($audio['public']);
        if ($audio['scheduled']) {
            $current_time = date("Y-m-d H:i:s");
            if ($current_time < $audio['date'] . ' ' . $audio['time']) {
                $audio = false;
            }
        }
        unset($audio['scheduled']);
        unset($audio['date']);
        unset($audio['time']);
        return $audio;
    }

    private function beat_pack_clean($audio, $audio_id = null, $images = true) {

        //unset($audio['type']);
        $audio['scheduled'] = true;
        if ($audio['publish_at'] == '0000-00-00 00:00:00' || empty($audio['publish_at'])) {
            $audio['scheduled'] = false;
        }
        $audio['date'] = ($audio['scheduled']) ? substr($audio['publish_at'], 0, 10) : '';
        $audio['time'] = ($audio['scheduled']) ? substr($audio['publish_at'], 11) : '';
        $audio['genre_id'] = !empty($audio['genre_id']) ? $audio['genre_id'] : '';
        $audio['license_id'] = !empty($audio['license_id']) ? $audio['license_id'] : '';
        $audio['data_image'] = '';


//        $audio['url_user'] = '';
//        $audio['url_title'] = '';
        $audio['beats'] = '';
//        $audio['licenses'] = '';
//        $audio['collaborators'] = '';
//        $audio['marketing'] = '';
//        $audio['data_image'] = '';
//        $audio['data_untagged_mp3'] = '';
//        $audio['data_untagged_wav'] = '';
//        $audio['data_track_stems'] = '';
//        $audio['data_tagged_file'] = '';
        //Coverart
        $path = $this->s3_path . $this->s3_coverart;
        if ($images) {
            if (!empty($audio['coverart'])) {
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
//                if (!empty($data_image)) {
//                    $img_file = $audio['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
//                    $audio['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['coverart']);
//                }
                $audio['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
                //NEW ENCRYPTED IMAGE
                $final_url = $this->general_library->encode_image_url($audio['user_id'], $this->s3_path . $this->s3_coverart . '/' . $audio['coverart']);
                $audio['data_image'] = $final_url;
                //END ENCRYPTED IMAGE
            }
        }
        $audio['beats'] = $this->Album_model->fetch_album_audio_by_album_id($audio['id']);
//        $audio['licenses'] = $this->Audio_model->fetch_audio_license_by_id($audio['id']);
        if (!empty($audio_id)) {
//            $user = $this->User_model->fetch_user_by_id($audio['user_id']);
//            $audio['url_user'] = $user['url'];
//            $audio['url_title'] = url_title($audio['title']);
//            $audio['beats'] = $this->Album_model->fetch_album_audio_by_album_id($audio_id);
//            $audio['collaborators'] = [];
//            $path = $this->s3_path . $this->s3_folder;
//            $collaborators = $this->Audio_model->fetch_audio_collaborator_by_id($audio_id);
//            foreach ($collaborators as $collaborator) {
//                $collaborator['data_image'] = '';
//                if (!empty($collaborator['image'])) {
//                    $data_image = $this->aws_s3->s3_read($this->bucket, $path, $collaborator['image']);
//                    if (!empty($data_image)) {
//                        $img_file = $collaborator['image'];
//                        file_put_contents($this->temp_dir . '/' . $collaborator['image'], $data_image);
//                        $src = 'data:' . mime_content_type($this->temp_dir . '/' . $collaborator['image']) . ';base64,' . base64_encode($data_image);
//                        $collaborator['data_image'] = $src;
//                        unlink($this->temp_dir . '/' . $collaborator['image']);
//                    }
//                }
//                $audio['collaborators'][] = $collaborator;
//            }
//            $audio['marketing'] = $this->Audio_model->fetch_audio_marketing_by_id($audio_id);
//            $path = $this->s3_path . $this->s3_audio;
//            if (!empty($audio['untagged_mp3'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['untagged_mp3'];
//                    file_put_contents($this->temp_dir . '/' . $audio['untagged_mp3'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['untagged_mp3']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_untagged_mp3'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['untagged_mp3']);
//                }
//            }
//            if (!empty($audio['untagged_wav'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['untagged_wav'];
//                    file_put_contents($this->temp_dir . '/' . $audio['untagged_wav'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['untagged_wav']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_untagged_wav'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['untagged_wav']);
//                }
//            }
//            if (!empty($audio['track_stems'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['track_stems'];
//                    file_put_contents($this->temp_dir . '/' . $audio['track_stems'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['track_stems']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_track_stems'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['track_stems']);
//                }
//            }
//            if (!empty($audio['tagged_file'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['tagged_file']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['tagged_file'];
//                    file_put_contents($this->temp_dir . '/' . $audio['tagged_file'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['tagged_file']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_tagged_file'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['tagged_file']);
//                }
//            }
        }
//        unset($audio['publish_at']);
//        unset($audio['price']);
//        unset($audio['samples']);
//        unset($audio['description']);
        return $audio;
    }

    public function sound_kits_get($id = null, $audio_id = null) {
        if (!empty($id)) {
            $page = (!empty($this->input->get('page'))) ? intval($this->input->get('page')) : 0;
            $page_size = (!empty($this->input->get('page_size'))) ? intval($this->input->get('page_size')) : 0;
            $sort = (!empty($this->input->get('sort'))) ? $this->input->get('sort') : 'default';
            $tag = (!empty($this->input->get('tag'))) ? $this->input->get('tag') : '';
            $genre = (!empty($this->input->get('genre'))) ? $this->input->get('genre') : '';
            if (!is_int($page) || !is_int($page_size)) {
                $this->error = 'Parameters page and page_size can only have integer values';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            } else {
                $offset = ($page > 0) ? (($page - 1) * $page_size) : 0;
                $limit = $page_size;
                $streamys = $this->Audio_model->fetch_sound_kit_by_profile($id, $audio_id, $genre, $tag, $sort, $limit, $offset);
                $audios = [];
                foreach ($streamys as $streamy) {
                    $audio_response = $this->audio_clean($streamy, $audio_id);
                    if (!empty($audio_response)) {
                        $audios[] = $audio_response;
                    }
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $audios), RestController::HTTP_OK);
            }
        } else {
            $this->error = 'Provide Peoducer ID';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function beats_get($id = null, $audio_id = null) {
        if (!empty($id)) {
            $page = (!empty($this->input->get('page'))) ? intval($this->input->get('page')) : 0;
            $page_size = (!empty($this->input->get('page_size'))) ? intval($this->input->get('page_size')) : 0;
            $sort = (!empty($this->input->get('sort'))) ? $this->input->get('sort') : 'default';
            $tag = (!empty($this->input->get('tag'))) ? $this->input->get('tag') : '';
            $genre = (!empty($this->input->get('genre'))) ? $this->input->get('genre') : '';
            $bpm_min = (!empty($this->input->get('bpm_min'))) ? $this->input->get('bpm_min') : '';
            $bpm_max = (!empty($this->input->get('bpm_max'))) ? $this->input->get('bpm_max') : '';
            $beat_type = (!empty($this->input->get('type'))) ? $this->input->get('type') : '';
            if (!is_int($page) || !is_int($page_size)) {
                $this->error = 'Parameters page and page_size can only have integer values';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            } else {
                $offset = ($page > 0) ? (($page - 1) * $page_size) : 0;
                $limit = $page_size;
                if (!empty($audio_id)) {
                    if (empty($beat_type)) {
                        $this->error = 'Provide Type';
                        $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                    } elseif ($beat_type != 'beat' & $beat_type != 'pack') {
                        $this->error = 'Provide a Valid Type';
                        $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                    }
                }
                //$streamys = $this->Audio_model->fetch_beat_by_profile($id, $audio_id, $genre, $tag, $bpm_min, $bpm_max, $sort, $limit, $offset);
                $streamys = $this->Audio_model->fetch_beats_by_profile($id, $audio_id, $genre, $tag, $bpm_min, $bpm_max, $beat_type, $sort, $limit, $offset, null);
                $audios = [];
                foreach ($streamys as $streamy) {
                    $audio_response = $this->audio_clean($streamy, $audio_id);
                    if (!empty($audio_response)) {
                        $audios[] = $audio_response;
                    }
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $audios), RestController::HTTP_OK);
            }
        } else {
            $this->error = 'Provide Peoducer ID';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

//    public function audios_get($id = null, $track_type = null, $audio_id = null) {
//        if (!empty($id) && !empty($track_type)) {
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
//                $streamys = $this->Audio_model->fetch_streamys_by_user_id($id, $track_type, $audio_id, false, $limit, $offset);
//                $audios = [];
//                foreach ($streamys as $streamy) {
//                    $audio_response = $this->audio_clean($streamy, $audio_id);
//                    if (!empty($audio_response)) {
//                        $audios[] = $audio_response;
//                    }
//                }
//                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $audios), RestController::HTTP_OK);
//            }
//        } else {
//            $this->error = 'Provide Peoducer ID and Track Type';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }

    private function video_clean($video) {
        unset($video['coverart']);
        unset($video['publish_at']);
        //unset($video['timezone']);
        //unset($video['explicit_content']);
        //PROFILE
        unset($video['status_id']);
        unset($video['sort']);
        if ($video['public'] != '1') {
            $video = false;
        }
        unset($video['public']);
        //unset($video['genre_id']);
        unset($video['related_track']);
        if ($video['scheduled']) {
            $current_time = date("Y-m-d H:i:s");
            if ($current_time < $video['date'] . ' ' . $video['time']) {
                $video = false;
            }
        }
        unset($video['scheduled']);
        unset($video['date']);
        unset($video['time']);
        return $video;
    }

    public function videos_get($id = null, $video_id = null) {
        if (!empty($id)) {
            $page = (!empty($this->input->get('page'))) ? intval($this->input->get('page')) : 0;
            $page_size = (!empty($this->input->get('page_size'))) ? intval($this->input->get('page_size')) : 0;
            $sort = (!empty($this->input->get('sort'))) ? $this->input->get('sort') : 'default';
            $tag = (!empty($this->input->get('tag'))) ? $this->input->get('tag') : '';
            $genre = (!empty($this->input->get('genre'))) ? $this->input->get('genre') : '';
            if (!is_int($page) || !is_int($page_size)) {
                $this->error = 'Parameters page and page_size can only have integer values';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            } else {
                $offset = ($page > 0) ? (($page - 1) * $page_size) : 0;
                $limit = $page_size;
                $videos = $this->Video_model->fetch_video_by_profile($id, $video_id, $genre, $tag, $sort, $limit, $offset);
                $videos_response = [];
                foreach ($videos as $video) {
                    $video['scheduled'] = true;
                    if ($video['publish_at'] == '0000-00-00 00:00:00' || empty($video['publish_at'])) {
                        $video['scheduled'] = false;
                    }
                    $video['date'] = ($video['scheduled']) ? substr($video['publish_at'], 0, 10) : '';
                    $video['time'] = ($video['scheduled']) ? substr($video['publish_at'], 11) : '';
                    $video['public'] = ($video['public'] == '3') ? '1' : $video['public'];
                    $video_response = $this->video_clean($video);
                    if (!empty($video_response)) {
                        $videos_response[] = $video_response;
                    }
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $videos_response), RestController::HTTP_OK);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
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
        $path = $this->s3_path . $this->s3_coverart;
        $link['data_image'] = '';
        if ($images) {
            if (!empty($link['coverart'])) {
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $link['coverart']);
//                if (!empty($data_image)) {
//                    $img_file = $link['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $link['coverart'], $data_image);
//                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $link['coverart']) . ';base64,' . base64_encode($data_image);
//                    $link['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $link['coverart']);
//                }
                $link['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $link['coverart'];
                //NEW ENCRYPTED IMAGE
                $final_url = $this->general_library->encode_image_url($link['user_id'], $this->s3_path . $this->s3_coverart . '/' . $link['coverart']);
                $link['data_image'] = $final_url;
                //END ENCRYPTED IMAGE
            }
        }
        unset($link['publish_at']);
        unset($link['publish_end']);
        //PROFILE
        unset($link['status_id']);
        unset($link['sort']);
        if ($link['public'] != '1') {
            $link = false;
        }
        unset($link['public']);
        if ($link['scheduled']) {
            $current_time = date("Y-m-d H:i:s");
            if ($current_time < $link['date'] . ' ' . $link['time']) {
                $link = false;
            }
            if (!empty($link['end_date']) && $current_time > $link['end_date'] . ' ' . $link['end_time']) {
                $link = false;
            }
        }
        unset($link['scheduled']);
        unset($link['date']);
        unset($link['time']);
        unset($link['end_date']);
        unset($link['end_time']);
        return $link;
    }

    public function links_get($id = null, $link_id = null) {
        if (!empty($id)) {
            $page = (!empty($this->input->get('page'))) ? intval($this->input->get('page')) : 0;
            $page_size = (!empty($this->input->get('page_size'))) ? intval($this->input->get('page_size')) : 0;
            $sort = (!empty($this->input->get('sort'))) ? $this->input->get('sort') : 'default';
            $tag = (!empty($this->input->get('tag'))) ? $this->input->get('tag') : '';
            if (!is_int($page) || !is_int($page_size)) {
                $this->error = 'Parameters page and page_size can only have integer values';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            } else {
                $offset = ($page > 0) ? (($page - 1) * $page_size) : 0;
                $limit = $page_size;
                $links = $this->Link_model->fetch_links_by_profile($id, $link_id, $tag, $sort, $limit, $offset);
                $links_reponse = [];
                foreach ($links as $link) {
                    $link_reponse = $this->link_clean($link);
                    if (!empty($link_reponse)) {
                        $links_reponse[] = $link_reponse;
                    }
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $links_reponse), RestController::HTTP_OK);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function genres_get($id = null, $type = null) {
        if (!empty($id) && !empty($type)) {
            $genres = [];
            if ($type == 'beats') {
                $genres = $this->Audio_model->fetch_beats_genres_by_profile($id);
            } elseif ($type == 'kits') {
                $genres = $this->Audio_model->fetch_sound_kits_genres_by_profile($id);
            } elseif ($type == 'videos') {
                $genres = $this->Video_model->fetch_videos_genres_by_profile($id);
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $genres), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID AND/OR Type';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function licenses_get($id = null, $license_id = null) {
        if (!empty($id)) {
            $licenses = $this->License_model->fetch_licenses_by_user_id($id, $license_id);
            $licenses_reponse = [];
            if (!empty($licenses)) {
                foreach ($licenses as $license) {
                    //Define if user can use the license (PENDING) ***** 
                    $license['license_available'] = true;
                    $licenses_reponse[] = $license;
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $licenses_reponse), RestController::HTTP_OK);
            } else {
                $this->error = 'Licenses Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function sound_kit_file_get($id = null, $audio_id = null, $title = null) {
        if (!empty($id) && !empty($audio_id) && !empty($title)) {
            $audio = $this->Audio_model->fetch_audio_by_id_user($audio_id, $id);
            $response = [];
            if (empty($audio)) {
                $this->error = 'Audio Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            } else {
                if (!empty($audio['track_stems'])) {
                    $path = $this->s3_path . $this->s3_audio;
                    $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
                    if (!empty($data_file)) {
                        file_put_contents($this->temp_dir . '/' . $audio['track_stems'], $data_file);
                        $title = urldecode($title);
                        //Audio List.
                        $zip = new ZipArchive;
                        if ($zip->open($this->temp_dir . '/' . $audio['track_stems']) === TRUE) {
                            $beat_file = $zip->getFromName($title);
                            $zip->close();
                            if (!empty($beat_file)) {
                                file_put_contents($this->temp_dir . '/' . $title, $beat_file);
                                $src = 'data:' . mime_content_type($this->temp_dir . '/' . $title) . ';base64,' . base64_encode($beat_file);
                                $response['audio'] = $src;
                            } else {
                                $this->error = 'Title Not Found.';
                                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                            }
                        }
                        unlink($this->temp_dir . '/' . $audio['track_stems']);
                        unlink($this->temp_dir . '/' . $title);
                    } else {
                        $this->error = 'Track_Stems Not Found.';
                        $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                    }
                } else {
                    $this->error = 'Track_Stems Not Found.';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $response), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID, Sound Kit ID and Autio Title';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    //NEW
    public function beats_tab_get($url = null, $audio_id = null, $beat_type = null) {
        if (!empty($url)) {
            $register_user = $this->User_model->fetch_user_by_search(['url' => $url]);
            if (!empty($register_user)) {
                $user_images = (empty($audio_id)) ? true : false;
                $user_response = $this->user_clean_2($register_user, $user_images);
                $data_response = [];
                $data_response['profile'] = $user_response;
                //GENRES
                $data_response['genres'] = $this->Audio_model->fetch_beats_genres_by_profile($register_user['id']);
                //Licenses
                $licenses = $this->License_model->fetch_licenses_by_user_id($register_user['id'], null);
                $data_response['licenses'] = [];
                if (!empty($licenses)) {
                    foreach ($licenses as $license) {
                        //Define if user can use the license (PENDING) ***** 
                        $license['license_available'] = true;
                        $data_response['licenses'][] = $license;
                    }
                }
                $data_response['beats'] = [];
                $streamys = $this->Audio_model->fetch_beats_by_profile($register_user['id'], $audio_id, null, null, null, null, $beat_type, 'default', 50, 0, null);
                foreach ($streamys as $streamy) {
                    $audio_response = $this->audio_clean_2($streamy, $audio_id);
                    if (!empty($audio_response)) {
                        $data_response['beats'][] = $audio_response;
                    }
                }
                if (!empty($audio_id) && !empty($streamys)) {
                    $data_log = [];
                    $data_log['audio_id'] = $audio_id;
                    $data_log['audio_type'] = (!empty($beat_type)) ? $beat_type : 'beat';
                    $data_log['action'] = 'VIEW';
                    $data_log['user_id'] = $register_user['id'];
                    $this->Audio_model->insert_audio_log($data_log);
                }
                //More Items
                $data_response['extra'] = [];
                if (!empty($audio_id)) {
                    $extra_streamys = $this->Audio_model->fetch_beats_by_profile($register_user['id'], null, null, null, null, null, $beat_type, 'random', 4, 0, null);
                    foreach ($extra_streamys as $extra_streamy) {
                        $audio_response = $this->audio_clean_2($extra_streamy, null);
                        if (!empty($audio_response)) {
                            if ($audio_response['id'] != $audio_id) {
                                $played_count = $this->Audio_model->fetch_audio_played($audio_response['id']);
                                $audio_response['play'] = $played_count['Count'];
                                $data_response['extra'][] = $audio_response;
                            }
                        }
                    }
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $data_response), RestController::HTTP_OK);
            } else {
                $this->error = 'Profile Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Profile URL.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function sound_kits_tab_get($url = null, $audio_id = null) {
        if (!empty($url)) {
            $register_user = $this->User_model->fetch_user_by_search(['url' => $url]);
            if (!empty($register_user)) {
                $user_images = (empty($audio_id)) ? true : false;
                $user_response = $this->user_clean_2($register_user, $user_images);
                $data_response = [];
                $data_response['profile'] = $user_response;
                //GENRES
                $data_response['genres'] = $this->Audio_model->fetch_sound_kits_genres_by_profile($register_user['id']);
                $data_response['sound_kits'] = [];
                //$streamys = $this->Audio_model->fetch_beats_by_profile($register_user['id'], $audio_id, null, null, null, null, $beat_type, 'default', 50, 0);
                $streamys = $this->Audio_model->fetch_sound_kit_by_profile($register_user['id'], $audio_id, null, null, 'default', 50, 0);
                foreach ($streamys as $streamy) {
                    $audio_response = $this->audio_clean_2($streamy, $audio_id);
                    if (!empty($audio_response)) {
                        $data_response['sound_kits'][] = $audio_response;
                    }
                }
                if (!empty($audio_id) && !empty($streamys)) {
                    $data_log = [];
                    $data_log['audio_id'] = $audio_id;
                    $data_log['audio_type'] = 'kit';
                    $data_log['action'] = 'VIEW';
                    $data_log['user_id'] = $register_user['id'];
                    $this->Audio_model->insert_audio_log($data_log);
                }
                //More Items
                $data_response['extra'] = [];
                if (!empty($audio_id)) {
                    $extra_streamys = $this->Audio_model->fetch_sound_kit_by_profile($register_user['id'], null, null, null, 'random', 4, 0);
                    foreach ($extra_streamys as $extra_streamy) {
                        $audio_response = $this->audio_clean_2($extra_streamy, null);
                        if (!empty($audio_response)) {
                            if ($audio_response['id'] != $audio_id) {
                                $played_count = $this->Audio_model->fetch_audio_played($audio_id);
                                $audio_response['play'] = $played_count['Count'];
                                $data_response['extra'][] = $audio_response;
                            }
                        }
                    }
                }

                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $data_response), RestController::HTTP_OK);
            } else {
                $this->error = 'Profile Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Profile URL.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function videos_tab_get($url = null, $video_id = null) {
        if (!empty($url)) {
            $register_user = $this->User_model->fetch_user_by_search(['url' => $url]);
            if (!empty($register_user)) {
                $user_response = $this->user_clean_2($register_user);
                $data_response = [];
                $data_response['profile'] = $user_response;
                //GENRES
                $data_response['genres'] = $this->Video_model->fetch_videos_genres_by_profile($register_user['id']);
                $data_response['videos'] = [];
                //$videos = $this->Video_model->fetch_video_by_profile($id, $video_id, $genre, $tag, $sort, $limit, $offset);
                $videos = $this->Video_model->fetch_video_by_profile($register_user['id'], $video_id, null, null, 'default', 50, 0);
                foreach ($videos as $video) {
                    $video['scheduled'] = true;
                    if ($video['publish_at'] == '0000-00-00 00:00:00' || empty($video['publish_at'])) {
                        $video['scheduled'] = false;
                    }
                    $video['date'] = ($video['scheduled']) ? substr($video['publish_at'], 0, 10) : '';
                    $video['time'] = ($video['scheduled']) ? substr($video['publish_at'], 11) : '';
                    $video['public'] = ($video['public'] == '3') ? '1' : $video['public'];
                    $video_response = $this->video_clean($video);
                    if (!empty($video_response)) {
                        $data_response['videos'][] = $video_response;
                    }
                }
                if (!empty($video_id) && !empty($videos)) {
                    $data_log = [];
                    $data_log['audio_id'] = $video_id;
                    $data_log['audio_type'] = 'videos';
                    $data_log['action'] = 'VIEW';
                    $data_log['user_id'] = $register_user['id'];
                    $this->Audio_model->insert_audio_log($data_log);
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $data_response), RestController::HTTP_OK);
            } else {
                $this->error = 'Profile Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Profile URL.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function links_tab_get($url = null, $link_id = null) {
        if (!empty($url)) {
            $register_user = $this->User_model->fetch_user_by_search(['url' => $url]);
            if (!empty($register_user)) {
                $user_response = $this->user_clean_2($register_user);
                $data_response = [];
                $data_response['profile'] = $user_response;
                //GENRES
                //$data_response['genres'] = $this->Video_model->fetch_videos_genres_by_profile($register_user['id']);
                $data_response['links'] = [];
                //$videos = $this->Video_model->fetch_video_by_profile($id, $video_id, $genre, $tag, $sort, $limit, $offset);
                $links = $this->Link_model->fetch_links_by_profile($register_user['id'], $link_id, null, 'default', 50, 0);
                foreach ($links as $link) {
                    $link_reponse = $this->link_clean($link);
                    if (!empty($link_reponse)) {
                        $data_response['links'][] = $link_reponse;
                    }
                }
//                if (!empty($video_id) && !empty($videos)) {
//                    $data_log = [];
//                    $data_log['audio_id'] = $video_id;
//                    $data_log['audio_type'] = 'videos';
//                    $data_log['action'] = 'VIEW';
//                    $data_log['user_id'] = $register_user['id'];
//                    $this->Audio_model->insert_audio_log($data_log);
//                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $data_response), RestController::HTTP_OK);
            } else {
                $this->error = 'Profile Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Profile URL.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function user_clean_2($user, $images = true) {
        unset($user['password']);
        unset($user['email_confirmed']);
        unset($user['status_id']);
        //unset($user['facebook']);
        //unset($user['instagram']);
        //unset($user['twitter']);
        //unset($user['soundcloud']);
        //unset($user['youtube']);
        unset($user['platform']);
        unset($user['platform_id']);
        unset($user['platform_token']);
        unset($user['payment_processor']);
        unset($user['payment_processor_key']);
        //NEW
        unset($user['plan_id']);
        unset($user['email_paypal']);
        unset($user['facebook']);
        unset($user['instagram']);
        unset($user['twitter']);
        unset($user['soundcloud']);
        unset($user['youtube']);

        unset($user['email']);
        unset($user['created_at']);
        unset($user['phone']);
        unset($user['about']);
        unset($user['timezone']);
        //PENDING
        //$user['followers'] = '50000';
        $user['followers'] = $this->Audio_model->fetch_followers_count($user['id']);
        //$user['plays'] = '1000000';
        $user['plays'] = $this->Audio_model->fetch_audio_log_count($user['id'], 'PLAY');
        //$user['beats'] = '300';
        $user['beats'] = $this->Audio_model->fetch_beat_count($user['id']);
        //
        //Avatar & Banner
        $path = $this->s3_path . $this->s3_folder;
        $user['avatar_url'] = '';
        $user['data_image'] = '';
        $user['data_banner'] = '';
        if ($images) {
            if (!empty($user['image'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $user['image']);
                if (!empty($data_image)) {
                    //$img_file = $user['image'];
                    file_put_contents($this->temp_dir . '/' . $user['image'], $data_image);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $user['image']) . ';base64,' . base64_encode($data_image);
                    $user['data_image'] = $src;
                    unlink($this->temp_dir . '/' . $user['image']);
                    $user['avatar_url'] = $this->server_url . $this->s3_path . $this->s3_folder . '/' . $user['image'];
                }
            } else {
                $user['image'] = 'LS_avatar.png';
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $user['image']);
                if (!empty($data_image)) {
                    //$img_file = $user['image'];
                    file_put_contents($this->temp_dir . '/' . $user['image'], $data_image);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $user['image']) . ';base64,' . base64_encode($data_image);
                    $user['data_image'] = $src;
                    unlink($this->temp_dir . '/' . $user['image']);
                    $user['avatar_url'] = $this->server_url . $this->s3_path . $this->s3_folder . '/' . $user['image'];
                }
            }
            if (!empty($user['banner'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $user['banner']);
                if (!empty($data_image)) {
                    //$img_file = $user['banner'];
                    file_put_contents($this->temp_dir . '/' . $user['banner'], $data_image);
                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $user['banner']) . ';base64,' . base64_encode($data_image);
                    $user['data_banner'] = $src;
                    unlink($this->temp_dir . '/' . $user['banner']);
                }
            }
        }
        return $user;
    }

    private function audio_clean_2($audio, $audio_id = null, $images = true) {
        if ($audio['track_type'] == '2') {
            if ($audio['type'] == 'beat') {
                $audio = $this->beat_clean_2($audio, $audio_id, $images);
            } else {
                $audio = $this->beat_pack_clean_2($audio, $audio_id, $images);
            }
        } elseif ($audio['track_type'] == '3') {
            $audio = $this->sound_kit_clean_2($audio, $audio_id, $images);
        }
        return $audio;
    }

    private function beat_clean_2($audio, $audio_id = null, $images = true) {

        $audio['scheduled'] = true;
        if ($audio['publish_at'] == '0000-00-00 00:00:00' || empty($audio['publish_at'])) {
            $audio['scheduled'] = false;
        }
        $audio['date'] = ($audio['scheduled']) ? substr($audio['publish_at'], 0, 10) : '';
        $audio['time'] = ($audio['scheduled']) ? substr($audio['publish_at'], 11) : '';

        $audio['genre_id'] = !empty($audio['genre_id']) ? $audio['genre_id'] : '';
        $audio['key_id'] = !empty($audio['key_id']) ? $audio['key_id'] : '';

        $audio['url_user'] = '';
        $audio['url_title'] = '';
        $audio['beat_packs'] = '';
        $audio['coverart_url'] = '';
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
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
//                //$link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
//                if (!empty($data_image)) {
//                    $img_file = $audio['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
//                    $audio['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['coverart']);
//                    $audio['coverart_url'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
//                }
                $audio['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
                //NEW ENCRYPTED IMAGE
                $final_url = $this->general_library->encode_image_url($audio['user_id'], $this->s3_path . $this->s3_coverart . '/' . $audio['coverart']);
                $audio['data_image'] = $audio['coverart_url'] = $final_url;
                //END ENCRYPTED IMAGE
            }
        }
        $audio['licenses'] = $this->Audio_model->fetch_audio_license_by_id($audio['id']);
        if (!empty($audio_id)) {
            $user = $this->User_model->fetch_user_by_id($audio['user_id']);
            $audio['url_user'] = $user['url'];
            $audio['url_title'] = url_title($audio['title']);
            $audio['beat_packs'] = $this->Album_model->fetch_album_audio_by_id($audio_id);
            $audio['collaborators'] = [];
            $path = $this->s3_path . $this->s3_folder;
            $collaborators = $this->Audio_model->fetch_audio_collaborator_by_id($audio_id);
            foreach ($collaborators as $collaborator) {
                $collaborator['data_image'] = '';
                if (!empty($collaborator['image'])) {
//                    $data_image = $this->aws_s3->s3_read($this->bucket, $path, $collaborator['image']);
//                    if (!empty($data_image)) {
//                        $img_file = $collaborator['image'];
//                        file_put_contents($this->temp_dir . '/' . $collaborator['image'], $data_image);
//                        $src = 'data:' . mime_content_type($this->temp_dir . '/' . $collaborator['image']) . ';base64,' . base64_encode($data_image);
//                        $collaborator['data_image'] = $src;
//                        unlink($this->temp_dir . '/' . $collaborator['image']);
//                    }
                    $collaborator['data_image'] = $this->server_url . $this->s3_path . $this->s3_folder . '/' . $collaborator['image'];
                    //NEW ENCRYPTED IMAGE
                    $final_url = $this->general_library->encode_image_url($audio['user_id'], $this->s3_path . $this->s3_folder . '/' . $collaborator['image']);
                    $collaborator['data_image'] = $final_url;
                    //END ENCRYPTED IMAGE
                }
                $audio['collaborators'][] = $collaborator;
            }
            $audio['marketing'] = $this->Audio_model->fetch_audio_marketing_by_id($audio_id);
            $path = $this->s3_path . $this->s3_audio;
            if (!empty($audio['tagged_file'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['tagged_file']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['tagged_file'];
//                    file_put_contents($this->temp_dir . '/' . $audio['tagged_file'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['tagged_file']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_tagged_file'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['tagged_file']);
//                }
                $audio['data_tagged_file'] = $this->server_url . $this->s3_path . $this->s3_audio . '/' . $audio['tagged_file'];
                //NEW ENCRYPTED AUDIO
                $final_url = $this->general_library->encode_audio_url($audio['user_id'], $this->s3_path . $this->s3_audio . '/' . $audio['tagged_file']);
                $audio['data_tagged_file'] = $final_url;
                //END ENCRYPTED AUDIO
            } elseif (!empty($audio['untagged_mp3']) && empty($audio['data_tagged_file'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['untagged_mp3'];
//                    file_put_contents($this->temp_dir . '/' . $audio['untagged_mp3'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['untagged_mp3']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_untagged_mp3'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['untagged_mp3']);
//                }
                $audio['data_untagged_mp3'] = $this->server_url . $this->s3_path . $this->s3_audio . '/' . $audio['untagged_mp3'];
                //NEW ENCRYPTED AUDIO
                $final_url = $this->general_library->encode_audio_url($audio['user_id'], $this->s3_path . $this->s3_audio . '/' . $audio['untagged_mp3']);
                $audio['data_untagged_mp3'] = $final_url;
                //END ENCRYPTED AUDIO
            } elseif (!empty($audio['untagged_wav']) && empty($audio['data_untagged_mp3'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['untagged_wav'];
//                    file_put_contents($this->temp_dir . '/' . $audio['untagged_wav'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['untagged_wav']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_untagged_wav'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['untagged_wav']);
//                }
                $audio['data_untagged_wav'] = $this->server_url . $this->s3_path . $this->s3_audio . '/' . $audio['untagged_wav'];
                //NEW ENCRYPTED AUDIO
                $final_url = $this->general_library->encode_audio_url($audio['user_id'], $this->s3_path . $this->s3_audio . '/' . $audio['untagged_wav']);
                //$audio['data_untagged_wav'] = $final_url;
                //END ENCRYPTED AUDIO
            }
        }
        unset($audio['publish_at']);
        unset($audio['price']);
        unset($audio['samples']);
        unset($audio['description']);
        //PROFILE
        unset($audio['status_id']);
        unset($audio['sort']);
        if ($audio['public'] != '1') {
            $audio = false;
        }
        unset($audio['public']);
        if ($audio['scheduled']) {
            $current_time = date("Y-m-d H:i:s");
            if ($current_time < $audio['date'] . ' ' . $audio['time']) {
                $audio = false;
            }
        }
        unset($audio['scheduled']);
        unset($audio['date']);
        unset($audio['time']);
        //new
        if (empty($audio_id)) {
            unset($audio['created_at']);
            unset($audio['status_id']);
            //unset($audio['bpm']);
            unset($audio['key_id']);
            unset($audio['public']);
            unset($audio['publish_at']);
            unset($audio['untagged_mp3']);
            unset($audio['untagged_wav_name']);
            unset($audio['untagged_wav']);
            unset($audio['track_stems_name']);
            unset($audio['track_stems']);
            unset($audio['tagged_file_name']);
            unset($audio['tagged_file']);
            unset($audio['license_id']);
            unset($audio['url_user']);
            unset($audio['url_title']);
            unset($audio['beat_packs']);
            unset($audio['collaborators']);
            unset($audio['marketing']);
            unset($audio['data_untagged_mp3']);
            unset($audio['data_untagged_wav']);
            unset($audio['data_track_stems']);
            unset($audio['data_tagged_file']);
        }
        return $audio;
    }

    private function beat_pack_clean_2($audio, $audio_id = null, $images = true) {

        //unset($audio['type']);
        $audio['scheduled'] = true;
        if ($audio['publish_at'] == '0000-00-00 00:00:00' || empty($audio['publish_at'])) {
            $audio['scheduled'] = false;
        }
        $audio['date'] = ($audio['scheduled']) ? substr($audio['publish_at'], 0, 10) : '';
        $audio['time'] = ($audio['scheduled']) ? substr($audio['publish_at'], 11) : '';
        $audio['genre_id'] = !empty($audio['genre_id']) ? $audio['genre_id'] : '';
        $audio['license_id'] = !empty($audio['license_id']) ? $audio['license_id'] : '';
        $audio['coverart_url'] = '';
        $audio['data_image'] = '';
        $audio['beats'] = '';

        //Coverart
        $path = $this->s3_path . $this->s3_coverart;
        if ($images) {
            if (!empty($audio['coverart'])) {
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
//                if (!empty($data_image)) {
//                    $img_file = $audio['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
//                    $audio['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['coverart']);
//                    $audio['coverart_url'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
//                }
                $audio['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
                //NEW ENCRYPTED IMAGE
                $final_url = $this->general_library->encode_image_url($audio['user_id'], $this->s3_path . $this->s3_coverart . '/' . $audio['coverart']);
                $audio['data_image'] = $audio['coverart_url'] = $final_url;
                //END ENCRYPTED IMAGE
            }
        }
        $audio['beats'] = $this->Album_model->fetch_album_audio_by_album_id_with_name($audio['id']);
        //PROFILE
        unset($audio['status_id']);
        unset($audio['sort']);
        if ($audio['public'] != '1') {
            $audio = false;
        }
        unset($audio['public']);
        if ($audio['scheduled']) {
            $current_time = date("Y-m-d H:i:s");
            if ($current_time < $audio['date'] . ' ' . $audio['time']) {
                $audio = false;
            }
        }
        unset($audio['scheduled']);
        unset($audio['date']);
        unset($audio['time']);
        //new
        if (empty($audio_id)) {
            unset($audio['created_at']);
            unset($audio['status_id']);
            unset($audio['bpm']);
            unset($audio['key_id']);
            unset($audio['public']);
            unset($audio['publish_at']);
            unset($audio['untagged_mp3']);
            unset($audio['untagged_wav_name']);
            unset($audio['untagged_wav']);
            unset($audio['track_stems_name']);
            unset($audio['track_stems']);
            unset($audio['tagged_file_name']);
            unset($audio['tagged_file']);
            unset($audio['license_id']);
            unset($audio['url_user']);
            unset($audio['url_title']);
            unset($audio['beat_packs']);
            unset($audio['collaborators']);
            unset($audio['marketing']);
            unset($audio['data_untagged_mp3']);
            unset($audio['data_untagged_wav']);
            unset($audio['data_track_stems']);
            unset($audio['data_tagged_file']);
        }
        return $audio;
    }

    private function sound_kit_clean_2($audio, $audio_id = null, $images = true) {

        $audio['scheduled'] = true;
        if ($audio['publish_at'] == '0000-00-00 00:00:00' || empty($audio['publish_at'])) {
            $audio['scheduled'] = false;
        }
        $audio['date'] = ($audio['scheduled']) ? substr($audio['publish_at'], 0, 10) : '';
        $audio['time'] = ($audio['scheduled']) ? substr($audio['publish_at'], 11) : '';
        $audio['genre_id'] = !empty($audio['genre_id']) ? $audio['genre_id'] : '';
        $audio['coverart_url'] = '';
        $audio['url_user'] = '';
        $audio['url_title'] = '';
        //$audio['kit_files_name'] = [];
        $audio['data_image'] = '';
        $audio['data_track_stems'] = '';
        $audio['data_tagged_file'] = '';

        //Coverart
        $path = $this->s3_path . $this->s3_coverart;
        if ($images) {
            if (!empty($audio['coverart'])) {
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
//                if (!empty($data_image)) {
//                    $img_file = $audio['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
//                    $audio['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['coverart']);
//                    $audio['coverart_url'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
//                }
                $audio['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
                //NEW ENCRYPTED IMAGE
                $final_url = $this->general_library->encode_image_url($audio['user_id'], $this->s3_path . $this->s3_coverart . '/' . $audio['coverart']);
                $audio['data_image'] = $audio['coverart_url'] = $final_url;
                //END ENCRYPTED IMAGE
            }
        }
        if (!empty($audio_id)) {
            $user = $this->User_model->fetch_user_by_id($audio['user_id']);
            $audio['url_user'] = $user['url'];
            $audio['url_title'] = url_title($audio['title']);
            $path = $this->s3_path . $this->s3_audio;
            $audio['kit_files_name'] = (!empty($audio['kit_files_name'])) ? json_decode($audio['kit_files_name']) : [];
            if (!empty($audio['tagged_file'])) {
//                $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['tagged_file']);
//                if (!empty($data_file)) {
//                    $img_file = $audio['tagged_file'];
//                    file_put_contents($this->temp_dir . '/' . $audio['tagged_file'], $data_file);
//                    $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['tagged_file']) . ';base64,' . base64_encode($data_file);
//                    $audio['data_tagged_file'] = $src;
//                    unlink($this->temp_dir . '/' . $audio['tagged_file']);
//                }
                $audio['data_tagged_file'] = $this->server_url . $this->s3_path . $this->s3_audio . '/' . $audio['tagged_file'];
                //NEW ENCRYPTED AUDIO
                $final_url = $this->general_library->encode_audio_url($audio['user_id'], $this->s3_path . $this->s3_audio . '/' . $audio['tagged_file']);
                //$audio['data_tagged_file'] = $final_url;
                //END ENCRYPTED AUDIO
            }
        }
        unset($audio['publish_at']);
        unset($audio['bpm']);
        unset($audio['key_id']);
        unset($audio['untagged_mp3_name']);
        unset($audio['untagged_mp3']);
        unset($audio['untagged_wav_name']);
        unset($audio['untagged_wav']);
        unset($audio['key_id']);
        //PROFILE
        unset($audio['status_id']);
        unset($audio['sort']);
        if ($audio['public'] != '1') {
            $audio = false;
        }
        unset($audio['public']);
        if ($audio['scheduled']) {
            $current_time = date("Y-m-d H:i:s");
            if ($current_time < $audio['date'] . ' ' . $audio['time']) {
                $audio = false;
            }
        }
        unset($audio['scheduled']);
        unset($audio['date']);
        unset($audio['time']);
        return $audio;
    }

    public function audio_action_post() {
        $audio_id = (!empty($this->input->post('audio_id'))) ? $this->input->post('audio_id') : '';
        $audio_type = (!empty($this->input->post('audio_type'))) ? $this->input->post('audio_type') : '';
        $action = (!empty($this->input->post('action'))) ? $this->input->post('action') : '';
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        if (!empty($audio_id) && !empty($audio_type) && !empty($action)) {
            $data_log = [];
            $data_log['audio_id'] = $audio_id;
            $data_log['audio_type'] = $audio_type;
            $data_log['action'] = strtoupper($action);
            $data_log['user_id'] = $user_id;
            if ($audio_type == 'beat' || $audio_type == 'pack' || $audio_type == 'kit') {
                $this->Audio_model->insert_audio_log($data_log);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The audio action has been created successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Provide Valid Audio Type';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } {
            $this->error = 'Provide Audio ID and/or Audio Type and/or Action';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function action_post() {
        $audio_id = (!empty($this->input->post('id'))) ? $this->input->post('id') : '';
        $audio_type = (!empty($this->input->post('type'))) ? $this->input->post('type') : '';
        $action = (!empty($this->input->post('action'))) ? $this->input->post('action') : '';
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        if (!empty($audio_id) && !empty($audio_type) && !empty($action)) {
            $data_log = [];
            $data_log['audio_id'] = $audio_id;
            $data_log['audio_type'] = $audio_type;
            $data_log['action'] = strtoupper($action);
            $data_log['user_id'] = $user_id;
            if ($audio_type == 'beat' || $audio_type == 'pack' || $audio_type == 'kit' || $audio_type == 'link' || $audio_type == 'video') {
                $this->Audio_model->insert_audio_log($data_log);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The action has been created successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Provide Valid Type';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } {
            $this->error = 'Provide ID and/or Type and/or Action';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function stripe_payment_post() {

        $array = [
            'user_id' => '35',
            'payment' => [
                'exp_month' => '10',
                'exp_year' => '2021',
                'number' => '4242424242424242',
                'cvc' => '314',
                'name' => 'John Doe',
                'address_zip' => '33312',
                'subtotal' => '180',
                'feeCC' => '10',
                'feeService' => '10',
                'total' => '200'
            ],
            'cart' => [
                ['item_id' => '10', 'item_title' => 'Title 10', 'item_amount' => '45', 'item_track_type' => 'beat', 'producer_id' => '30', 'license_id' => '5'],
                ['item_id' => '25', 'item_title' => 'Title 25', 'item_amount' => '90', 'item_track_type' => 'kit', 'producer_id' => '30', 'license_id' => ''],
                ['item_id' => '67', 'item_title' => 'Title 67', 'item_amount' => '45', 'item_track_type' => 'pack', 'producer_id' => '24', 'license_id' => '']
            ]
        ];

        //VARS: USER ID QUE PAGA, VALORES A COBRAR, LISTA DE ITEMS, SUBTOTAL, FEES, TOTAL.
        //CREATE TOKEN
        $this->load->library('Stripe_library');
        $exp_month = 10;
        $exp_year = 2021;
        $number = '4242424242424242';
        $cvc = '314';
        $name = 'Paolo Ferra';
        $address_zip = '33312';
        $user_id = 35;
        $card_token = $this->stripe_library->create_a_card_token($exp_month, $exp_year, $number, $cvc, $name, $address_zip);
        if (!$card_token['status']) {
            $this->error = 'Payment Error: ' . $card_token['error'];
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        } else {
            $token_id = $card_token['token_id'];
            echo $token_id . '<br>';
            $subtotal = 180;
            $feeCC = 10;
            $feeService = 10;
            $total = 200;
            $invoice = [
                'user_id' => $user_id,
                'status' => 'PENDING',
                'sub_total' => $subtotal,
                'feeCC' => $feeCC,
                'feeService' => $feeService,
                'total' => $total,
                'payment_customer_id' => $token_id,
            ];
            $invoice_id = $this->User_model->insert_user_purchase($invoice);
            $invoice_number = 'LS' . str_pad($invoice_id, 7, "0", STR_PAD_LEFT);

//            $transfer_group = 'ORDER_95';
//            $description = 'Linkstream Charge ORDER_95';
            $transfer_group = $invoice_number;
            $description = 'Linkstream - Invoice: ' . $invoice_number;
            $receipt_email = 'paolofq@gmail.com';

            $charge = $this->stripe_library->create_a_charge($total, $description, $receipt_email, $token_id, $transfer_group);
            if (!$charge['status']) {
                $this->error = 'Payment Error: ' . $charge['error'];
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            } else {
                $charge_id = $charge['charge_id'];
                $receipt_url = $charge['receipt_url'];
                echo $charge_id . '<br>';
                echo $receipt_url . '<br>';
                $invoice['invoice_number'] = $invoice_number;
                $invoice['status'] = 'COMPLETED';
                $invoice['payment_charge_id'] = $charge_id;
                $invoice['billingZip'] = $address_zip;
                $invoice['billingCVV'] = $cvc;
                $invoice['billingCC6'] = substr($number, 6);
                $invoice['billingCC'] = substr($number, -4);
                $invoice['billingName'] = $name;
                $this->User_model->update_user_purchase($invoice_id, $invoice);
            }
        }
    }

    public function recommendations_get($user_id) {
        $genre_recommendation = $this->Audio_model->fetch_genre_recommendations($user_id);
        $data_response = [];
        $extra_streamys = $this->Audio_model->fetch_beats_by_profile(null, null, $genre_recommendation['genre_id'], null, null, null, 'beat', 'random', 4, 0, $user_id);
        foreach ($extra_streamys as $extra_streamy) {
            $audio_response = $this->audio_clean_2($extra_streamy, null);
            if (!empty($audio_response)) {
                //if ($audio_response['id'] != $audio_id) {
                $data_response['extra'][] = $audio_response;
                //}
            }
        }
        $this->response(array('status' => 'success', 'env' => ENV, 'data' => $data_response), RestController::HTTP_OK);
    }

    public function visitor_post() {
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        if (empty($user_id)) {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
        $register_user = $this->User_model->fetch_user_by_id($user_id);
        if (empty($register_user)) {
            $this->error = 'User Not Found.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
        $session_id = (!empty($this->input->post('session_id'))) ? $this->input->post('session_id') : '';
        //$session_id = session_id();
        $ip = $_SERVER['REMOTE_ADDR'];
//        $ip = $_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP'] : ($_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        $ip = ($ip == '::1') ? '170.55.19.206' : $ip;
        $agent_string = (!empty($this->input->post('agent'))) ? $this->input->post('agent') : '';
//        $platform = (!empty($this->input->post('platform'))) ? $this->input->post('platform') : '';
        //$agent_string = (!empty($this->input->post('agent_string'))) ? $this->input->post('agent_string') : '';
        $url = (!empty($this->input->post('url'))) ? $this->input->post('url') : '';
        $utm_source = (!empty($this->input->post('utm_source'))) ? $this->input->post('utm_source') : '';
        $ref_id = (!empty($this->input->post('ref_id'))) ? $this->input->post('ref_id') : '';
        if (empty($session_id) || empty($agent_string)) {
            $this->error = 'Provide Visitor Information.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
        $visitor = $this->Visitor_model->fetch_visitor_by_search(array('user_id' => $register_user['id'], 'session_id' => $session_id));
        if (empty($visitor)) {
            //GET LOCATION
            $data_location = $this->general_library->ip_location($ip);
            //END LOCATION 
            $data = array(
                'user_id' => $register_user['id'],
                'session_id' => $session_id,
                'ip' => $ip,
                'agent' => '',
                'platform' => '',
                'country' => $data_location['country'],
                'countryCode' => $data_location['countryCode'],
                'region' => $data_location['region'],
                'regionName' => $data_location['regionName'],
                'city' => $data_location['city'],
                'zip' => $data_location['zip'],
                'lat' => $data_location['lat'],
                'lon' => $data_location['lon'],
                'timezone' => $data_location['timezone'],
                'url' => $url,
                'utm_source' => $utm_source,
                'ref_id' => $ref_id,
                'agent_string' => $agent_string
            );
//            echo '<pre>';
//            print_r($data);
//            echo '</pre>';
//            exit;
            //VISITOR
            $this->Visitor_model->insert_visitor($data);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The visitor has been created successfully.'), RestController::HTTP_OK);
        } else {
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The visitor has been created successfully.'), RestController::HTTP_OK);
        }
    }

    public function visitor_get() {
        //$session_id = session_id();
        $ip = $_SERVER['REMOTE_ADDR'];
//        $ip = $_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP'] : ($_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        $ip = ($ip == '::1') ? '170.55.19.206' : $ip;
        $this->response(array('status' => 'success', 'env' => ENV, 'visitor_ip' => $ip), RestController::HTTP_OK);
    }

    private function analytics($register_user) {
        $session_id = session_id();
        print_r($_SERVER['REMOTE_ADDR']);
        exit;
//        $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
//        print_r($ip);
        $visitor = $this->Visitor_model->fetch_visitor_by_search(array('user_id' => $register_user['id'], 'session_id' => $session_id));
        if (empty($visitor)) {
            //IP
            $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $ip = ($ip == '::1') ? '170.55.19.206' : $ip;
            //
            //USER AGENT
            if ($this->agent->is_browser()) {
                $agent = $this->agent->browser() . ' ' . $this->agent->version();
            } elseif ($this->agent->is_robot()) {
                $agent = $this->agent->robot();
            } elseif ($this->agent->is_mobile()) {
                $agent = $this->agent->mobile();
            } else {
                $agent = 'Unidentified User Agent';
            }
            $platform = $this->agent->platform(); // Platform info (Windows, Linux, Mac, etc.)
            $agent_string = $this->agent->agent_string();
            //
            //LOCATION
            //$location = file_get_contents('http://ip-api.com/json/' . $ip);
            //$data_loc = json_decode($location, true);
            //
            $data = array(
                'user_id' => $register_user['id'],
                'session_id' => $session_id,
                'ip' => $ip,
                'agent' => $agent,
                'platform' => $platform,
                'country' => (!empty($data_loc) && $data_loc['status'] == 'success') ? $data_loc['country'] : 'United States',
                'countryCode' => (!empty($data_loc) && $data_loc['status'] == 'success') ? $data_loc['countryCode'] : 'US',
                'region' => (!empty($data_loc) && $data_loc['status'] == 'success') ? $data_loc['region'] : 'FL',
                'regionName' => (!empty($data_loc) && $data_loc['status'] == 'success') ? $data_loc['regionName'] : 'Florida',
                'city' => (!empty($data_loc) && $data_loc['status'] == 'success') ? $data_loc['city'] : 'Miami',
                'zip' => (!empty($data_loc) && $data_loc['status'] == 'success') ? $data_loc['zip'] : '33132',
                'lat' => (!empty($data_loc) && $data_loc['status'] == 'success') ? $data_loc['lat'] : '25.7806',
                'lon' => (!empty($data_loc) && $data_loc['status'] == 'success') ? $data_loc['lon'] : '-80.1826',
                'timezone' => (!empty($data_loc) && $data_loc['status'] == 'success') ? $data_loc['timezone'] : 'America/New_York',
                'agent_string' => $agent_string
            );
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            //VISITOR
            //$this->Visitor_model->insert_visitor($data);
        }
    }

}
