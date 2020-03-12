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

    public function __construct() {
        parent::__construct();
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        //Models
        $this->load->model("User_model");
        //Libraries
        $this->load->library(array('Instagram_api', 'aws_s3', 'Aws_pinpoint'));
        //Helpers
        $this->load->helper('email');
    }

//    public function _remap($method, $arguments = array()) {
//        $requestMethod = strtolower($this->input->server('REQUEST_METHOD'));
//
//        if ($method == 'index') {
//            $callMethod = strtolower($requestMethod);
//        } else {
//            //$callMethod = $requestMethod . ucfirst($method);
//            $callMethod = ucfirst($method).'_'.$requestMethod;
//        }
//
//        if (method_exists($this, $callMethod)) {
//            return call_user_func_array(array($this, $callMethod), $arguments);
//        }
//
//        throw new \BadMethodCallException("{$callMethod} does not exist!");
//    }
//     $this->response("Wrong email or password.", REST_Controller::HTTP_BAD_REQUEST);
//     $this->response([
//                    'status' => TRUE,
//                    'message' => 'User login successful.',
//                    'data' => $user
//                ], REST_Controller::HTTP_OK);
//                
    //id
    public function index_get($id = null) {
        $data = array();
        if (!empty($id)) {
            $register_user = $this->User_model->fetch_user_by_id($id);
            if (!empty($register_user)) {
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $register_user), RestController::HTTP_OK);
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
        $email = strip_tags($this->input->post('email'));
        $user_name = strip_tags($this->input->post('user_name'));
        $platform = strip_tags($this->input->post('platform'));
        if ((!empty($email) || !empty($user_name)) && !empty($platform)) {
            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email));
            if (empty($register_user)) {
                $user = array();
                $user['user_name'] = $user_name;
                $user['first_name'] = (!empty($this->input->post('first_name'))) ? $this->input->post('first_name') : '';
                $user['last_name'] = (!empty($this->input->post('last_name'))) ? $this->input->post('last_name') : '';
                $user['display_name'] = (!empty($this->input->post('display_name'))) ? $this->input->post('display_name') : '';
                $user['email'] = $email;
                $user['email_confirmed'] = '1';
                $user['password'] = (!empty($this->input->post('password'))) ? $this->general_library->encrypt_txt($this->input->post('password')) : '';
                $user['status_id'] = '3';
                $user['plan_id'] = '1';
                $user['url'] = (!empty($this->input->post('url'))) ? $this->input->post('url') : '';
                $user['phone'] = (!empty($this->input->post('phone'))) ? $this->input->post('phone') : '';
                $user['image'] = (!empty($this->input->post('image'))) ? $this->input->post('image') : '';
                $user['banner'] = (!empty($this->input->post('banner'))) ? $this->input->post('banner') : '';
                $user['about'] = (!empty($this->input->post('about'))) ? $this->input->post('about') : '';
//            $user['youtube'] = (!empty($this->input->post('youtube'))) ? $this->input->post('youtube') : '';
//            $user['facebook'] = (!empty($this->input->post('facebook'))) ? $this->input->post('facebook') : '';
//            $user['instagram'] = (!empty($this->input->post('instagram'))) ? $this->input->post('instagram') : '';
//            $user['twitter'] = (!empty($this->input->post('twitter'))) ? $this->input->post('twitter') : '';
//            $user['soundcloud'] = (!empty($this->input->post('soundcloud'))) ? $this->input->post('soundcloud') : '';
                $user['email_paypal'] = (!empty($this->input->post('email_paypal'))) ? $this->input->post('email_paypal') : '';
                $user['platform'] = $platform;
                $user['platform_id'] = (!empty($this->input->post('platform_id'))) ? $this->input->post('platform_id') : '';
                $user['platform_token'] = (!empty($this->input->post('platform_token'))) ? $this->input->post('platform_token') : '';
                $user['bio'] = (!empty($this->input->post('bio'))) ? $this->input->post('bio') : '';
                $user['id'] = $this->User_model->insert_user($user);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The user info has been created successfully.', 'id' => $user['id']), RestController::HTTP_OK);
            } else {
                $this->error = 'The given email already exists.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide complete user info to add';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function index_put($id = null) {
        if (!empty($id)) {
            $register_user = $this->User_model->fetch_user_by_id($id);
            if (!empty($register_user)) {
                //$user = array();
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
                    //$register_user['image'] = $this->put("image");
                    $image = $this->put("image");
                    preg_match("/^data:image\/(.*);base64/i", $image, $match);
                    $ext = (!empty($match[1])) ? $match[1] : '.png';
                    $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
                    $register_user['image'] = $image_name;
                    //upload image to server 
                    $source = $this->get_temp_dir();
                    file_put_contents($source . '/' . $image_name, file_get_contents($image));
                    //SAVE S3
                    //$bucket = 'files.link.stream';
                    //$path = (ENV == 'live') ? 'Prod/' : 'Dev/';
                    $dest_folder = 'Profile';
                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
                    $s3_source = $source . '/' . $image_name;
                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
                    unlink($source . '/' . $image_name);
                }
                if (!empty($this->put('banner'))) {
                    //$register_user['banner'] = $this->put("banner");
                    $banner = $this->put("banner");
                    preg_match("/^data:image\/(.*);base64/i", $banner, $match);
                    $ext = (!empty($match[1])) ? $match[1] : '.png';
                    $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
                    $register_user['banner'] = $image_name;
                    //upload image to server 
                    $source = $this->get_temp_dir();
                    file_put_contents($source . '/' . $image_name, file_get_contents($banner));
                    //SAVE S3
                    //$bucket = 'files.link.stream';
                    //$path = (ENV == 'live') ? 'Prod/' : 'Dev/';
                    $dest_folder = 'Profile';
                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
                    $s3_source = $source . '/' . $image_name;
                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
                    unlink($source . '/' . $image_name);
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
                //if (!empty($user)) {
                $this->User_model->update_user($id, $register_user);
                //}
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The user info has been updated successfully.', 'data' => $register_user), RestController::HTTP_OK);
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
        $data = array();
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
                    unset($register_user['password']);
                    unset($register_user['email_confirmed']);
                    unset($register_user['status_id']);
                    unset($register_user['facebook']);
                    unset($register_user['instagram']);
                    unset($register_user['twitter']);
                    unset($register_user['soundcloud']);
                    unset($register_user['youtube']);
                    $this->response(array('status' => 'success', 'env' => ENV, 'data' => $register_user), RestController::HTTP_OK);
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
                unset($user['password']);
                //unset($register_user['email_confirmed']);
                unset($user['status_id']);
                // unset($register_user['facebook']);
                // unset($register_user['instagram']);
                //unset($register_user['twitter']);
                // unset($register_user['soundcloud']);
                // unset($register_user['youtube']);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user), RestController::HTTP_OK);
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
        //$var = $this->session->userdata;
        // $email = $var['register-email'];
        //$id = $var['register-id'];
        //$user = $var['register-user'];
        $email_e = $this->general_library->urlsafe_b64encode($user['email']);
        $id_e = $this->general_library->urlsafe_b64encode($user['id']);
        //$user_e = $this->general_library->urlsafe_b64encode($user['user_name']);
        $base = (ENV == 'dev' || ENV == 'staging') ? 'https://dev-link-vue.link.stream' : 'https://link.stream';
        $url = $base . '/email-confirm/' . $email_e . '/' . $id_e;
        $body = $this->load->view('app/email/email-confirm', array('user' => $user['user_name'], 'email' => $user['email'], 'url' => $url), true);
        //$body = $this->load->view('email/email_register', array('user' => $user['user_name'], 'email' => $user['email'], 'url' => $url), true);
        $this->general_library->send_ses($user['email'], $user['email'], 'LinkStream', 'noreply@link.stream', "Register on LinkStream", $body);
    }

    public function email_confirm_post() {
        //$data = array();
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
                    $this->User_model->update_user($register_user['id'], array('email_confirmed' => '1'));
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
        //$data = array();
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
//        $user_id = strip_tags($this->input->post('user_id'));
//        $instagram_username = strip_tags($this->input->post('instagram_username'));
//        $access_token = strip_tags($this->input->post('platform_token'));
//        //$instagram_avatar = strip_tags($this->input->post('instagram_avatar_url'));
//        
        $code = $this->input->post('code');
        $redirect_url = $this->input->post('redirect_url');
        $auth_response = $this->instagram_api->authorize_2($code, $redirect_url);
        if (!empty($auth_response->error_type)) {
//            print_r($auth_response->error_type);
//            echo '<br>';
//            print_r($auth_response->code);
//            echo '<br>';
//            print_r($auth_response->error_message);
//            echo '<br>';
            $this->error = $auth_response->code . ':' . $auth_response->error_message;
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        } else {
            $access_token = $auth_response->access_token;
            $user_id = $auth_response->user_id;
            $instagram_user = $this->instagram_api->getUserInfo($user_id, 'id,username,account_type,media_count', $access_token);
//            $instagram_avatar = (!empty($instagram_user->username)) ? $this->instagram_get_photo($instagram_user->username) : '';
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
                //$user['image'] = $instagram_avatar;
                $instagram_avatar = (!empty($instagram_user->username)) ? $this->instagram_get_photo($instagram_user->username) : '';
                $user['image'] = '';
                if (!empty($instagram_avatar)) {
                    $content = file_get_contents($instagram_avatar);
                    //
                    $image_name = time() . '.png';
                    // upload cropped image to server 
                    $source = $this->get_temp_dir();
                    file_put_contents($source . '/' . $image_name, $content);
                    //SAVE S3
                    //$bucket = 'files.link.stream';
                    //$path = (ENV == 'live') ? 'Prod/' : 'Dev/';
                    $dest_folder = 'Profile';
                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
                    $s3_source = $source . '/' . $image_name;
                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
                    //$response['file_name'] = $image_name;
                    unlink($source . '/' . $image_name);
                    $user['image'] = $image_name;
                }
                $user['status_id'] = '3';
                $user['id'] = $this->User_model->insert_user($user);
                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user), RestController::HTTP_OK);
            } else {
                $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Logged in'));
                unset($register_user['password']);
                unset($register_user['email_confirmed']);
                unset($register_user['status_id']);
                unset($register_user['facebook']);
                unset($register_user['instagram']);
                unset($register_user['twitter']);
                unset($register_user['soundcloud']);
                unset($register_user['youtube']);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $register_user), RestController::HTTP_OK);
            }
        }

//        if (!empty($user_id) && !empty($instagram_username) && !empty($access_token)) {
//            //Check User
//            $register_user = $this->User_model->fetch_user_by_search(array('platform' => 'IG', 'platform_id' => $user_id));
//            if (empty($register_user)) {
//                //Create Account
//                $user = array();
//                $user['user_name'] = $user['display_name'] = $user['url'] = (!empty($instagram_username)) ? $this->generate_username($instagram_username) : $this->generate_username();
//                $user['email'] = '';
//                $user['plan_id'] = '1';
//                $user['platform'] = 'IG';
//                $user['platform_id'] = $user_id;
//                $user['platform_token'] = $access_token;
//                //$user['image'] = $instagram_avatar;
//                $instagram_avatar = (!empty($instagram_username)) ? $this->instagram_get_photo($instagram_username) : '';
//                $user['image'] = '';
//                if (!empty($instagram_avatar)) {
//                    $content = file_get_contents($instagram_avatar);
//                    //
//                    $image_name = time() . '.png';
//                    // upload cropped image to server 
//                    $source = $this->get_temp_dir();
//                    file_put_contents($source . '/' . $image_name, $content);
//                    //SAVE S3
//                    //$bucket = 'files.link.stream';
//                    //$path = (ENV == 'live') ? 'Prod/' : 'Dev/';
//                    $dest_folder = 'Profile';
//                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
//                    $s3_source = $source . '/' . $image_name;
//                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
//                    //$response['file_name'] = $image_name;
//                    unlink($source . '/' . $image_name);
//                    $user['image'] = $image_name;
//                }
//                $user['status_id'] = '3';
//                $user['id'] = $this->User_model->insert_user($user);
//                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
//                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user), RestController::HTTP_OK);
//            } else {
//                $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Logged in'));
//                unset($register_user['password']);
//                unset($register_user['email_confirmed']);
//                unset($register_user['status_id']);
//                unset($register_user['facebook']);
//                unset($register_user['instagram']);
//                unset($register_user['twitter']);
//                unset($register_user['soundcloud']);
//                unset($register_user['youtube']);
//                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $register_user), RestController::HTTP_OK);
//            }
//        } else {
//            $this->error = 'Provide complete user info to add';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
    }

    private function instagram_get_photo($user_name) {
        $getValues = file_get_contents('https://www.instagram.com/' . $user_name . '/?__a=1');
        $jsonObj = json_decode($getValues, TRUE);
        $photoURL = $jsonObj["graphql"]["user"]["profile_pic_url_hd"];
        //print_r($photoURL);
        return $photoURL;
    }

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
                    //
                    $image_name = time() . '.png';
                    // upload cropped image to server 
                    $source = $this->get_temp_dir();
                    file_put_contents($source . '/' . $image_name, $content);
                    //SAVE S3
                    //$bucket = 'files.link.stream';
                    //$path = (ENV == 'live') ? 'Prod/' : 'Dev/';
                    $dest_folder = 'Profile';
                    $destination = $this->s3_path . $dest_folder . '/' . $image_name;
                    $s3_source = $source . '/' . $image_name;
                    $this->aws_s3->s3push($s3_source, $destination, $this->bucket);
                    //$response['file_name'] = $image_name;
                    unlink($source . '/' . $image_name);
                    $user['image'] = $image_name;
                }
                //$user['image'] = $token_info->picture;
                $user['status_id'] = '3';
                $user['email_confirmed'] = '1';
                $user['id'] = $this->User_model->insert_user($user);
                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user), RestController::HTTP_OK);
            } else {
                $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Logged in'));
                unset($register_user['password']);
                unset($register_user['email_confirmed']);
                unset($register_user['status_id']);
                unset($register_user['facebook']);
                unset($register_user['instagram']);
                unset($register_user['twitter']);
                unset($register_user['soundcloud']);
                unset($register_user['youtube']);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $register_user), RestController::HTTP_OK);
            }
            return $this->output->set_output(json_encode(['success' => true]));
        } else {
            $this->error = 'Invalid token';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
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

    public function status_get() {
        $status = $this->User_model->fetch_user_status();
        $this->response(array('status' => 'success', 'env' => ENV, 'data' => $status), RestController::HTTP_OK);
    }

    public function plan_get() {
        $status = $this->User_model->fetch_user_plan();
        $this->response(array('status' => 'success', 'env' => ENV, 'data' => $status), RestController::HTTP_OK);
    }

    public function forgot_password_post() {
        //$data = array();
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
        $this->general_library->send_ses($user['email'], $user['email'], 'LinkStream', 'noreply@link.stream', "Register on LinkStream", $body);
    }

    //https://dev-link-vue.link.stream/reset-password/cGF1bEBsaW5rLnN0cmVhbQ../MzU.
    public function password_reset_post() {
        //$data = array();
        $email_e = $this->input->post('param_1');
        $id_e = $this->input->post('param_2');
        //$date_e = $this->input->post('param_3');
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
//        if (!empty($email_e) && !empty($id_e) && !empty($date_e)) {
//            $email_decode = $this->general_library->urlsafe_b64decode($email_e);
//            $id_decode = $this->general_library->urlsafe_b64decode($id_e);
//            $id = (!empty($id_decode)) ? $id_decode : 'N';
//            $email = (valid_email($email_decode)) ? $email_decode : 'N';
//            $date = $this->general_library->urlsafe_b64decode($date_e);
//            //print_r('id' . $id);
//            //print_r('date' . $date);
//            //exit;
//            if ($date >= date("Y-m-d H:i:s")) {
//                //Check User
//                $register_user = $this->User_model->fetch_user_by_search(array('email' => $email, 'id' => $id));
//                if (!empty($register_user)) {
//                    $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
//                } else {
//                    $this->error = 'User Not Found.';
//                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//                }
//            } else {
//                $this->error = 'Your link has expired.';
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            }
//            //Check User
//            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email, 'id' => $id));
//            if (!empty($register_user)) {
//                if ($register_user['email_confirmed'] == '1') {
//                    $this->error = 'Email already confirmed previously';
//                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//                } else {
//                    $this->User_model->update_user($register_user['id'], array('email_confirmed' => '1'));
//                    $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Confirmed Email'));
//                    $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
//                }
//            } else {
//                $this->error = 'User Not Found.';
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            }
//        } else {
//            $this->error = 'Provide complete info';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
    }

}
