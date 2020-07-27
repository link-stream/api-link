<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Users extends RestController {

    private $error;
    private $bucket;
    private $s3_path;
    private $s3_folder;
    private $s3_coverart;
    private $temp_dir;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model(array('User_model', 'Audio_model', 'Album_model'));
        //Libraries
        $this->load->library(array('Instagram_api', 'aws_s3', 'Aws_pinpoint'));
        //Helpers
        $this->load->helper(array('email'));
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_folder = 'Profile';
        $this->s3_coverart = 'Coverart';
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

    public function index_get($id = null) {
        if (!$this->general_library->header_token($id)) {
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
        }
        if (!empty($id)) {
            $register_user = $this->User_model->fetch_user_by_id($id);
            if (!empty($register_user)) {
                $user_response = $this->user_clean($register_user);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_post() {
        die('Nothing here');
    }

    public function index_put($id = null) {
        if (!$this->general_library->header_token($id)) {
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
        }
        if (!empty($id)) {
            $register_user = $this->User_model->fetch_user_by_id($id);
            if (!empty($register_user)) {
                if (!empty($this->put('user_name'))) {
                    $register_user['user_name'] = $this->put('user_name');
                }
                if (!empty($this->put('first_name'))) {
                    $register_user['first_name'] = $this->put('first_name');
                }
                if (!empty($this->put('last_name'))) {
                    $register_user['last_name'] = $this->put('last_name');
                }
                if (!empty($this->put('display_name'))) {
                    $register_user['display_name'] = $this->put('display_name');
                }
                if (!empty($this->put('email'))) {
                    $register_user['email'] = $this->put('email');
                }
                if (!empty($this->put('email_confirmed'))) {
                    $register_user['email_confirmed'] = $this->put('email_confirmed');
                }
                if (!empty($this->put('current_password'))) {
                    $current_password = $this->general_library->encrypt_txt($this->put('current_password'));
                    if ($current_password != $register_user['password']) {
                        $this->error = 'Current Password Not Match.';
                        $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                    }
                }
                if (!empty($this->put('password'))) {
                    $register_user['password'] = $this->general_library->encrypt_txt($this->put('password'));
                }
                if (!empty($this->put('status_id'))) {
                    $register_user['status_id'] = $this->put('status_id');
                }
                if (!empty($this->put('plan_id'))) {
                    $register_user['plan_id'] = $this->put('plan_id');
                }
                if (!empty($this->put('url'))) {
                    $register_user['url'] = $this->put('url');
                }
                if (!empty($this->put('phone'))) {
                    $register_user['phone'] = $this->put('phone');
                }
                if (!empty($this->put('image'))) {
                    $image = $this->put("image");
                    $register_user['image'] = $this->image_decode_put($image);
                }
                if (!empty($this->put('banner'))) {
                    $banner = $this->put("banner");
                    $register_user['banner'] = $this->image_decode_put($banner);
                }
                if (!empty($this->put('about'))) {
                    $register_user['about'] = $this->put('about');
                }
                if (!empty($this->put('email_paypal'))) {
                    $register_user['email_paypal'] = $this->put('email_paypal');
                }
                if (!empty($this->put('bio'))) {
                    $register_user['bio'] = $this->put('bio');
                }
                if (!empty($this->put('city'))) {
                    $register_user['city'] = $this->put('city');
                }
                if (!empty($this->put('country'))) {
                    $register_user['country'] = $this->put('country');
                }
                if (!empty($this->put('timezone'))) {
                    $register_user['timezone'] = $this->put('timezone');
                }
                if (!empty($this->put('facebook'))) {
                    $register_user['facebook'] = $this->put('facebook');
                }
                if (!empty($this->put('twitter'))) {
                    $register_user['twitter'] = $this->put('twitter');
                }
                if (!empty($this->put('instagram'))) {
                    $register_user['instagram'] = $this->put('instagram');
                }
                if (!empty($this->put('soundcloud'))) {
                    $register_user['soundcloud'] = $this->put('soundcloud');
                }
                $this->User_model->update_user($id, $register_user);
                $user_response = $this->user_clean($register_user);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The user info has been updated successfully.', 'data' => $user_response), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    //params: type = username or email
    public function availability_get($type = null, $value = null, $id = null) {
        if (empty($type)) {
            $this->error = 'Type is Required';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        } elseif (empty($value)) {
            $this->error = 'Value is Required';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        } elseif ($type != 'username' && $type != 'email' && $type != 'url') {
            $this->error = 'Type ' . $type . ' is now allowed, only username or email are allowed as type';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        } else {
            if ($type == 'username') {
                $register_user = $this->User_model->fetch_user_by_search(array('user_name' => $value));
            } elseif ($type == 'email') {
                $register_user = $this->User_model->fetch_user_by_search(array('email' => $value));
            } else {
                if (!empty($id)) {
                    $register_user = $this->User_model->fetch_user_url_availability($id, $value);
                } else {
                    $this->error = 'Provide User ID.';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
            }
            if (empty($register_user)) {
                $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
            } else {
                $this->error = ucfirst($type) . ': ' . $value . ' is not available';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        }
    }

    //params = email, password
    public function login_post() {
        // Get the post data
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        // Validate the post data
        if (!empty($email) && !empty($password)) {
            // Check if any user exists with the given credentials
            $password_e = $this->general_library->encrypt_txt($password);
            //Check Email And User
            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email, 'password' => $password_e));
            if (!empty($register_user)) {
                if ($register_user['status_id'] == 1) {
                    $register_user['token'] = $this->User_model->create_token($register_user['id']);
                    $user_response = $this->user_clean($register_user);
                    $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
                } else {
                    $this->error = 'User in PENDING Status, please confirm your email';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
            } else {
                $this->error = 'Email or Password Incorrect';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            // Set the response and exit
            $this->error = 'Provide email and password.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function logout_post() {
        if ($this->general_library->unset_token()) {
            $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
        } else {
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
        }
    }

    private function generate_username($user_name = null) {
        $streamy_username = (!empty($user_name)) ? strtolower(str_replace(' ', '', $user_name)) : uniqid();
        $register_user = $this->User_model->fetch_user_by_search(array('user_name' => $streamy_username));
        if (empty($register_user)) {
            return $streamy_username;
        } else {
            $streamy_username = strtolower(str_replace(' ', '', $user_name)) . rand(0, 100);
            $register_user = $this->User_model->fetch_user_by_search(array('user_name' => $streamy_username));
            if (empty($register_user)) {
                return $streamy_username;
            } else {
                return $this->generate_username($streamy_username);
            }
        }
    }

    private function generate_password() {
        $tmp_pass = md5(uniqid(rand(), true));
        $password = $this->general_library->encrypt_txt($tmp_pass);
        return $password;
    }

    public function registration_post() {
        // Get the post data
        $email = strip_tags($this->input->post('email'));
        $password = $this->input->post('password');
        $user_name = strip_tags($this->input->post('user_name'));
        if (!empty($email) && !empty($user_name) && !empty($password)) {
            //Check Email And User
            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email));
            if (empty($register_user)) {
                $user_name = $this->generate_username($user_name);
                $user = array();
                $user['user_name'] = $user['display_name'] = $user['url'] = strtolower(str_replace(' ', '', $user_name));
                $user['email'] = $email;
                $user['password'] = $this->general_library->encrypt_txt($password);
                $user['plan_id'] = '1';
                $user['status_id'] = '3';
                $user['platform'] = 'LinkStream';
                $user['id'] = $this->User_model->insert_user($user);
                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
                $this->register_email($user);
                $user['token'] = $this->User_model->create_token($user['id']);
                $user_response = $this->user_clean($user);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
            } else {
                $this->error = 'The given email already exists.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide complete user info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function register_email($user) {
        $email_e = $this->general_library->urlsafe_b64encode($user['email']);
        $id_e = $this->general_library->urlsafe_b64encode($user['id']);
        $base = (ENV == 'dev' || ENV == 'staging') ? 'https://dev-link-vue.link.stream' : 'https://link.stream';
        $url = $base . '/email-confirm/' . $email_e . '/' . $id_e;
        $body = $this->load->view('app/email/email-confirm', array('user' => $user['user_name'], 'email' => $user['email'], 'url' => $url), true);
        $this->general_library->send_ses($user['email'], $user['email'], 'LinkStream', 'noreply@linkstream.com', "Register on LinkStream", $body);
    }

    public function email_confirm_post() {
        $email_e = $this->input->post('param_1');
        $id_e = $this->input->post('param_2');
        if (!empty($email_e) && !empty($id_e)) {
            $email_decode = $this->general_library->urlsafe_b64decode($email_e);
            $email = (valid_email($email_decode)) ? $email_decode : 'N';
            $id_decode = $this->general_library->urlsafe_b64decode($id_e);
            $id = (!empty($id_decode)) ? $id_decode : 'N';
            //Check User
            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email, 'id' => $id));
            if (!empty($register_user)) {
                if ($register_user['email_confirmed'] == '1') {
                    $this->error = 'Email already confirmed previously';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                } else {
                    $this->User_model->update_user($register_user['id'], array('email_confirmed' => '1', 'status_id' => '1'));
                    $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Confirmed Email'));
                    $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
                }
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide complete info';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function resend_email_confirm_post() {
        $id = $this->input->post('user_id');
        if (!empty($id)) {
            //Check User
            $register_user = $this->User_model->fetch_user_by_id($id);
            if (!empty($register_user)) {
                $this->register_email($register_user);
                $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function instagram_post() {
        $code = $this->input->post('code');
        $redirect_url = $this->input->post('redirect_url');
        $auth_response = $this->instagram_api->authorize_2($code, $redirect_url);
        if (!empty($auth_response->error_type)) {
            $this->error = $auth_response->code . ':' . $auth_response->error_message;
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        } else {
            $access_token = $auth_response->access_token;
            $user_id = $auth_response->user_id;
            $instagram_user = $this->instagram_api->getUserInfo($user_id, 'id,username,account_type,media_count', $access_token);
            //Check User
            $register_user = $this->User_model->fetch_user_by_search(array('platform' => 'IG', 'platform_id' => $user_id));
            if (empty($register_user)) {
                //Create Account
                $user = array();
                $user['user_name'] = $user['display_name'] = $user['url'] = (!empty($instagram_user->username)) ? $this->generate_username($instagram_user->username) : $this->generate_username();
                $user['email'] = '';
                $user['plan_id'] = '1';
                $user['platform'] = 'IG';
                $user['platform_id'] = $user_id;
                $user['platform_token'] = $access_token;
                $instagram_avatar = (!empty($instagram_user->username)) ? $this->instagram_get_photo($instagram_user->username) : '';
                $user['image'] = '';
                if (!empty($instagram_avatar)) {
                    $content = file_get_contents($instagram_avatar);
                    $image_name = md5(uniqid(rand(), true)) . '.png';
                    //upload cropped image to server 
                    file_put_contents($this->temp_dir . '/' . $image_name, $content);
                    //SAVE S3
                    $this->s3_push($image_name);
                    $user['image'] = $image_name;
                }
                $user['status_id'] = '1';
                $user['id'] = $this->User_model->insert_user($user);
                $user['token'] = $this->User_model->create_token($user['id']);
                $user_response = $this->user_clean($user);
                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
            } else {
                $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Logged in'));
                $register_user['token'] = $this->User_model->create_token($register_user['id']);
                $user_response = $this->user_clean($register_user);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
            }
        }
    }

    private function instagram_get_photo($user_name) {
        $getValues = file_get_contents('https://www.instagram.com/' . $user_name . '/?__a=1');
        $jsonObj = json_decode($getValues, TRUE);
        $photoURL = $jsonObj["graphql"]["user"]["profile_pic_url_hd"];
        return $photoURL;
    }

//    public function instagram_get_photo_get($user_name) {
//        echo $user_name;
//        echo '<br>';
//        echo '<br>';
//        $getValues = file_get_contents('https://www.instagram.com/' . $user_name . '/?__a=1');
//        $jsonObj = json_decode($getValues, TRUE);
//        $photoURL = $jsonObj["graphql"]["user"]["profile_pic_url_hd"];
//        echo $photoURL;
//        echo '<br>';
//        echo '<br>';
//        $instagram_avatar = $photoURL;
//        $content = file_get_contents($instagram_avatar);
//        $image_name = md5(uniqid(rand(), true)) . '.png';
//        echo $image_name;
//        echo '<br>';
//        echo '<br>';
//        //upload cropped image to server 
//        file_put_contents($this->temp_dir . '/' . $image_name, $content);
//        //SAVE S3
//        $this->s3_push($image_name);
//    }

    public function google_post() {
        //$this->output->set_content_type('application/json');
        $token = $this->input->post('platform_token');
        if (empty($token)) {
            $this->error = 'Missing token';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $token_info_json = curl_exec($ch);
            curl_close($ch);
            $token_info = json_decode($token_info_json);
        } catch (Exception $e) {
            $this->error = $e;
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
        if (isset($token_info->email, $token_info->aud) && $token_info->aud == GOOGLE_LOGIN_CLIENT_ID) {
            //Check User
            $register_user = $this->User_model->fetch_user_by_search(array('platform' => 'Google', 'platform_id' => $token_info->sub));
            if (empty($register_user)) {
                //Create Account
                $user = array();
                $user['user_name'] = $user['display_name'] = $user['url'] = (!empty($token_info->name)) ? $this->generate_username($token_info->name) : $this->generate_username();
                $user['first_name'] = (!empty($token_info->given_name)) ? $token_info->given_name : '';
                $user['last_name'] = (!empty($token_info->family_name)) ? $token_info->family_name : '';
                $user['email'] = (!empty($token_info->email)) ? $token_info->email : '';
                $user['plan_id'] = '1';
                $user['platform'] = 'Google';
                $user['platform_id'] = $token_info->sub;
                $user['platform_token'] = $token;
                $user['image'] = '';
                if (!empty($token_info->picture)) {
                    $content = file_get_contents($token_info->picture);
                    $image_name = md5(uniqid(rand(), true)) . '.png';
                    //upload cropped image to server 
                    file_put_contents($this->temp_dir . '/' . $image_name, $content);
                    //SAVE S3
                    $this->s3_push($image_name);
                    $user['image'] = $image_name;
                }
                $user['status_id'] = '1';
                $user['email_confirmed'] = '1';
                $user['id'] = $this->User_model->insert_user($user);
                $user['token'] = $this->User_model->create_token($user['id']);
                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
                $user_response = $this->user_clean($user);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
            } else {
                $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Logged in'));
                $register_user['token'] = $this->User_model->create_token($register_user['id']);
                $user_response = $this->user_clean($register_user);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
            }
            return $this->output->set_output(json_encode(['success' => true]));
        } else {
            $this->error = 'Invalid token';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function status_get() {
        $status = $this->User_model->fetch_user_status();
        $this->response(array('status' => 'success', 'env' => ENV, 'data' => $status), RestController::HTTP_OK);
    }

    public function plan_get() {
        $status = $this->User_model->fetch_user_plan();
        $this->response(array('status' => 'success', 'env' => ENV, 'data' => $status), RestController::HTTP_OK);
    }

    public function forgot_password_post() {
        $email = $this->input->post('email');
        if (!empty($email)) {
            //Check User
            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email));
            if (!empty($register_user)) {
                $this->forgot_password_email($register_user);
                $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Email.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function forgot_password_email($user) {
        $email_e = $this->general_library->urlsafe_b64encode($user['email']);
        $id_e = $this->general_library->urlsafe_b64encode($user['id']);
        $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', strtotime('+10 minutes'))));
        $date_e = $this->general_library->urlsafe_b64encode($date);
        $base = (ENV == 'dev' || ENV == 'staging') ? 'https://dev-link-vue.link.stream' : 'https://link.stream';
        //$url = $base . '/reset-password/' . $email_e . '/' . $id_e . '/' . $date_e;
        $url = $base . '/reset-password/' . $email_e . '/' . $id_e;
        $body = $this->load->view('app/email/email-password-reset', array('user' => $user['user_name'], 'email' => $user['email'], 'url' => $url), true);
        $this->general_library->send_ses($user['email'], $user['email'], 'LinkStream', 'noreply@linkstream.com', "Register on LinkStream", $body);
    }

    //https://dev-link-vue.link.stream/reset-password/cGF1bEBsaW5rLnN0cmVhbQ../MzU.
    public function password_reset_post() {
        $email_e = $this->input->post('param_1');
        $id_e = $this->input->post('param_2');
        $new_pass = $this->input->post('new_password');
        if (!empty($email_e) && !empty($id_e) && !empty($new_pass)) {
            $email_decode = $this->general_library->urlsafe_b64decode($email_e);
            $id_decode = $this->general_library->urlsafe_b64decode($id_e);
            $id = (!empty($id_decode)) ? $id_decode : 'N';
            $email = (valid_email($email_decode)) ? $email_decode : 'N';
            //Check User
            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email, 'id' => $id));
            if (!empty($register_user)) {
                $password = $this->general_library->encrypt_txt($new_pass);
                $this->User_model->update_user($register_user['id'], array('password' => $password));
                $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Password Reset Successfully'));
                $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide complete info';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function collaborator_get($user_id = null) {
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $search = (!empty($this->input->get('search'))) ? $this->input->get('search') : '';
            $collaborators = $this->User_model->fetch_collaborator($search);
            $collaborators_reponse = [];
            $path = $this->s3_path . $this->s3_folder;
            foreach ($collaborators as $collaborator) {
                if ($user_id == $collaborator['id']) {
                    continue;
                }
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
                $collaborators_reponse[] = $collaborator;
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $collaborators_reponse), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function invite_collaborator_post($user_id = null, $email) {
        if (!empty($user_id) && !empty($email)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $collaborators_reponse = [];
            $path = $this->s3_path . $this->s3_folder;
            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email));
            if (!empty($register_user)) {
                $collaborators_reponse['id'] = $register_user['id'];
                $collaborators_reponse['user_name'] = $register_user['user_name'];
                $collaborators_reponse['email'] = $register_user['email'];
                $collaborators_reponse['image'] = $register_user['image'];
                $collaborators_reponse['data_image'] = '';
                if (!empty($collaborators_reponse['image'])) {
                    $data_image = $this->aws_s3->s3_read($this->bucket, $path, $collaborators_reponse['image']);
                    if (!empty($data_image)) {
                        $img_file = $collaborators_reponse['image'];
                        file_put_contents($this->temp_dir . '/' . $collaborators_reponse['image'], $data_image);
                        $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $collaborators_reponse['image']) . ';base64,' . base64_encode($data_image);
                        $collaborators_reponse['data_image'] = $src;
                        unlink($this->temp_dir . '/' . $collaborators_reponse['image']);
                    }
                }
            } else {
                //Create User ***
                $email_user = strstr($email, '@', true);
                //
                $user_name = $this->generate_username($email_user);
                $user = array();
                $user['user_name'] = $user['display_name'] = $user['url'] = strtolower(str_replace(' ', '', $user_name));
                $user['email'] = $email;
                $user['password'] = $this->generate_password();
                $user['plan_id'] = '1';
                $user['status_id'] = '3';
                $user['platform'] = 'LinkStream';
                $user['id'] = $this->User_model->insert_user($user);
                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
                $collaborators_reponse['id'] = $user['id'];
                $collaborators_reponse['user_name'] = $user['user_name'];
                $collaborators_reponse['email'] = $user['email'];
                $collaborators_reponse['image'] = '';
                $collaborators_reponse['data_image'] = '';
                //Send Invitation Email
                /////////////////////////////$this->register_email($user);
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $collaborators_reponse), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID and Email';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function purchases_get($user_id = null) {
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $purchases = $this->User_model->fetch_user_purchases($user_id);
            $response = [];
            $path = $this->s3_path . $this->s3_coverart;
            foreach ($purchases as $invoice) {
//                $invoice['details'] = $this->User_model->fetch_user_purchases_details($invoice['id']);
//                $response[] = $invoice;
                $details = $this->User_model->fetch_user_purchases_details($invoice['id']);
                $response_details = [];
                foreach ($details as $detail) {
                    $item_id = $detail['item_id'];
                    $item_track_type = $detail['item_track_type'];
                    if ($item_track_type == '1' || $item_track_type == '2' || $item_track_type == '3') {
                        $audio = $this->Audio_model->fetch_audio_by_id($item_id);
                    } else {
                        $audio = $this->Album_model->fetch_album_by_id($item_id);
                    }
                    $detail['data_image'] = '';
                    if (!empty($audio['coverart'])) {
                        $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
                        if (!empty($data_image)) {
                            $img_file = $audio['coverart'];
                            file_put_contents($this->temp_dir . '/' . $audio['coverart'], $data_image);
                            $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
                            $detail['data_image'] = $src;
                            unlink($this->temp_dir . '/' . $audio['coverart']);
                        }
                    }
                    $response_details[] = $detail;
                }
                $invoice['details'] = $response_details;
                $response[] = $invoice;
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $response), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function payment_method_post() {
        $payment_method = [];
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $first_name = (!empty($this->input->post('first_name'))) ? $this->input->post('first_name') : '';
            $last_name = (!empty($this->input->post('last_name'))) ? $this->input->post('last_name') : '';
            $cc_number = (!empty($this->input->post('cc_number'))) ? $this->input->post('cc_number') : '';
            $expiration_date = (!empty($this->input->post('expiration_date'))) ? $this->input->post('expiration_date') : '';
            $cvv = (!empty($this->input->post('cvv'))) ? $this->input->post('cvv') : '';
            $exp_month = substr($expiration_date, 0, 2);
            $exp_year = substr($expiration_date, 3);
            //Create_payment_method
            echo 'stripe_library';
            $this->load->library('stripe_library');
            $type = 'card';
            $card = [
                'number' => $cc_number,
                'exp_month' => $exp_month,
                'exp_year' => $exp_year,
                'cvc' => $cvv,
            ];
//            $response = $this->stripe_library->create_payment_method($type, $card);
            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The payment method has been created successfully.'), RestController::HTTP_OK);
            if ($response['status']) {
                //response true Save in DB
                $payment_method['user_id'] = $user_id;
                $payment_method['status'] = 'ACTIVE';
                $payment_method['first_name'] = $first_name;
                $payment_method['last_name'] = $last_name;
                $payment_method['first_cc_number'] = substr($cc_number, 0, 6);
                $payment_method['last_cc_number'] = substr($cc_number, -4);
                $payment_method['expiration_date'] = $expiration_date;
                $payment_method['cvv'] = $cvv;
                $payment_method['payment_method_key'] = $response['payment_method_id'];
                $this->User_model->insert_payment_method($payment_method);
            } else {
                $this->error = $response['error'];
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }


            //else return error.
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function payment_method_get($user_id = null) {
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $payment_methods = $this->User_model->fetch_payment_method_by_user_id($user_id);
            $response = [];
            foreach ($payment_methods as $payment_method) {
                $cc_number = (!empty($payment_method['first_cc_number']) && substr($payment_method['first_cc_number'], 0, 1) == '3') ? $payment_method['first_cc_number'] . '11111' . $payment_method['last_cc_number'] : $payment_method['first_cc_number'] . '111111' . $payment_method['last_cc_number'];
                $response[] = [
                    'id' => $payment_method['id'],
                    'cc_number' => $payment_method['last_cc_number'],
                    'expiration_date' => $payment_method['expiration_date'],
                    'is_default' => $payment_method['is_default'],
                    'cc_type' => $this->general_library->card_type($cc_number)
                ];
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $response), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function payment_method_put($id = null) {
        if (!empty($id)) {
            $payment_method = $this->User_model->fetch_payment_method_by_id($id);
            if (!empty($payment_method)) {
                if (!$this->general_library->header_token($payment_method['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                if ($this->put('is_default') !== null) {
                    $is_default = (!empty($this->put('is_default'))) ? '1' : '0';
                    $this->User_model->update_payment_method_by_user_id($payment_method['user_id'], ['is_default' => '0']);
                    $this->User_model->update_payment_method($id, ['is_default' => $is_default]);
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Payment Method info has been updated successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Payment Method Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Payment Method ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function payment_method_delete($id = null) {
        if (!empty($id)) {
            $payment_method = $this->User_model->fetch_payment_method_by_id($id);
            if (!empty($payment_method)) {
                if (!$this->general_library->header_token($payment_method['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $this->User_model->update_payment_method($id, ['status' => 'INACTIVE']);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Payment Method has been deleted successfully.'), RestController::HTTP_OK);
            } else {
                $this->error = 'Payment Method Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Payment Method ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

}
