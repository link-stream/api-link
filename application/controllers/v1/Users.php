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
    private $temp_dir;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model("User_model");
        //Libraries
        $this->load->library(array('Instagram_api', 'aws_s3', 'Aws_pinpoint'));
        //Helpers
        $this->load->helper(array('email'));
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_folder = 'Profile';
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
        unset($user['facebook']);
        unset($user['instagram']);
        unset($user['twitter']);
        unset($user['soundcloud']);
        unset($user['youtube']);
        unset($user['platform']);
        unset($user['platform_id']);
        unset($user['platform_token']);
        //Avatar & Banner
        $path = $this->s3_path . $this->s3_folder;
        $user['data_image'] = '';
        $user['data_banner'] = '';
        if ($images) {
            if (!empty($user['image'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $user['image']);
                $user['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
            }
            if (!empty($user['banner'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $user['banner']);
                $user['data_banner'] = (!empty($data_image)) ? base64_encode($data_image) : '';
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
        $this->general_library->send_ses($user['email'], $user['email'], 'LinkStream', 'noreply@link.stream', "Register on LinkStream", $body);
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
                $user['status_id'] = '3';
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
                $user['status_id'] = '3';
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
        $this->general_library->send_ses($user['email'], $user['email'], 'LinkStream', 'noreply@link.stream', "Register on LinkStream", $body);
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

}
