<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Licenses extends RestController {

    private $error;

//    private $bucket;
//    private $s3_path;
//    private $s3_coverart;
//    private $s3_audio;
//    private $temp_dir;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model(array('User_model', 'License_model', 'Log_model'));
        //Libraries
        $this->load->library(array('aws_s3', 'Aws_pinpoint'));
        //Helpers
        //$this->load->helper('email');
        //VARS
        $this->error = '';
//        $this->bucket = 'files.link.stream';
//        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
//        $this->s3_coverart = 'Coverart';
//        $this->s3_audio = 'Audio';
//        $this->temp_dir = $this->general_library->get_temp_dir();
    }

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

    public function index_put($id = null) {
        if (!empty($id)) {
            $license = $this->License_model->fetch_license_by_id($id);
            if (!empty($license)) {
                if (!$this->general_library->header_token($license['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'License', 'log' => 'Updated']);
                if ($this->put('status_id') !== null) {
                    $license['status_id'] = $this->put('status_id');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'status_id', 'log' => $license['status_id']]);
                }
                if ($this->put('price') !== null) {
                    $license['price'] = $this->put('price');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'price', 'log' => $license['price']]);
                }
                if ($this->put('mp3') !== null) {
                    $license['mp3'] = $this->put('mp3');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'mp3', 'log' => $license['mp3']]);
                }
                if ($this->put('wav') !== null) {
                    $license['wav'] = $this->put('wav');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'wav', 'log' => $license['wav']]);
                }
                if ($this->put('trackout_stems') !== null) {
                    $license['trackout_stems'] = $this->put('trackout_stems');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'trackout_stems', 'log' => $license['trackout_stems']]);
                }
                if ($this->put('distribution_copies') !== null) {
                    $license['distribution_copies'] = $this->put('distribution_copies');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'distribution_copies', 'log' => $license['distribution_copies']]);
                }
                if ($this->put('free_download') !== null) {
                    $license['free_download'] = $this->put('free_download');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'free_download', 'log' => $license['free_download']]);
                }
                if ($this->put('audio_streams') !== null) {
                    $license['audio_streams'] = $this->put('audio_streams');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'audio_streams', 'log' => $license['audio_streams']]);
                }
                if ($this->put('music_videos') !== null) {
                    $license['music_videos'] = $this->put('music_videos');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'music_videos', 'log' => $license['music_videos']]);
                }
                if ($this->put('video_streams') !== null) {
                    $license['video_streams'] = $this->put('video_streams');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'video_streams', 'log' => $license['video_streams']]);
                }
                if ($this->put('broadcasting_rights') !== null) {
                    $license['broadcasting_rights'] = $this->put('broadcasting_rights');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'broadcasting_rights', 'log' => $license['broadcasting_rights']]);
                }
                if ($this->put('radio_station') !== null) {
                    $license['radio_station'] = $this->put('radio_station');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'radio_station', 'log' => $license['radio_station']]);
                }
                if ($this->put('paid_performances') !== null) {
                    $license['paid_performances'] = $this->put('paid_performances');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'wav', 'log' => $license['wav']]);
                }
                if ($this->put('non_profit_performances') !== null) {
                    $license['non_profit_performances'] = $this->put('non_profit_performances');
                    $this->Log_model->insert_user_action_log(['user_id' => $license['user_id'], 'content_id' => $id, 'content_table' => 'st_license', 'event' => 'non_profit_performances', 'log' => $license['non_profit_performances']]);
                }
                $this->License_model->update_license($id, $license);
                //Define if user can use the license (PENDING) ***** 
                $license['license_available'] = true;
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The License info has been updated successfully.', 'data' => $license), RestController::HTTP_OK);
            } else {
                $this->error = 'License Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide License ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

}
