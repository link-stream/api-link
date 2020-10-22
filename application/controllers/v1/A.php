<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class A extends CI_Controller {

    private $error;
    private $bucket;
    private $s3_path;
    private $s3_coverart;
    private $s3_audio;
    private $s3_folder;
    private $temp_dir;
    private $server_url;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model("Marketing_model");
        $this->load->model("License_model");
        $this->load->model("Audio_model");
        $this->load->model("Album_model");
        //Libraries
        $this->load->library(array('aws_s3'));
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_coverart = 'Coverart';
        $this->s3_audio = 'Audio';
        $this->s3_folder = 'Profile';
        $this->temp_dir = $this->general_library->get_temp_dir();
        $this->server_url = 'https://s3.us-east-2.amazonaws.com/files.link.stream/';
    }

    public function _remap($method, $params = array()) {
        $method = str_replace('-', '_', $method);
        if (method_exists(__CLASS__, $method)) {
            $count = count($params);
            if ($count == 0)
                $this->$method();
            elseif ($count == 1)
                $this->$method($params[0]);
            elseif ($count == 2)
                $this->$method($params[0], $params[1]);
            elseif ($count == 3)
                $this->$method($params[0], $params[1], $params[2]);
            elseif ($count == 4)
                $this->$method($params[0], $params[1], $params[2], $params[3]);
        } else {
            redirect('/' . $this->loc_url, 'location', 302);
        }
    }

    public function action_open() {
        if (!empty($this->input->get('ref_id'))) {
            $ref_id = $this->input->get('ref_id');
            //LOCATION
            $ip = $_SERVER['REMOTE_ADDR'];
            $ip = ($ip == '::1') ? '170.55.19.206' : $ip;
            $data_location = $this->general_library->ip_location($ip);
            $this->Marketing_model->update_open_action($ref_id, $ip, $data_location['country'], $data_location['countryCode']);
        }
        $imagen_url = (ENV != 'live') ? 'https://dev-link-vue.link.stream/static/img/open.jpg' : 'https://linkstream/static/img/open.jpg';
        header("Content-Type: image/jpeg"); // it will return image 
        $logo = file_get_contents($imagen_url);
        echo $logo;
    }

    public function action_click($ref_id) {
        if (!empty($ref_id)) {
//            $ref_id = $this->input->get('ref_id');
            //LOCATION
//            $ip = $_SERVER['REMOTE_ADDR'];
//            $ip = ($ip == '::1') ? '170.55.19.206' : $ip;
//            $data_location = $this->general_library->ip_location($ip);
            $this->Marketing_model->update_click_action($ref_id);
        }
        return true;
    }

    public function download_old($user_id = null, $item_id = null, $code = null, $hash = null) {

        if (empty($user_id) || empty($item_id) || empty($code) || empty($hash)) {
            echo 'Error';
        } elseif ($hash != sha1($user_id . $item_id . $code)) {
            echo 'ErrorB';
        } else {
            $this->load->library('zip');
            $item_license = $this->License_model->fetch_item_license($user_id, $item_id, $code, $hash);
            if (empty($item_license)) {
                echo 'Error';
            } else {
//                echo '<pre>';
//                print_r($item_license);
//                echo '</pre>';exit;
                if ($item_license['item_track_type'] == 'beat') {
                    //$item_id = 398;
                    $audio = $this->Audio_model->fetch_audio_by_id($item_id);
                    if (empty($audio)) {
                        echo 'Error';
                    } else {
                        $path = $this->s3_path . $this->s3_audio;
                        if ($item_license['mp3']) {
//                                        echo 'MP3';
                            if (!empty($audio['untagged_mp3'])) {
                                $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
                                $this->zip->add_data($audio['untagged_mp3_name'], $data_track_stems);
                            }
                        }
                        if ($item_license['wav']) {
//                                        echo 'WAV';
                            if (!empty($audio['untagged_wav'])) {
                                $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
                                $this->zip->add_data($audio['untagged_wav_name'], $data_track_stems);
                            }
                        }
                        if ($item_license['trackout_stems']) {
//                                        echo 'ZIP';
                            if (!empty($audio['track_stems'])) {
                                $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
                                $this->zip->add_data($audio['track_stems_name'], $data_track_stems);
                            }
                        }
                        $file_name = $audio['title'];
                        $file_name = urlencode($file_name);
                        // Write the zip file to a folder on your server. Name it "my_backup.zip"
                        //$this->zip->archive($this->temp_dir . '/my_backup.zip');
                        // Download the file to your desktop. Name it "my_backup.zip"
                        $this->zip->download($file_name . '.zip');
                    }
                } elseif ($item_license['item_track_type'] == 'kit') {
                    //
                    //kit
                    //
                    $audio = $this->Audio_model->fetch_audio_by_id($item_id);
                    if (empty($audio)) {
                        echo 'Error';
                    } else {
                        $path = $this->s3_path . $this->s3_audio;
                        if (!empty($audio['track_stems'])) {
                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
                            $this->zip->add_data($audio['track_stems_name'], $data_track_stems);
                        }
                        if (!empty($audio['tagged_file'])) {
                            $data_tagged_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['tagged_file']);
                            $this->zip->add_data($audio['tagged_file_name'], $data_tagged_file);
                        }
                    }
                    $file_name = $audio['title'];
                    $file_name = urlencode($file_name);
                    // Write the zip file to a folder on your server. Name it "my_backup.zip"
                    //$this->zip->archive($this->temp_dir . '/my_backup.zip');
                    // Download the file to your desktop. Name it "my_backup.zip"
                    $this->zip->download($file_name . '.zip');

                    //
                    //end kit
                    //
                } elseif ($item_license['item_track_type'] == 'pack') {
                    //
                    //pack
                    //
//                    $item_id = 34;
                    $album = $this->Album_model->fetch_album_by_id($item_id);
                    if (empty($album)) {
                        echo 'Error';
                    } else {

                        $license_info = $this->License_model->fetch_license_by_id($album['license_id']);

                        $album_items = $this->Album_model->fetch_album_audio_by_album_id($item_id);
                        if (empty($album_items)) {
                            echo 'Error';
                        } else {
//                            echo '<pre>';
//                            print_r($album);
//                            echo '</pre>';
//                            echo '<pre>';
//                            print_r($license_info);
//                            echo '</pre>';
//                            echo '<pre>';
//                            print_r($album_items);
//                            echo '</pre>';
                            $i = 0;
                            foreach ($album_items as $item) {
                                $audio = $this->Audio_model->fetch_audio_by_id($item['id_audio']);
                                if (!empty($audio)) {
//                                    echo '<pre>';
//                                    print_r($audio);
//                                    echo '</pre>';
                                    $path = $this->s3_path . $this->s3_audio;
                                    if ($license_info['mp3']) {
//                                        echo 'MP3';
                                        if (!empty($audio['untagged_mp3'])) {
                                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
                                            $this->zip->add_data($audio['untagged_mp3_name'], $data_track_stems);
                                        }
                                    }
                                    if ($license_info['wav']) {
//                                        echo 'WAV';
                                        if (!empty($audio['untagged_wav'])) {
                                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
                                            $this->zip->add_data($audio['untagged_wav_name'], $data_track_stems);
                                        }
                                    }
                                    if ($license_info['trackout_stems']) {
//                                        echo 'ZIP';
                                        if (!empty($audio['track_stems'])) {
                                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
                                            $this->zip->add_data($audio['track_stems_name'], $data_track_stems);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $file_name = $audio['title'];
                    $file_name = urlencode($file_name);
                    // Write the zip file to a folder on your server. Name it "my_backup.zip"
                    //$this->zip->archive($this->temp_dir . '/my_backup.zip');
                    // Download the file to your desktop. Name it "my_backup.zip"
                    $this->zip->download($file_name . '.zip');

                    //
                    //end pack
                    //
                }


                //Create PDF.
                //
                //Include pdf and files in zip.
                //
            }
        }
    }

    public function free_download($user_id = null, $item_id = null, $type = null, $code = null, $hash = null, $license_id = null) {

        if (empty($user_id) || empty($item_id) || empty($type) || empty($code) || empty($hash)) {
            echo 'Error';
        } elseif ($hash != sha1($user_id . $item_id . $code)) {
            echo 'ErrorB';
        } else {
            $this->load->library('zip');
            $item_license = $this->License_model->fetch_item_license($user_id, $item_id, $code, $hash);
            if (empty($item_license)) {
                echo 'Error';
            } else {
//                echo '<pre>';
//                print_r($item_license);
//                echo '</pre>';exit;
                if ($item_license['item_track_type'] == 'beat') {
                    //$item_id = 398;
                    $audio = $this->Audio_model->fetch_audio_by_id($item_id);
                    if (empty($audio)) {
                        echo 'Error';
                    } else {
                        $path = $this->s3_path . $this->s3_audio;
                        if ($item_license['mp3']) {
//                                        echo 'MP3';
                            if (!empty($audio['untagged_mp3'])) {
                                $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
                                $this->zip->add_data($audio['untagged_mp3_name'], $data_track_stems);
                            }
                        }
                        if ($item_license['wav']) {
//                                        echo 'WAV';
                            if (!empty($audio['untagged_wav'])) {
                                $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
                                $this->zip->add_data($audio['untagged_wav_name'], $data_track_stems);
                            }
                        }
                        if ($item_license['trackout_stems']) {
//                                        echo 'ZIP';
                            if (!empty($audio['track_stems'])) {
                                $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
                                $this->zip->add_data($audio['track_stems_name'], $data_track_stems);
                            }
                        }
                        $file_name = $audio['title'];
                        $file_name = urlencode($file_name);
                        // Write the zip file to a folder on your server. Name it "my_backup.zip"
                        //$this->zip->archive($this->temp_dir . '/my_backup.zip');
                        // Download the file to your desktop. Name it "my_backup.zip"
                        $this->zip->download($file_name . '.zip');
                    }
                } elseif ($item_license['item_track_type'] == 'kit') {
                    //
                    //kit
                    //
                    $audio = $this->Audio_model->fetch_audio_by_id($item_id);
                    if (empty($audio)) {
                        echo 'Error';
                    } else {
                        $path = $this->s3_path . $this->s3_audio;
                        if (!empty($audio['track_stems'])) {
                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
                            $this->zip->add_data($audio['track_stems_name'], $data_track_stems);
                        }
                        if (!empty($audio['tagged_file'])) {
                            $data_tagged_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['tagged_file']);
                            $this->zip->add_data($audio['tagged_file_name'], $data_tagged_file);
                        }
                    }
                    $file_name = $audio['title'];
                    $file_name = urlencode($file_name);
                    // Write the zip file to a folder on your server. Name it "my_backup.zip"
                    //$this->zip->archive($this->temp_dir . '/my_backup.zip');
                    // Download the file to your desktop. Name it "my_backup.zip"
                    $this->zip->download($file_name . '.zip');

                    //
                    //end kit
                    //
                } elseif ($item_license['item_track_type'] == 'pack') {
                    //
                    //pack
                    //
//                    $item_id = 34;
                    $album = $this->Album_model->fetch_album_by_id($item_id);
                    if (empty($album)) {
                        echo 'Error';
                    } else {

                        $license_info = $this->License_model->fetch_license_by_id($album['license_id']);

                        $album_items = $this->Album_model->fetch_album_audio_by_album_id($item_id);
                        if (empty($album_items)) {
                            echo 'Error';
                        } else {
//                            echo '<pre>';
//                            print_r($album);
//                            echo '</pre>';
//                            echo '<pre>';
//                            print_r($license_info);
//                            echo '</pre>';
//                            echo '<pre>';
//                            print_r($album_items);
//                            echo '</pre>';
                            $i = 0;
                            foreach ($album_items as $item) {
                                $audio = $this->Audio_model->fetch_audio_by_id($item['id_audio']);
                                if (!empty($audio)) {
//                                    echo '<pre>';
//                                    print_r($audio);
//                                    echo '</pre>';
                                    $path = $this->s3_path . $this->s3_audio;
                                    if ($license_info['mp3']) {
//                                        echo 'MP3';
                                        if (!empty($audio['untagged_mp3'])) {
                                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
                                            $this->zip->add_data($audio['untagged_mp3_name'], $data_track_stems);
                                        }
                                    }
                                    if ($license_info['wav']) {
//                                        echo 'WAV';
                                        if (!empty($audio['untagged_wav'])) {
                                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
                                            $this->zip->add_data($audio['untagged_wav_name'], $data_track_stems);
                                        }
                                    }
                                    if ($license_info['trackout_stems']) {
//                                        echo 'ZIP';
                                        if (!empty($audio['track_stems'])) {
                                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
                                            $this->zip->add_data($audio['track_stems_name'], $data_track_stems);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $file_name = $audio['title'];
                    $file_name = urlencode($file_name);
                    // Write the zip file to a folder on your server. Name it "my_backup.zip"
                    //$this->zip->archive($this->temp_dir . '/my_backup.zip');
                    // Download the file to your desktop. Name it "my_backup.zip"
                    $this->zip->download($file_name . '.zip');

                    //
                    //end pack
                    //
                }


                //Create PDF.
                //
                //Include pdf and files in zip.
                //
            }
        }
    }

    public function download($encode_url = null) {
        if (empty($encode_url)) {
            return false;
        }
        $data = $this->general_library->decode_download_url($encode_url);
        if (empty($data)) {
            return false;
        }
//        echo '<pre>';
//        print_r($data);
//        echo '</pre>';
//        exit;
        $item_license = $this->License_model->fetch_item_license($data['user_id'], $data['item_id'], $data['invoice_id'], $data['invoice_detail_id']);
        if (empty($item_license)) {
            return false;
        }
        if ($data['key'] != sha1($item_license['producer_id'])) {
            return false;
        }
//        echo '<pre>';
//        print_r($item_license);
//        echo '</pre>';
//        exit;
        $this->load->library('zip');
        $path = $this->s3_path . $this->s3_audio;
        if ($item_license['item_track_type'] == 'pack') {
            $album_items = $this->Album_model->fetch_album_audio_by_album_id($item_license['item_id']);
            if (empty($album_items)) {
                return false;
            }
            foreach ($album_items as $item) {
                $audio = $this->Audio_model->fetch_audio_by_id($item['id_audio']);
                if (!empty($audio)) {
                    if ($license_info['mp3']) {
                        if (!empty($audio['untagged_mp3'])) {
                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
                            $this->zip->add_data($audio['untagged_mp3_name'], $data_track_stems);
                        }
                    }
                    if ($license_info['wav']) {
                        if (!empty($audio['untagged_wav'])) {
                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
                            $this->zip->add_data($audio['untagged_wav_name'], $data_track_stems);
                        }
                    }
                    if ($license_info['trackout_stems']) {
                        if (!empty($audio['track_stems'])) {
                            $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $audio['track_stems']);
                            $this->zip->add_data($audio['track_stems_name'], $data_track_stems);
                        }
                    }
                }
            }
            $file_name = $item_license['item_title'];
            $file_name = urlencode($file_name);
            // Write the zip file to a folder on your server. Name it "my_backup.zip"
            //$this->zip->archive($this->temp_dir . '/my_backup.zip');
            // Download the file to your desktop. Name it "my_backup.zip"
            $this->zip->download($file_name . '.zip');
        } elseif ($item_license['item_track_type'] == 'kit') {
            if (!empty($item_license['track_stems'])) {
                $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $item_license['track_stems']);
                $this->zip->add_data($item_license['track_stems_name'], $data_track_stems);
            }
            if (!empty($item_license['tagged_file'])) {
                $data_tagged_file = $this->aws_s3->s3_read($this->bucket, $path, $item_license['tagged_file']);
                $this->zip->add_data($item_license['tagged_file_name'], $data_tagged_file);
            }
            $file_name = $item_license['item_title'];
            $file_name = urlencode($file_name);
            // Write the zip file to a folder on your server. Name it "my_backup.zip"
            //$this->zip->archive($this->temp_dir . '/my_backup.zip');
            // Download the file to your desktop. Name it "my_backup.zip"
            $this->zip->download($file_name . '.zip');
        } elseif ($item_license['item_track_type'] == 'beat') {
            if ($item_license['mp3']) {
                if (!empty($item_license['untagged_mp3'])) {
                    $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $item_license['untagged_mp3']);
                    $this->zip->add_data($item_license['untagged_mp3_name'], $data_track_stems);
                }
            }
            if ($item_license['wav']) {
                if (!empty($item_license['untagged_wav'])) {
                    $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $item_license['untagged_wav']);
                    $this->zip->add_data($item_license['untagged_wav_name'], $data_track_stems);
                }
            }
            if (!empty($item_license['track_stems'])) {
                $data_track_stems = $this->aws_s3->s3_read($this->bucket, $path, $item_license['track_stems']);
                $this->zip->add_data($item_license['track_stems_name'], $data_track_stems);
            }
            if (!empty($item_license['tagged_file'])) {
                $data_tagged_file = $this->aws_s3->s3_read($this->bucket, $path, $item_license['tagged_file']);
                $this->zip->add_data($item_license['tagged_file_name'], $data_tagged_file);
            }
            $file_name = $item_license['item_title'];
            $file_name = urlencode($file_name);
            // Write the zip file to a folder on your server. Name it "my_backup.zip"
            //$this->zip->archive($this->temp_dir . '/my_backup.zip');
            // Download the file to your desktop. Name it "my_backup.zip"
            $this->zip->download($file_name . '.zip');
        }
    }

}
