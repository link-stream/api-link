<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class App extends CI_Controller {

    private $loc_url = 'app';
    private $loc_path = 'app/';
    private $error;
    private $ses_name = 'app_session';
    private $limit = 0;
    private $bucket = 'files.link.stream';

    public function __construct() {
        parent::__construct();
        //Session
        $this->general_library->update_cookie();
        //Libraries
        $this->load->library(array('Instagram_api', 'aws_s3', 'Aws_pinpoint'));
        //Models
        $this->load->model("User_model");
        $this->load->model("Streamy_model");
        //Vars
        $this->error = '';
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

    private function status_pending() {
        $user = $this->general_library->get_cookie();
        if ($user['status_id'] == '3') {
            redirect($this->loc_url . '/account_settings', 'location', 302);
        }
    }

    public function index() {
        $data = array();
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            //$this->status_pending();
            $user = $this->general_library->get_cookie();
            $data['user'] = $user;
            $data['plan_modal'] = ($user['status_id'] == '3') ? true : false;
            $user['status_id'] = '1';
            $this->User_model->update_user($user['id'], array('status_id' => $user['status_id']));
            $encrypted_user = $this->general_library->urlsafe_b64encode(json_encode($user));
            $this->general_library->update_cookie(serialize(array('user' => $encrypted_user)));


            $data['title'] = 'Streamy';
            $data['page'] = 'Dashboard';
            $data['body_content'] = 'dashboard';
            $this->load->view($this->loc_path . 'layouts/layout', $data);

            //$this->load->view($this->loc_path . 'dashboard', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function logout() {
        delete_cookie($this->ses_name);
        $this->output->set_header("Expires: Tue, 01 Jul 2001 06:00:00 GMT");
        $this->output->set_header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
        $this->output->set_header("Pragma: no-cache");
        redirect('login', 'location', 302);
    }

    /* Login & Register Section */

    private function user_session($register_user) {
        $streamy_user = json_encode(array(
            'id' => $register_user['id'],
            'email' => $register_user['email'],
            'user_name' => $register_user['user_name'],
            'plan_id' => $register_user['plan_id'],
            'platform' => $register_user['platform'],
            'platform_id' => $register_user['platform_id'],
            'image' => $register_user['image'],
            'status_id' => $register_user['status_id'],
            'url' => $register_user['url']
        ));
        $this->general_library->create_cookie($streamy_user);
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

    public function verify_username() {
        $user_name = $this->input->post('username', TRUE);
        $register_user = $this->User_model->fetch_user_by_search(array('user_name' => $user_name));
        if (empty($register_user)) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function verify_email() {
        $email = $this->input->post('email', TRUE);
        $register_user = $this->User_model->fetch_user_by_search(array('email' => $email));
        if (empty($register_user)) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function test_username($username = null) {
        $streamy_username = $this->generate_username($username);
        echo $streamy_username;
    }

    public function login() {
        $data = array();
        $data['msg'] = '';
        if ($this->input->post()) {
            $email = trim($this->input->post('email', TRUE));
            $password = trim($this->input->post('password', TRUE));
            if (!empty($email) && !empty($password)) {
                $password_e = $this->general_library->encrypt_txt($password);
                //Check Email And User
                $register_user = $this->User_model->fetch_user_by_search(array('email' => $email, 'password' => $password_e));
                if (!empty($register_user)) {
                    if ($register_user['status_id'] == 1) {
                        $this->user_session($register_user);
                        redirect($this->loc_url, 'location', 302);
                    } else {
                        $data['msg'] = 'User in PENDING Status, please confirm your email';
                    }
                } else {
                    $data['msg'] = 'Email or Password Incorrect';
                }
            } else {
                $data['msg'] = 'Fields can not be empty';
            }
        }
//        $this->load->view($this->loc_path . 'signin', $data);
        $data['body_content'] = "<sign_in></sign_in>";
        $this->load->view($this->loc_path . 'layouts/common', $data);
    }

    public function login_js() {
        $status = 'Success';
        $msg = '';
//        print_r($this->input->post());
//        $email = trim($this->input->post('name', TRUE));
//        echo $email;
//        $json = json_decode(file_get_contents('php://input'), true);    
//        $email = trim($json['email']);
//        $password = trim($json['password']);
        $email = trim($this->input->post('email', TRUE));
        $password = trim($this->input->post('password', TRUE));
        if (!empty($email) && !empty($password)) {
            $password_e = $this->general_library->encrypt_txt($password);
            //Check Email And User
            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email, 'password' => $password_e));
            if (!empty($register_user)) {
                if ($register_user['status_id'] == 1) {
                    $this->user_session($register_user);
                    //redirect($this->loc_url, 'location', 302);
                } else {
                    $msg = 'User in PENDING Status, please confirm your email';
                    $status = 'Error';
                }
            } else {
                $msg = 'Email or Password Incorrect';
                $status = 'Error';
            }
        } else {
            $msg = 'Fields can not be empty';
            $status = 'Error';
        }
        echo json_encode(array('status' => $status, 'msg' => $msg));
    }

    public function register() {
        $data = array();
        $data['msg'] = '';
        //print_r($this->input->post());
        if ($this->input->post()) {
            $email = trim($this->input->post('email', TRUE));
            $user_name = trim($this->input->post('username', TRUE));
            $password = trim($this->input->post('password', TRUE));
            $confirm_password = trim($this->input->post('repassword', TRUE));
            if (!empty($email) && !empty($user_name) && !empty($password) && !empty($confirm_password)) {
                //Check Email And User
                $register_user = $this->User_model->fetch_user_by_search(array('email' => $email));
                if (empty($register_user)) {
                    $user_name = $this->generate_username($user_name);
                    //$register_user = $this->User_model->fetch_user_by_search(array('user_name' => $user_name));
//                    if (empty($register_user)) {
                    if ($password == $confirm_password) {
                        //Create Account
                        $user = array();
                        $user['user_name'] = $user['display_name'] = $user['url'] = strtolower(str_replace(' ', '', $user_name));
                        $user['email'] = $email;
                        $user['password'] = $this->general_library->encrypt_txt($password);
                        $user['plan_id'] = '1';
                        $user['status_id'] = '3';
                        $user['platform'] = 'Streamy';
                        $user['id'] = $this->User_model->insert_user($user);
                        $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
                        $this->session->set_userdata('register-email', $user['email']);
                        $this->session->set_userdata('register-id', $user['id']);
                        $this->session->set_userdata('register-user', $user['user_name']);
                        redirect($this->loc_url . '/register_confirm', 'location', 302);
                    } else {
                        $data['msg'] = 'Password and Confirm Password Should Be Equals';
                    }
//                }
//                    else {
//                        $data['msg'] = 'User Name Already Register';
//                    }
                } else {
                    $data['msg'] = 'Email Already Register';
                }
            } else {
                $data['msg'] = 'Fields can not be empty';
            }
        }
        //$this->load->view($this->loc_path . 'signup', $data);
        //$data['body_content'] = '<sign_up></sign_up>';
        $baseUrl = base_url();
        $data['body_content'] = "<sign_up></sign_up>";
        $this->load->view($this->loc_path . 'layouts/common', $data);
    }

    public function register_js() {
        $status = 'Success';
        $msg = '';
        $email = trim($this->input->post('email', TRUE));
        $user_name = trim($this->input->post('username', TRUE));
        $password = trim($this->input->post('password', TRUE));
        $confirm_password = trim($this->input->post('repassword', TRUE));
        if (!empty($email) && !empty($user_name) && !empty($password) && !empty($confirm_password)) {
            //Check Email And User
            $register_user = $this->User_model->fetch_user_by_search(array('email' => $email));
            if (empty($register_user)) {
                $user_name = $this->generate_username($user_name);
                //$register_user = $this->User_model->fetch_user_by_search(array('user_name' => $user_name));
//                    if (empty($register_user)) {
                if ($password == $confirm_password) {
                    //Create Account
                    $user = array();
                    $user['user_name'] = $user['display_name'] = $user['url'] = strtolower(str_replace(' ', '', $user_name));
                    $user['email'] = $email;
                    $user['password'] = $this->general_library->encrypt_txt($password);
                    $user['plan_id'] = '1';
                    $user['status_id'] = '3';
                    $user['platform'] = 'Streamy';
                    $user['id'] = $this->User_model->insert_user($user);
                    $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
                    $this->session->set_userdata('register-email', $user['email']);
                    $this->session->set_userdata('register-id', $user['id']);
                    $this->session->set_userdata('register-user', $user['user_name']);
                    //redirect($this->loc_url . '/register_confirm', 'location', 302);
                } else {
                    $status = 'Error';
                    $msg = 'Password and Confirm Password Should Be Equals';
                }
            } else {
                $status = 'Error';
                $msg = 'Email Already Register';
            }
        } else {
            $status = 'Error';
            $msg = 'Fields can not be empty';
        }
        echo json_encode(array('status' => $status, 'msg' => $msg));
    }

    public function register_confirm() {
        $var = $this->session->userdata;
        $email = $var['register-email'];
        $id = $var['register-id'];
        $user = $var['register-user'];
        $email_e = $this->general_library->urlsafe_b64encode($email);
        $id_e = $this->general_library->urlsafe_b64encode($id);
        $user_e = $this->general_library->urlsafe_b64encode($user);
        $url = base_url() . 'app/email_confirm/' . $email_e . '/' . $id_e . '/' . $user_e;
        $body = $this->load->view('email/email_register', array('user' => $user, 'email' => $email, 'url' => $url), true);
        //$this->general_library->send_ses($email, $email, 'LinkStream', 'noreply@link.stream', "Register on LinksStream", $body);
        //echo 'Please Check your email and confirm your email address';
        $data['body_content'] = "<confirm_email></confirm_email>";
        $this->load->view($this->loc_path . 'layouts/common', $data);
    }

    public function email_confirm($email_e, $id_e, $user_e) {
        //$data = array();
        $email = $this->general_library->urlsafe_b64decode($email_e);
        $id = $this->general_library->urlsafe_b64decode($id_e);
        $user = $this->general_library->urlsafe_b64decode($user_e);
        //Check User
        $register_user = $this->User_model->fetch_user_by_search(array('email' => $email, 'user_name' => $user, 'id' => $id));
        if (!empty($register_user)) {
            $this->user_session($register_user);
            $this->User_model->update_user($register_user['id'], array('email_confirmed' => '1'));
            $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Confirmed Email'));
            redirect($this->loc_url, 'location', 302);
        } else {
            redirect($this->loc_url . '/register', 'location', 302);
        }
    }

    public function instagram_register() {
        $url = $this->instagram_api->instagramLogin(); //get credential
        redirect($url, 'location', 302);
    }

    public function instagram_oauth() {
        //if (isset($_GET['code']) && $_GET['code'] != '') {}
        if (!empty($this->input->get('code'))) {
            $code = $this->input->get('code', TRUE);
            //print_r($code);
            //echo '<br>';
            //exit;
            $auth_response = $this->instagram_api->authorize($code);
            if (!empty($auth_response->error_type)) {
                print_r($auth_response->error_type);
                echo '<br>';
                print_r($auth_response->code);
                echo '<br>';
                print_r($auth_response->error_message);
                echo '<br>';
                exit;
            } else {
                //echo '<pre>';
                //print_r($auth_response);
                //echo '<br>';
                $access_token = $auth_response->access_token;
                //print_r($access_token);
                //echo '<br>';
                $user_id = $auth_response->user_id;
                //print_r($user_id);
                //echo '<br>';
                $instagram_user = $this->instagram_api->getUserInfo($user_id, 'id,username,account_type,media_count', $access_token);
                //$instagram_user = $this->instagram_api->getUserInfoMe('id,username', $access_token);
                //print_r($instagram_user);
                //echo '<br>';
                //$user_media = $this->instagram_api->getUserMedia('id,media_type,media_url,username,timestamp', $access_token);
                //print_r($user_media);
                //echo '<br>';
                $instagram_avatar = (!empty($instagram_user->username)) ? $this->instagram_get_photo($instagram_user->username) : '';
                //print_r($instagram_avatar);
                //echo '<br>';
                //echo '</pre>';
                //exit;
                $this->instagram_user($user_id, $access_token, $instagram_user, $instagram_avatar);
            }
            redirect($this->loc_url, 'location', 302);
        }
    }

    private function instagram_get_photo($user_name) {
        $getValues = file_get_contents('https://www.instagram.com/' . $user_name . '/?__a=1');
        $jsonObj = json_decode($getValues, TRUE);
        $photoURL = $jsonObj["graphql"]["user"]["profile_pic_url_hd"];
        //print_r($photoURL);
        return $photoURL;
    }

    private function instagram_user($user_id, $access_token, $instagram_user, $instagram_avatar) {
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
            $user['image'] = '';
            if (!empty($instagram_avatar)) {
                $content = file_get_contents($instagram_avatar);
                //
                $image_name = time() . '.png';
                // upload cropped image to server 
                $source = $this->get_temp_dir();
                file_put_contents($source . '/' . $image_name, $content);
                //SAVE S3
                $bucket = 'files.link.stream';
                $path = (ENV == 'live') ? 'Prod/' : 'Dev/';
                $dest_folder = 'Profile';
                $destination = $path . $dest_folder . '/' . $image_name;
                $s3_source = $source . '/' . $image_name;
                $this->aws_s3->s3push($s3_source, $destination, $bucket);
                //$response['file_name'] = $image_name;
                unlink($source . '/' . $image_name);
                $user['image'] = $image_name;
            }
            $user['status_id'] = '3';
            $user['id'] = $this->User_model->insert_user($user);
            $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
            $this->user_session($user);
        } else {
            $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Logged in'));
            $this->user_session($register_user);
        }
        return true;
    }

    public function google_oauth() {
        $this->output->set_content_type('application/json');
        $token = $this->input->post('id_token', true);
        if (empty($token)) {
            return $this->output->set_output(json_encode(['error' => 'Missing token']));
        }
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $token_info_json = curl_exec($ch);
            curl_close($ch);
            $token_info = json_decode($token_info_json);
        } catch (Exception $e) {
            
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
                    $bucket = 'files.link.stream';
                    $path = (ENV == 'live') ? 'Prod/' : 'Dev/';
                    $dest_folder = 'Profile';
                    $destination = $path . $dest_folder . '/' . $image_name;
                    $s3_source = $source . '/' . $image_name;
                    $this->aws_s3->s3push($s3_source, $destination, $bucket);
                    //$response['file_name'] = $image_name;
                    unlink($source . '/' . $image_name);
                    $user['image'] = $image_name;
                }
                //$user['image'] = $token_info->picture;
                $user['status_id'] = '3';
                $user['email_confirmed'] = '1';
                $user['id'] = $this->User_model->insert_user($user);
                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
                $this->user_session($user);
            } else {
                $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Logged in'));
                $this->user_session($register_user);
            }
            return $this->output->set_output(json_encode(['success' => true]));
//            return $this->output->set_output(json_encode(['success' => true, 'token' => $token_info, 'sub' => $token_info->sub]));
//            $result = $this->cs_model->get_cs_member_by_email(strtolower($token_info->email));
//            if (isset($result['is_active']) && $result['is_active'] == 1) {
//                //$this->create_auth_cookie($result);
//                return $this->output->set_output(json_encode(['success' => true]));
//            } else {
//                return $this->output->set_output(json_encode(['error' => 'Invalid eTags email']));
//            }
        } else {
            return $this->output->set_output(json_encode(['error' => 'Invalid token']));
        }
    }

    public function forgot() {
        $data = array();
        //$this->load->view($this->loc_path . 'forgot', $data);
        $data['body_content'] = '<forgot_password></forgot_password>';
        $this->load->view($this->loc_path . 'layouts/common', $data);
    }

    /* End Login & Register Section */

    /* Account Section */

    public function account() {
        $data = array();
        $this->load->view($this->loc_path . 'account', $data);
    }

    public function account_settings_2() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['user'] = $user;
            $this->load->view($this->loc_path . 'account_settings', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function account_settings() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['user'] = $user;
            $this->load->view($this->loc_path . 'account_settings_2', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function verify_account_username() {
        $user_name = $this->input->post('username', TRUE);
        $user = $this->general_library->get_cookie();
        $register_user = $this->User_model->fetch_user_by_search(array('user_name' => $user_name));
        if (empty($register_user)) {
            echo 'true';
        } elseif ($user['user_name'] == $user_name) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function verify_account_email() {
        $email = $this->input->post('email', TRUE);
        $user = $this->general_library->get_cookie();
        $register_user = $this->User_model->fetch_user_by_search(array('email' => $email));
        if (empty($register_user)) {
            echo 'true';
        } elseif ($user['email'] == $email) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    public function account_setting_update_ajax() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $user_name = $this->input->post('username', TRUE);
            $email = $this->input->post('email', TRUE);
            $this->User_model->update_user($user['id'], array('user_name' => $user_name, 'email' => $email));
            //update cookie
            $user['user_name'] = $user_name;
            $user['email'] = $email;
            $encrypted_user = $this->general_library->urlsafe_b64encode(json_encode($user));
            $this->general_library->update_cookie(serialize(array('user' => $encrypted_user)));
            //
            echo json_encode(array('status' => 'Success'));
        }
    }

    public function account_setting_complete_ajax() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $role = $this->input->post('radio', TRUE);
            $url = base_url() . $this->general_library->urlsafe_b64encode($user['user_name']);
            $this->User_model->update_user($user['id'], array('plan_id' => $role, 'status_id' => '1', 'url' => $url));
            //update cookie
            $user['plan_id'] = $role;
            $user['status_id'] = '1';
            $user['url'] = $url;
            $encrypted_user = $this->general_library->urlsafe_b64encode(json_encode($user));
            $this->general_library->update_cookie(serialize(array('user' => $encrypted_user)));
            //
            echo json_encode(array('status' => 'Success'));
        }
    }

    public function account_setting_payment_ajax() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $role = $this->input->post('pln_id', TRUE);
            $url = base_url() . $user['user_name'];
            $this->User_model->update_user($user['id'], array('plan_id' => $role, 'status_id' => '1'));
            //update cookie
            $user['plan_id'] = $role;
            $user['status_id'] = '1';
            $user['url'] = $url;
            $encrypted_user = $this->general_library->urlsafe_b64encode(json_encode($user));
            $this->general_library->update_cookie(serialize(array('user' => $encrypted_user)));
            //
            echo json_encode(array('status' => 'Success'));
        }
    }

    /* End Account Section */

    public function not_found() {
        $data = array();
        $this->load->view($this->loc_path . 'not-found', $data);
    }

    public function sound_cloud_test() {
        $url = "https://soundcloud.com/iamstarinthesky/snow-angel-feat-skele-and-shigaraki-prod-sogimura";
//$url = 'https://soundcloud.com/iamstarinthesky';
        $search_link = 'https://soundcloud.com/iamstarinthesky/if-stars-could-wish-feat-whywewish-prod-young-flaco';
//Get the JSON data of song details with embed code from SoundCloud oEmbed
        $getValues = file_get_contents('http://soundcloud.com/oembed?format=js&url=' . $url . '&iframe=true');
//        print_r($getValues);
//        echo '<br>';
//        $pos = strpos($getValues, $search_link);
//        echo '<br>';
//        if ($pos === FALSE) {
//            echo 'No exist';
//        } else {
//            echo 'Exist, Position: ' . $pos;
//        }
//        //Clean the Json to decode
//        $decodeiFrame = substr($getValues, 1, -2);
//        //json decode to convert it as an array
//        $jsonObj = json_decode($decodeiFrame);
//        //Change the height of the embed player if you want else uncomment below line
//        echo $jsonObj->html;
//        //Print the embed player to the page
//        //echo str_replace('height="4000"', 'height="2400"', $jsonObj->html);
    }

    /* Streamy */

    public function content($action = null, $type = null) {
        $data = array();
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            //$this->status_pending();
            $user = $this->general_library->get_cookie();
            if (!empty($action) && $action == 'add') {
                $data['user'] = $user;
                $data['type'] = '';
                $data['placeholder_url'] = '';
                $data['type_url'] = '';
                $data['genres'] = $this->Streamy_model->fetch_genres();
                if (!empty($type)) {
                    if ($type == 'sc') {
                        $data['type'] = '1';
                        $data['placeholder_url'] = 'https://soundcloud.com/iamstarinthesky/go-hard-prod-silo';
                        $data['type_url'] = 'SoundCloud URL';
                        $this->load->view($this->loc_path . 'content/content_add', $data);
                    } elseif ($type == 'yt') {
                        $data['type'] = '2';
                        $data['placeholder_url'] = 'https://www.youtube.com/watch?v=h_D3VFfhvs4';
                        $data['type_url'] = 'YouTube URL';
                        $this->load->view($this->loc_path . 'content/content_add', $data);
                    } elseif ($type == 'lk') {
                        $data['type'] = '3';
                        $data['placeholder_url'] = 'https://www.link.stream';
                        $data['type_url'] = 'LinkStreams URL';
                        $this->load->view($this->loc_path . 'content/linkstream', $data);
                    } elseif ($type == 'st') {
                        $data['type'] = '3';
                        $this->load->view($this->loc_path . 'content/my_streamy_add', $data);
                    } else {
                        $data['type'] = '';
                        $data['placeholder_url'] = '';
                        $data['type_url'] = '';
                        $this->load->view($this->loc_path . 'content/content_add', $data);
                    }
                } else {
                    $data['type'] = '';
                    $data['placeholder_url'] = '';
                    $data['type_url'] = '';
                    $this->load->view($this->loc_path . 'content/content_add', $data);
                }
            } else {
                $data['user'] = $user;
                $data['genres'] = $this->Streamy_model->fetch_genres();
                $data['types'] = $this->Streamy_model->fetch_types();
                $this->load->view($this->loc_path . 'content/my_content_dt', $data);
            }
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function content_old($action = null, $type = null) {
        $data = array();
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            //$this->status_pending();
            $user = $this->general_library->get_cookie();
            if (!empty($action) && $action == 'add') {
                $data['user'] = $user;
                $data['type'] = '';
                $data['placeholder_url'] = '';
                $data['type_url'] = '';
                $data['genres'] = $this->Streamy_model->fetch_genres();
                if (!empty($type)) {
                    if ($type == 'sc') {
                        $data['type'] = '1';
                        $data['placeholder_url'] = 'https://soundcloud.com/iamstarinthesky/go-hard-prod-silo';
                        $data['type_url'] = 'SoundCloud URL';
                        $this->load->view($this->loc_path . 'content/content_add', $data);
                    } elseif ($type == 'yt') {
                        $data['type'] = '2';
                        $data['placeholder_url'] = 'https://www.youtube.com/watch?v=h_D3VFfhvs4';
                        $data['type_url'] = 'YouTube URL';
                        $this->load->view($this->loc_path . 'content/content_add', $data);
                    } elseif ($type == 'lk') {
                        $data['type'] = '3';
                        $data['placeholder_url'] = 'https://www.link.stream';
                        $data['type_url'] = 'LinkStreams URL';
                        $this->load->view($this->loc_path . 'content/linkstream', $data);
                    } elseif ($type == 'st') {
                        $data['type'] = '3';
                        $this->load->view($this->loc_path . 'content/my_streamy_add', $data);
                    } else {
                        $data['type'] = '';
                        $data['placeholder_url'] = '';
                        $data['type_url'] = '';
                        $this->load->view($this->loc_path . 'content/content_add', $data);
                    }
                } else {
                    $data['type'] = '';
                    $data['placeholder_url'] = '';
                    $data['type_url'] = '';
                    $this->load->view($this->loc_path . 'content/content_add', $data);
                }
            } else {
                //Soundcloud
                $search = array(
                    'user' => $user['id'],
                    'status_id' => '1',
                    'type' => '1'
                );
                $streamys = $this->Streamy_model->fetch_streamys_by_search($search, $this->limit, 0);
//              $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
                $stramy_list = array();
                $i = 0;
                foreach ($streamys as $streamy) {
                    $i++;
                    $stramy_list[] = $this->streamy_desc($streamy, $i);
                }
                $data['soundCloud'] = $stramy_list;
                //Youtube
                $search = array(
                    'user' => $user['id'],
                    'status_id' => '1',
                    'type' => '2'
                );
                $streamys = $this->Streamy_model->fetch_streamys_by_search($search, $this->limit, 0);
//              $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
                $stramy_list = array();
                $i = 0;
                foreach ($streamys as $streamy) {
                    $i++;
                    $stramy_list[] = $this->streamy_desc($streamy, $i);
                }
                $data['youTube'] = $stramy_list;
                //LinkStreams
                $search = array(
                    'user' => $user['id'],
                    'status_id' => '1',
                    'type' => '3'
                );
                $streamys = $this->Streamy_model->fetch_streamys_by_search($search, $this->limit, 0);
//              $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
                $stramy_list = array();
                $i = 0;
                foreach ($streamys as $streamy) {
                    $i++;
                    $stramy_list[] = $this->streamy_desc($streamy, $i);
                }
                $data['linkStreams'] = $stramy_list;
                //mystream
                $search = array(
                    'user' => $user['id'],
                    'status_id' => '1',
                    'type' => '4'
                );
                $streamys = $this->Streamy_model->fetch_streamys_by_search($search, $this->limit, 0);
//              $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
                $stramy_list = array();
                $i = 0;
                foreach ($streamys as $streamy) {
                    $i++;
                    $stramy_list[] = $this->streamy_desc($streamy, $i);
                }
                $data['mystream'] = $stramy_list;
                $data['user'] = $user;
                $this->load->view($this->loc_path . 'content/my_content', $data);
            }
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function my_content_add_2() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['user'] = $user;
            $this->load->view($this->loc_path . 'content/my_content_add', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function my_content_add($type = null) {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['user'] = $user;
            $data['type'] = (!empty($type)) ? $type : '';
            $data['placeholder_url'] = ($data['type'] == '1') ? 'https://soundcloud.com/iamstarinthesky/go-hard-prod-silo' : (($data['type'] == '2') ? 'https://www.youtube.com/watch?v=h_D3VFfhvs4' : 'https://www.link.stream');
            $data['type_url'] = ($data['type'] == '1') ? 'SoundCloud URL' : (($data['type'] == '2') ? 'YouTube URL' : 'URL');
            $data['genres'] = $this->Streamy_model->fetch_genres();
            $this->load->view($this->loc_path . 'content/my_content_add_2', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function streamy_content_view() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            //$user = $this->general_library->get_cookie();
            $type = $this->input->post('radio', TRUE);
            $streamy_url = $this->input->post('streamy_url', TRUE);
            $embed_url = $this->embed_url($streamy_url, $type);
            echo $embed_url;
        }
    }

    public function streamy_content_add() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $type = $this->input->post('radio', TRUE);
            $streamy_url = (!empty($this->input->post('streamy_url'))) ? $this->input->post('streamy_url', TRUE) : '';
            $priority = $this->input->post('priority', TRUE);
            $visibility = $this->input->post('visibility', TRUE);
            $name = $this->input->post('song_name', TRUE);
            $date = (!empty($this->input->post('date'))) ? $this->input->post('date', TRUE) : date('Y-m-d');
            $genre = $this->input->post('genre', TRUE);
            $data = array(
                'user_id' => $user['id'],
                'url' => $streamy_url,
                'type_id' => $type,
                'public' => $visibility,
                'status_id' => '1',
                'priority_id' => $priority,
                'publish_at' => date('Y-m-d 00:00:00', strtotime($date)),
                'name' => $name,
                'genre_id' => $genre
            );
            if ($type == '4') {
                //FILE
                if (!empty($_FILES['input_b1']['name'])) {
                    $upload = $this->s3_upload('input_b1', 'Media');
                    if ($upload['status']) {
                        $data['url'] = $upload['file_name'];
                    } else {
                        
                    }
                }
                //FILE
                if (!empty($_FILES['input_b2']['name'])) {
                    $upload = $this->s3_upload('input_b2', 'Media');
                    if ($upload['status']) {
                        $data['coverart'] = $upload['file_name'];
                    } else {
                        
                    }
                }
            }
            $this->Streamy_model->insert_streamy($data);
            echo json_encode(array('status' => 'Success'));
        }
    }

    private function s3_upload($field_name, $dest_folder = null) {
        $response = array('status' => true, 'msg' => '', 'file_name' => '');
        $this->load->library('upload');
        $source = $this->get_temp_dir();
        $config['upload_path'] = $source . '/';
        $config['allowed_types'] = '*';
        $config['max_size'] = 102400;
        $config['encrypt_name'] = TRUE;
        //$config['file_name'] = $filename;
        //unlink($source .'/'.$config['file_name']);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($field_name)) {
            $error = array('error' => $this->upload->display_errors());
            $response['status'] = false;
            $response['msg'] = $error['error'];
        } else {
            $file_uploades = $this->upload->data();
            //SAVE S3
            $bucket = 'files.link.stream';
            $path = (ENV == 'live') ? 'Prod/' : 'Dev/';
            $destination = $path . $dest_folder . '/' . $file_uploades['file_name'];
            $s3_source = $source . '/' . $file_uploades['file_name'];
            $this->aws_s3->s3push($s3_source, $destination, $bucket);
            $response['file_name'] = $file_uploades['file_name'];
            unlink($source . '/' . $file_uploades['file_name']);
            $response['status'] = true;
        }
        return $response;
    }

    public function my_content($type = null) {
        $data = array();
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            //$this->status_pending();
            $user = $this->general_library->get_cookie();
//            $search = array(
//                'user' => $user['id'],
//                'status' => '1',
//                'type' => (!empty($type) && $type == 'link') ? '3' : 'Streamy'
//            );
//            $streamys = $this->Streamy_model->fetch_streamys_by_search($search, 0, 0);
//            $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
//            $stramy_list = array();
//            foreach ($streamys as $streamy) {
//                $stramy_list[] = $this->streamy_desc($streamy);
////                $streamy['embeed'] = $this->embed_url($streamy['url'], $streamy['type']);
////                $streamy['type_desc'] = ($streamy['type'] == '1') ? 'SoundCloud' : (($streamy['type'] == '2') ? 'YouTube' : 'LinkStreams');
////                $streamy['public_desc'] = ($streamy['public'] == '1') ? 'Public' : 'Private';
////                $streamy['priority_desc'] = ($streamy['priority'] == '1') ? 'High' : (($streamy['priority'] == '2') ? 'Normal' : 'Low');
////                $streamy['publish_at'] = date('m/d/Y', strtotime($streamy['publish_at']));
////                $stramy_list[] = $streamy;
//            }
//            $data['streamys'] = $stramy_list;
//            $data['streamys_view'] = $this->load->view($this->loc_path . 'content/my_content_list', $data, true);
//            $data['streamys_nav'] = $this->streamy_nav($streamys_count, 1, $this->limit);
            //Soundcloud
            $search = array(
                'user' => $user['id'],
                'status_id' => '1',
                'type' => '1'
            );
            $streamys = $this->Streamy_model->fetch_streamys_by_search($search, 0, 0);
//            $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
            $stramy_list = array();
            foreach ($streamys as $streamy) {
                $stramy_list[] = $this->streamy_desc($streamy);
            }
            $data['soundCloud'] = $stramy_list;
            //Youtube
            $search = array(
                'user' => $user['id'],
                'status_id' => '1',
                'type' => '2'
            );
            $streamys = $this->Streamy_model->fetch_streamys_by_search($search, 0, 0);
//            $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
            $stramy_list = array();
            foreach ($streamys as $streamy) {
                $stramy_list[] = $this->streamy_desc($streamy);
            }
            $data['youTube'] = $stramy_list;
            //LinkStreams
            $search = array(
                'user' => $user['id'],
                'status_id' => '1',
                'type' => '3'
            );
            $streamys = $this->Streamy_model->fetch_streamys_by_search($search, 0, 0);
//            $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
            $stramy_list = array();
            foreach ($streamys as $streamy) {
                $stramy_list[] = $this->streamy_desc($streamy);
            }
            $data['linkStreams'] = $stramy_list;
            //mystream
            $search = array(
                'user' => $user['id'],
                'status_id' => '1',
                'type' => '4'
            );
            $streamys = $this->Streamy_model->fetch_streamys_by_search($search, 0, 0);
//            $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
            $stramy_list = array();
            foreach ($streamys as $streamy) {
                $stramy_list[] = $this->streamy_desc($streamy);
            }
            $data['mystream'] = $stramy_list;



            $data['user'] = $user;
            $this->load->view($this->loc_path . 'content/my_content', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function my_content_ajax() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            //$this->status_pending();
            $user = $this->general_library->get_cookie();
            $aaData = array();
            $name = $this->input->post('name', TRUE);
            $type = $this->input->post('type', TRUE);
            $genre = $this->input->post('genre', TRUE);
            $limit = $this->input->post('iDisplayLength');
            $offset = $this->input->post('iDisplayStart');
            $isort_col = $this->input->post('iSortCol_0');
            $sort_dir = $this->input->post('sSortDir_0');
            $sSearch = $this->input->post('sSearch');
            $array_field = array("id", "name", "type_name", "created_at");
            $sort_col = $array_field[$isort_col];
            $search = array(
                'user' => $user['id'],
                'status_id' => '1',
                'sort_col' => $sort_col,
                'sort_dir' => $sort_dir,
                'name' => $name,
                'type' => $type,
                'genre' => $genre
            );
            $streamys = $this->Streamy_model->fetch_streamys_by_search($search, $limit, $offset);
            $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search($search);
            //CREATING DATA TO SHOW
            if ($streamys_count > 0) {
                foreach ($streamys as $row) {
                    //$customerName = ($state == 'CA' || $state == 'MD') ? $row['shipName'] : $row['firstname'] . ' ' . $row['lastname'];
                    //$row['phone'] = str_replace(array("(", ")", "-", " "), "", $row['phone']);
                    $aaData[] = array(
                        '<button class="btn btn-outline-secondary m-1 js-edit" id="' . $row['id'] . '"><i class="fa fa-search-plus"></i></button>',
                        $row['name'],
                        $row['type_name'],
                        date('m/d/Y h:m:s', strtotime($row['created_at'])),
                        '<button class="btn btn-outline-danger m-1 js-delete" id="' . $row['id'] . '" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>',
                        $row
                    );
                }
            }
            echo json_encode(array('aaData' => $aaData, "iTotalRecords" => $streamys_count, "iTotalDisplayRecords" => $streamys_count));
        }
    }

    private function streamy_nav($streamys_count, $streamy_page, $limit) {
        $pagLink = '';
        if ($streamys_count > 0) {
            // Number of pages required. 
            $total_pages = ceil($streamys_count / $limit);
            $pagLink .= '<li class="page-item ' . (($total_pages == '1' || $streamy_page == '1') ? "disabled" : "") . '"><a class="page-link js-nav_button" href="#" tabindex="-1" id="' . ($streamy_page - 1) . '">Previous</a></li>';
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $streamy_page) {
                    $pagLink .= '<li class="page-item active"><a class="page-link" js-nav_button href="#" id="' . $i . '">' . $i . '</a></li>';
                } else {
                    $pagLink .= '<li class="page-item"><a class="page-link js-nav_button" href="#" id="' . $i . '">' . $i . '</a></li>';
                }
            }
            $pagLink .= '<li class="page-item ' . (($total_pages == '1' || $streamy_page == $total_pages) ? "disabled" : "") . ' "><a class="page-link js-nav_button" href="#" id="' . ($streamy_page + 1) . '">Next</a></li>';
        }
        return $pagLink;
    }

    public function streamy_remove_old() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $data = array();
            $user = $this->general_library->get_cookie();
            $id = $this->input->post('id', TRUE);
            $this->Streamy_model->update_streamy($id, array('status_id' => '3'));
            $streamys = $this->Streamy_model->fetch_streamys_by_search(array('user' => $user['id'], 'status_id' => '1'), $this->limit, 0);
            $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search(array('user' => $user['id'], 'status_id' => '1'));
            $stramy_list = array();
            foreach ($streamys as $streamy) {
                $stramy_list[] = $this->streamy_desc($streamy);
//                $streamy['embeed'] = $this->embed_url($streamy['url'], $streamy['type']);
//                $streamy['type_desc'] = ($streamy['type'] == '1') ? 'SoundCloud' : (($streamy['type'] == '2') ? 'YouTube' : 'LinkStreams');
//                $streamy['public_desc'] = ($streamy['public'] == '1') ? 'Public' : 'Private';
//                $streamy['priority_desc'] = ($streamy['priority'] == '1') ? 'High' : (($streamy['priority'] == '2') ? 'Normal' : 'Low');
//                $streamy['publish_at'] = date('m/d/Y', strtotime($streamy['publish_at']));
//                $stramy_list[] = $streamy;
            }
            $data['streamys'] = $stramy_list;
            $data['streamys_view'] = $this->load->view($this->loc_path . 'content/my_content_list', $data, true);
            $data['streamys_nav'] = $this->streamy_nav($streamys_count, 1, $this->limit);
            echo json_encode(array('status' => 'Success', 'streamys_view' => $data['streamys_view'], 'streamys_nav' => $data['streamys_nav']));
        }
    }

    public function streamy_remove() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $data = array();
            $user = $this->general_library->get_cookie();
            $id = $this->input->post('id', TRUE);
            $this->Streamy_model->update_streamy($id, array('status_id' => '3'));
//            $streamys = $this->Streamy_model->fetch_streamys_by_search(array('user' => $user['id'], 'status' => '1'), $this->limit, 0);
//            $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search(array('user' => $user['id'], 'status' => '1'));
//            $stramy_list = array();
//            foreach ($streamys as $streamy) {
//                $stramy_list[] = $this->streamy_desc($streamy);
////                $streamy['embeed'] = $this->embed_url($streamy['url'], $streamy['type']);
////                $streamy['type_desc'] = ($streamy['type'] == '1') ? 'SoundCloud' : (($streamy['type'] == '2') ? 'YouTube' : 'LinkStreams');
////                $streamy['public_desc'] = ($streamy['public'] == '1') ? 'Public' : 'Private';
////                $streamy['priority_desc'] = ($streamy['priority'] == '1') ? 'High' : (($streamy['priority'] == '2') ? 'Normal' : 'Low');
////                $streamy['publish_at'] = date('m/d/Y', strtotime($streamy['publish_at']));
////                $stramy_list[] = $streamy;
//            }
//            $data['streamys'] = $stramy_list;
//            $data['streamys_view'] = $this->load->view($this->loc_path . 'content/my_content_list', $data, true);
//            $data['streamys_nav'] = $this->streamy_nav($streamys_count, 1, $this->limit);
            echo json_encode(array('status' => 'Success'));
        }
    }

    public function streamy_nav_action() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $data = array();
            $user = $this->general_library->get_cookie();
            $id = $this->input->post('id', TRUE);
            //$this->Streamy_model->update_streamy($id, array('status' => '3'));
            $offset = ($id == 1) ? 0 : (($id - 1) * $this->limit);
            $streamys = $this->Streamy_model->fetch_streamys_by_search(array('user' => $user['id'], 'status_id' => '1'), $this->limit, $offset);
            $streamys_count = $this->Streamy_model->fetch_streamys_count_by_search(array('user' => $user['id'], 'status_id' => '1'));
            $stramy_list = array();
            foreach ($streamys as $streamy) {
                $stramy_list[] = $this->streamy_desc($streamy);
//                $streamy['embeed'] = $this->embed_url($streamy['url'], $streamy['type']);
//                $streamy['type_desc'] = ($streamy['type'] == '1') ? 'SoundCloud' : (($streamy['type'] == '2') ? 'YouTube' : 'LinkStreams');
//                $streamy['public_desc'] = ($streamy['public'] == '1') ? 'Public' : 'Private';
//                $streamy['priority_desc'] = ($streamy['priority'] == '1') ? 'High' : (($streamy['priority'] == '2') ? 'Normal' : 'Low');
//                $streamy['publish_at'] = date('m/d/Y', strtotime($streamy['publish_at']));
//                $stramy_list[] = $streamy;
            }
            $data['streamys'] = $stramy_list;
            $data['streamys_view'] = $this->load->view($this->loc_path . 'content/my_content_list', $data, true);
            $data['streamys_nav'] = $this->streamy_nav($streamys_count, $id, $this->limit);
            echo json_encode(array('status' => 'Success', 'streamys_view' => $data['streamys_view'], 'streamys_nav' => $data['streamys_nav']));
        }
    }

    public function streamy_view() {
        $id = $this->input->post('id', TRUE);
        $streamy = $this->Streamy_model->fetch_streamy_by_id($id);
        $streamy = $this->streamy_desc($streamy);
        $user = $this->general_library->get_cookie();
//        $streamy['type_desc'] = ($streamy['type'] == '1') ? 'SoundCloud' : (($streamy['type'] == '2') ? 'YouTube' : 'LinkStreams');
//        $streamy['public_desc'] = ($streamy['public'] == '1') ? 'Public' : 'Private';
//        $streamy['priority_desc'] = ($streamy['priority'] == '1') ? 'High' : (($streamy['priority'] == '2') ? 'Normal' : 'Low');
//        $streamy['publish_at'] = date('m/d/Y', strtotime($streamy['publish_at']));
        $streamy['genres'] = $this->Streamy_model->fetch_genres();
        $streamy['user'] = $user;
        $this->load->view($this->loc_path . 'content/my_content_modal', $streamy);
    }

    private function streamy_desc($streamy, $i = 0) {
        //$streamy['embeed'] = ($i <= 6) ? $this->embed_url($streamy['url'], $streamy['type']) : '';
        $streamy['embeed'] = $this->embed_url($streamy['url'], $streamy['type_id']);
        if ($streamy['type_id'] == '1') {
            $streamy['type_desc'] = 'SoundCloud';
            $streamy['type_icon'] = '<i class="i-Soundcloud"></i>';
        } elseif ($streamy['type_id'] == '2') {
            $streamy['type_desc'] = 'YouTube';
            $streamy['type_icon'] = '<i class="i-Youtube"></i>';
        } elseif ($streamy['type_id'] == '3') {
            $streamy['type_desc'] = 'LinkStreams';
            $streamy['type_icon'] = '<i class="i-Link"></i>';
        } elseif ($streamy['type_id'] == '4') {
            $streamy['type_desc'] = 'Streamy';
            $streamy['type_icon'] = '<i class="i-Play-Music"></i>';
        } elseif ($streamy['type_id'] == '5') {
            $streamy['type_desc'] = 'TikTok';
            $streamy['type_icon'] = '<i class="i-Play-Music"></i>';
        }




        //$streamy['type_desc'] = ($streamy['type'] == '1') ? 'SoundCloud' : (($streamy['type'] == '2') ? 'YouTube' : 'LinkStreams');
        //$streamy['type_icon'] = ($streamy['type'] == '1') ? ' <i class="i-Soundcloud"></i>' : (($streamy['type'] == '2') ? '<i class="i-Youtube"></i>' : '<i class="i-Link"></i>');
        $streamy['public_desc'] = ($streamy['public'] == '1') ? 'Public' : (($streamy['public'] == '2') ? 'Private' : 'Scheduled');
        $streamy['priority_desc'] = ($streamy['priority_id'] == '1') ? 'Spotlight' : 'Normal';
        //$streamy['publish_at'] = ($streamy['public'] == '3') ? date('m/d/Y', strtotime($streamy['publish_at'])) : '';
        $streamy['publish_at'] = date('m/d/Y', strtotime($streamy['publish_at']));
        $genre = $this->Streamy_model->fetch_genre_by_id($streamy['genre_id']);
        $streamy['genre_desc'] = $genre['genre'];
        return $streamy;
    }

    public function streamy() {
        $data = array();
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data['user_name'] = $user['user_name'];
            if ($this->input->post()) {
                $url = trim($this->input->post('url', TRUE));
                $type = trim($this->input->post('service_type', TRUE));
                echo $url;
                echo '<br>';
                echo $type;
                echo '<br>';
                $embed_url = $this->embed_url($url, $type);
                echo $embed_url;
                echo '<br>';
            }


            $this->load->view($this->loc_path . 'streamy_example', $data);
        } else {
            //$this->load->view('welcome_message', $data);
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function confirm_account() {
        echo 'AAAA';
    }

    public function audio_content($name = null) {
        $path = (ENV == 'live') ? 'Prod/Audio/' : 'Dev/Audio/';
        $file = 'https://s3.us-east-2.amazonaws.com/files.link.stream/' . $path . $name;
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header('Content-type: audio/mpeg3;audio/x-mpeg-3;video/mpeg;video/x-mpeg;text/xml');
        header('Content-Disposition: inline; filename="' . $name . '"');
        echo $file;
        //return $file;
    }

    private function embed_url($url, $type) {
        $embed_url = $url;
        if ($type == '1') {
            //SoundCloud
            $embed_url = '<iframe width="425" height="315" scrolling="no" frameborder="no" allow="autoplay" '
                    . 'src="https://w.soundcloud.com/player/?' . ''
                    . 'url=' . $url
                    . '&color=%23ff5500&auto_play=false&hide_related=false&show_comments=false&show_user=false&show_reposts=false&show_teaser=true&visual=true&show_artwork=false">'
                    . '</iframe>';
        } elseif ($type == '2') {
            //YouTube
            preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);
            $youtube_id = (!empty($matches[1])) ? $matches[1] : '';
            $embed_url = '<iframe width="420" height="315" '
                    . 'src="https://www.youtube.com/embed/' . $youtube_id . '"> '
                    . 'frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen '
                    . '</iframe>';
        } elseif ($type == '3') {
            //LinkStreams
            $embed_url = '<div class="row"> <div class="col-md-12 p-10"><a href="' . $url . '" class="btn btn-success" target="_blank">View</a></div></div>';
        } elseif ($type == '4') {
            //Streamy
            $path = (ENV == 'live') ? 'Prod/Audio/' : 'Dev/Audio/';
            $file = 'https://s3.us-east-2.amazonaws.com/files.link.stream/' . $path . $url;
            //$file = base_url() . 'app/audio_content/' . $url;
            $embed_url_old = '<audio id="myAudio">
  <source src="' . $file . '" type="audio/ogg">
  <source src="' . $file . '" type="audio/mpeg">
  Your browser does not support the audio element.
</audio>
<div class="row" style="padding: 10px;">
                <div class="col-5">
                 <button class="btn btn-outline-success m-1" onclick="playAudio()" type="button">Play Audio</button>

                </div>
                <div class="col-2">
                </div>
                <div class="col-5">
                  <button class="btn btn-outline-success m-1" onclick="pauseAudio()" type="button">Pause Audio</button>
                </div>
            </div>';
//            $embed_url = '           
//<figure>
//    <figcaption>Listen to the ' . $url . ':</figcaption>
//    <audio
//        controls
//        src=" ' . $file . '">
//            Your browser does not support the
//            <code>audio</code> element.
//    </audio>
//</figure>
//
//';


            $embed_url = '           
<figure>
   
    <audio
        controls style="width: 100%;"
        src="' . $file . '">
            Your browser does not support the
            <code>audio</code> element.
    </audio>
</figure>

';
        } elseif ($type == '5') {
            //Tik Tok
            $tiktok_url = "https://www.tiktok.com/oembed?url=" . $url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tiktok_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            $json = curl_exec($ch);
            if (!$json) {
                $embed_url = curl_error($ch);
            }
            curl_close($ch);
            $tik_tok = json_decode($json);
            $embed_url = (!empty($tik_tok->html)) ? $tik_tok->html : 'Error';
        } elseif ($type == '6') {
            //Spotify
            //https://open.spotify.com/track/7mkRzIiioITA6ETM7QQ1d8
            preg_match('/[\\spotify.com\\track\\][a-zA-Z0-9]{22}/', $url, $matches);
            $spotify_id = (!empty($matches[0])) ? $matches[0] : '';
            $embed_url = '<iframe src="https://open.spotify.com/embed/track/' . $spotify_id . '" width="300" height="380" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>';
        }
        return $embed_url;
    }

    public function test_url() {
//        $url = 'https://open.spotify.com/embed/track/7mkRzIiioITA6ETM7QQ1d8';
//        echo 'URL: ' . $url;
//        echo'<br>';
//        if (preg_match('/spotify:track:([a-zA-Z0-9]{22})/', $url, $matches)) {
//            $spotifyId = $re[1];
//        }
//        echo '<pre>';
//        print_r($matches);
//        echo '</pre>';
//        //$youtube_id = (!empty($matches[1])) ? $matches[1] : '';

        $url = "spotify:track:5xioIP2HexKl3QsI8JDlG8";
        //$url = 'https://open.spotify.com/track/7mkRzIiioITA6ETM7QQ1d8';
        echo 'URL: ' . $url;
        echo'<br>';
        if (preg_match('/spotify:track:([a-zA-Z0-9]{22})/', $url, $matches)) {
            $spotifyId = $matches[1];
            echo 'ID: ' . $spotifyId;
            echo'<br>';
        }

        $url = "spotify:track:5xioIP2HexKl3QsI8JDlG8";
        //$url = 'https://open.spotify.com/track/7mkRzIiioITA6ETM7QQ1d8';
        echo 'URL: ' . $url;
        echo'<br>';
        if (preg_match('/(?<=spotify:track:)[a-zA-Z0-9]{22}/', $url, $matches)) {
            $id = $matches[0];
            echo 'ID: ' . $id;
            echo'<br>';
        }



        $url = 'https://open.spotify.com/track/7mkRzIiioITA6ETM7QQ1d8';
        //$url = "spotify:track:5xioIP2HexKl3QsI8JDlG8";
        //$url='https://open.spotify.com/album/4RuzGKLG99XctuBMBkFFOC';
        echo 'URL: ' . $url;
        echo'<br>';
        //preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);
        preg_match('/[\\spotify.com\\track\\][a-zA-Z0-9]{22}/', $url, $matches1);
//        if (preg_match('/track([a-zA-Z0-9]{22})/', $url, $matches)) {
//            $id = $matches[0];
//            echo 'ID: ' . $id;
//            echo'<br>';
//        }
        echo '<pre>';
        print_r($matches1);
        echo '</pre>';
    }

    public function streamy_update() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $id = $this->input->post('id', TRUE);
            $streamy = $this->Streamy_model->fetch_streamy_by_id($id);
            $priority = $this->input->post('priority', TRUE);
            $visibility = $this->input->post('visibility', TRUE);
            $name = $this->input->post('song_name', TRUE);
            $date = $this->input->post('date', TRUE);
            $genre = $this->input->post('genre', TRUE);
            $streamy['public'] = $visibility;
            $streamy['priority_id'] = $priority;
            $streamy['publish_at'] = date('Y-m-d 00:00:00', strtotime($date));
            $streamy['name'] = $name;
            $streamy['genre_id'] = $genre;
            $this->Streamy_model->update_streamy($id, $streamy);
            $streamy = $this->streamy_desc($streamy);
            echo json_encode(array('status' => 'Success', 'streamy' => $streamy));
            //echo json_encode(array('status' => 'Success', 'streamys_view' => $data['streamys_view'], 'streamys_nav' => $data['streamys_nav']));
        }
    }

    public function my_linkstream_add() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['user'] = $user;
            $data['type'] = '3';
            $data['placeholder_url'] = ($data['type'] == '1') ? 'https://soundcloud.com/iamstarinthesky/go-hard-prod-silo' : (($data['type'] == '2') ? 'https://www.youtube.com/watch?v=h_D3VFfhvs4' : 'https://www.link.stream');
            $data['type_url'] = ($data['type'] == '1') ? 'SoundCloud URL' : (($data['type'] == '2') ? 'YouTube URL' : 'URL');
            $data['genres'] = $this->Streamy_model->fetch_genres();
            $this->load->view($this->loc_path . 'content/my_linkstream_add', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function my_streamy_add() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['user'] = $user;
            $data['type'] = '4';
            $data['placeholder_url'] = ($data['type'] == '1') ? 'https://soundcloud.com/iamstarinthesky/go-hard-prod-silo' : (($data['type'] == '2') ? 'https://www.youtube.com/watch?v=h_D3VFfhvs4' : 'https://www.link.stream');
            $data['type_url'] = ($data['type'] == '1') ? 'SoundCloud URL' : (($data['type'] == '2') ? 'YouTube URL' : 'URL');
            $data['genres'] = $this->Streamy_model->fetch_genres();
            $this->load->view($this->loc_path . 'content/my_streamy_add', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    //NEW
    public function video() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['user'] = $user;
            $data['type'] = '';
            $data['placeholder_url'] = '';
            $data['type_url'] = '';
            $data['genres'] = $this->Streamy_model->fetch_genres();
            $this->load->view($this->loc_path . 'content/video', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function audio() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['user'] = $user;
            $data['type'] = '';
            $data['placeholder_url'] = '';
            $data['type_url'] = '';
            $data['genres'] = $this->Streamy_model->fetch_genres();
            $this->load->view($this->loc_path . 'content/audio', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function linkstream() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['user'] = $user;
            $data['type'] = '3';
            $data['placeholder_url'] = 'https://www.link.stream';
            $data['type_url'] = 'URL';
            $data['genres'] = $this->Streamy_model->fetch_genres();
            $this->load->view($this->loc_path . 'content/linkstream', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function stream() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $data = array();
            $data['title'] = 'Add Content';
            $data['user'] = $user;
            $data['type'] = '';
            $data['placeholder_url'] = '';
            $data['type_url'] = '';
            $data['genres'] = $this->Streamy_model->fetch_genres();
            //$this->load->view($this->loc_path . 'content/stream', $data);
            //$data_config = json_encode(array('HTTP_ASSETS' => HTTP_ASSETS, 'base_url' => base_url()));
            $data['title'] = 'Tracks';
            $data['body_content'] = "<tracks></tracks>";
            //$data['body_content'] = "<tracks data_config={$data_config}></tracks>";
            //$data['body_content'] = '<tracks ></tracks>';
            $this->load->view($this->loc_path . 'layouts/layout', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    //
//    public function customize() {
//        if ($this->input->cookie($this->general_library->ses_name) != '') {
//            $user = $this->general_library->get_cookie();
//            $register_user = $this->User_model->fetch_user_by_search(array('id' => $user['id']));
//            $data = array();
//            $data['user'] = $register_user;
//            $data['order'] = $this->Streamy_model->fetch_content_order($register_user['id']);
//            $this->load->view($this->loc_path . 'customize', $data);
//        } else {
//            redirect($this->loc_url . '/login', 'location', 302);
//        }
//    }

    public function get_banner() {
        $user = $this->general_library->get_cookie();
        $user_id = $user['id'];
        $register_user = $this->User_model->fetch_user_by_search(array('id' => $user_id));
        if (!empty($register_user['banner'])) {
            $bucket = $this->bucket;
            $path = (ENV == 'live') ? 'Prod/Profile' : 'Dev/Profile';
            $s3_data = $this->aws_s3->s3_read($bucket, $path, $register_user['banner']);
            if (!empty($s3_data)) {
                $data = $s3_data;
            } else {
                $data = file_get_contents(HTTP_ASSETS . 'dist-assets/images/photo-wide-4.jpg');
            }
        } else {
            $data = file_get_contents(HTTP_ASSETS . 'dist-assets/images/photo-wide-4.jpg');
        }
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header('Content-type: image/jpeg');
        header('Content-Disposition: inline; filename="' . time() . '.jpg' . '"');
        echo $data;
    }

    public function get_avatar() {
        $user = $this->general_library->get_cookie();
        $register_user = $this->User_model->fetch_user_by_search(array('id' => $user['id']));
        if (!empty($register_user['image'])) {
            $bucket = $this->bucket;
            $path = (ENV == 'live') ? 'Prod/Profile' : 'Dev/Profile';
            $s3_data = $this->aws_s3->s3_read($bucket, $path, $register_user['image']);
            if (!empty($s3_data)) {
                $data = $s3_data;
            } else {
                $data = file_get_contents(HTTP_ASSETS . 'dist-assets/images/faces/1.jpg');
            }
        } else {
            $data = file_get_contents(HTTP_ASSETS . 'dist-assets/images/faces/1.jpg');
        }
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header('Content-type: image/jpeg');
        header('Content-Disposition: inline; filename="' . time() . '.jpg' . '"');
        echo $data;
    }

    public function banner_upload_ajax() {
        $upload = $this->s3_upload('file_photo2', 'Profile');
        $user = $this->general_library->get_cookie();
        $data = array();
        $data['banner'] = $upload['file_name'];
        $this->User_model->update_user($user['id'], $data); //banner_example.jpg
        echo json_encode(array('status' => 'Success', 'upload' => $upload['file_name']));
    }

    public function avatar_upload_ajax() {
        //$response = array('status' => true, 'msg' => '', 'file_name' => '');
        $croped_image = $this->input->post('image');
        list($type, $croped_image) = explode(';', $croped_image);
        list(, $croped_image) = explode(',', $croped_image);
        $croped_image = base64_decode($croped_image);
        $image_name = time() . '.png';
        // upload cropped image to server 
        $source = $this->get_temp_dir();
        file_put_contents($source . '/' . $image_name, $croped_image);
        //SAVE S3
        $bucket = 'files.link.stream';
        $path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $dest_folder = 'Profile';
        $destination = $path . $dest_folder . '/' . $image_name;
        $s3_source = $source . '/' . $image_name;
        $this->aws_s3->s3push($s3_source, $destination, $bucket);
        //$response['file_name'] = $image_name;
        unlink($source . '/' . $image_name);
        $user = $this->general_library->get_cookie();
        $data = array();
        $data['image'] = $image_name;
        $this->User_model->update_user($user['id'], $data); //banner_example.jpg
        echo json_encode(array('status' => 'Success', 'upload' => $image_name));
    }

//<script>
//  window.fbAsyncInit = function() {
//    FB.init({
//      appId      : '{your-app-id}',
//      cookie     : true,
//      xfbml      : true,
//      version    : '{api-version}'
//    });
//      
//    FB.AppEvents.logPageView();   
//      
//  };
//
//  (function(d, s, id){
//     var js, fjs = d.getElementsByTagName(s)[0];
//     if (d.getElementById(id)) {return;}
//     js = d.createElement(s); js.id = id;
//     js.src = "https://connect.facebook.net/en_US/sdk.js";
//     fjs.parentNode.insertBefore(js, fjs);
//   }(document, 'script', 'facebook-jssdk'));
//
//FB.getLoginStatus(function(response) {
//    statusChangeCallback(response);
//});
//{
//    status: 'connected',
//    authResponse: {
//        accessToken: '...',
//        expiresIn:'...',
//        signedRequest:'...',
//        userID:'...'
//    }
//}
//<fb:login-button 
//  scope="public_profile,email"
//  onlogin="checkLoginState();">
//</fb:login-button>
//    
//    
//function checkLoginState() {
//  FB.getLoginStatus(function(response) {
//    statusChangeCallback(response);
//  });
//}

    public function view() {
        $data = array();
        $this->load->view($this->loc_path . 'signin', $data);
    }

    public function template() {
        $data = array();
        $this->load->view($this->loc_path . 'streamy_blank', $data);
    }

    /* S3 */

    public function s3_push() {
        $temp_dir = $this->get_temp_dir();
        //echo $temp_dir;
        $source = "tmp/test.png";
        $destination = 'test.png';
        $bucket = 'files.link.stream';
        $this->aws_s3->s3push($source, $destination, $bucket);
    }

    public function fetch_list() {
        $bucket = 'files.link.stream';
        $list = $this->aws_s3->fetch_list($bucket);
        print_r($list);
//         $pdf_file = $this->aws_s3->read_file('test.png', $bucket);
//         print_r($pdf_file);
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

    public function early_access_sms() {
        $email = $this->input->post('email', TRUE);
        $phone = $this->input->post('phone', TRUE);
        if (!empty($email)) {
            $data = array();
            $body = $this->load->view('email/email_coming_soon', $data, true);
            $this->general_library->send_ses($email, $email, 'Streamy', 'noreply@link.stream', "You're In! Free Early Access Confirmed", $body);
        }
        if (!empty($phone)) {
            $this->load->library('Aws_pinpoint');
            $this->aws_pinpoint->send($phone, "Welcome! Let's bring social media and streaming music together in 2020. Thanks for registering. Stay tuned in, we will notify you as soon as you can start your stream! Reply “STOP” to cancel this reminder.");
        }
        $this->Streamy_model->insert_early_access(array('email' => $email, 'phone' => $phone));
        echo json_encode(array('status' => 'Success'));
    }

    public function email_coming_soon() {
        $data = array();
        $this->load->view('app/email/email-coming-soon', $data);
    }

    public function email_register() {
        $data = array();
        $this->load->view('app/email/confirm-email', $data);
    }

    public function send_sms() {
        $this->aws_pinpoint->send('13059705118', 'Welcome to LinkStream');
        //$this->aws_pinpoint->send('17142718161', 'Welcome to LinkStream');
    }

    public function get_data() {

        //IP
        $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        echo $ip;
        echo '<br>';
        echo '<br>';
        $ip = ($ip == '::1') ? '170.55.19.206' : $ip;
        echo $ip;
        echo '<br>';
        echo '<br>';
        //LOCATION ip-api.com
        echo 'ip-api.com';
        $location = file_get_contents('http://ip-api.com/json/' . $ip);
        $data_loc = json_decode($location, true);
        echo '<pre>';
        print_r($data_loc);
        echo '</pre>';
        echo '<br>';
        echo '<br>';




        //IP
        //$ip = $this->input->ip_address();
        //echo $ip;
        //echo '<br>';
        //$ip = '108.162.210.140';
        //echo $ip;
        //echo '<br>';
//        $location = file_get_contents('http://ip-api.com/json/' . $ip);
//        //you can also use ipinfo.io or any other ip location provider API
//        //print_r($location);exit;
//        $data_loc = json_decode($location, true);
//        echo '<pre>';
//        print_r($data_loc);
//        echo '</pre>';
//        echo '<br>';
//        echo $data_loc['country'];
//        echo '<br>';
//        echo $data_loc['countryCode'];
//        echo '<br>';
//        echo $data_loc['region'];
//        echo '<br>';
//        echo $data_loc['regionName'];
//        echo '<br>';
//        echo $data_loc['city'];
//        echo '<br>';
//        echo $data_loc['zip'];
//        echo '<br>';
//        echo '<br>';
//        echo '<br>';
//        echo '<br>';
//
//        $location = file_get_contents('https://api.ipgeolocationapi.com/geolocate/' . $ip);
//        //you can also use ipinfo.io or any other ip location provider API
//        //print_r($location);exit;
//        $data_loc = json_decode($location, true);
//        echo '<pre>';
//        print_r($data_loc);
//        echo '</pre>';
//        echo '<br>';
//        echo '<br>';
//        echo '<br>';
//        echo '<br>';
//        $location = file_get_contents('https://api.ipgeolocationapi.com/countries');
//        //you can also use ipinfo.io or any other ip location provider API
//        //print_r($location);exit;
//        $data_loc = json_decode($location, true);
//        echo '<pre>';
//        print_r($data_loc);
//        echo '</pre>';
//        echo '<br>';
//        echo '<br>';
//        echo '<br>';
//        echo '<br>';
//        $ipaddress = '';
//        if (isset($_SERVER['HTTP_CLIENT_IP']))
//            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
//        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
//            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
//        else if (isset($_SERVER['HTTP_X_FORWARDED']))
//            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
//        else if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
//            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
//        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
//            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
//        else if (isset($_SERVER['HTTP_FORWARDED']))
//            $ipaddress = $_SERVER['HTTP_FORWARDED'];
//        else if (isset($_SERVER['REMOTE_ADDR']))
//            $ipaddress = $_SERVER['REMOTE_ADDR'];
//        else
//            $ipaddress = 'UNKNOWN';
//        echo $ipaddress;
//        echo '<br>';
//        echo '<br>';
//        echo '<br>';

        $this->load->library('user_agent');
        if ($this->agent->is_browser()) {
            $agent = $this->agent->browser() . ' ' . $this->agent->version();
        } elseif ($this->agent->is_robot()) {
            $agent = $this->agent->robot();
        } elseif ($this->agent->is_mobile()) {
            $agent = $this->agent->mobile();
        } else {
            $agent = 'Unidentified User Agent';
        }

        echo $agent;
        echo '<br>';
        echo '<br>';

        echo $this->agent->platform(); // Platform info (Windows, Linux, Mac, etc.)
        echo '<br>';
        echo '<br>';

//        $newdata = array(
//            'username' => 'johndoe',
//            'email' => 'johndoe@some-site.com',
//            'logged_in' => TRUE
//        );
//        $this->session->set_userdata($newdata);
//        print_r($this->session->userdata());
//        echo '<br>';
//        $session_id = $this->session->userdata('session_id');
//        print_r($session_id);
//        echo '<br>';
        print_r($this->session);
        echo '<br>';
        echo '<br>';
//        print_r($this->session->tempdata());
//        echo '<br>';
        print_r(session_id());
        echo '<br>';
        echo '<br>';


        echo $_SERVER['DOCUMENT_ROOT'];
        echo '<br>';
        echo '<br>';
    }

    public function upgrade() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $register_user = $this->User_model->fetch_user_by_search(array('id' => $user['id']));
            $data = array();
            $data['user'] = $register_user;
            //$data['order'] = $this->Streamy_model->fetch_content_order($register_user['id']);
            $this->load->view($this->loc_path . 'content/upgrade', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

    public function youtube() {
        $data = array();
        $this->load->view($this->loc_path . 'youtube_test', $data);
    }

    //NEW FUNCTIONS 02/2020

    public function track_add_js() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $streamy = array();
            $streamy['user_id'] = $user['id'];
            $streamy['name'] = $this->input->post('song_name', TRUE);
            $streamy['status_id'] = '1';
            $streamy['type_id'] = '4'; //Track
            $streamy['priority_id'] = $this->input->post('priority', TRUE);
            $streamy['genre_id'] = $this->input->post('genre', TRUE);
            $streamy['public'] = $this->input->post('visibility', TRUE);
            $date = (!empty($this->input->post('date'))) ? $this->input->post('date', TRUE) : date('Y-m-d');
            $streamy['publish_at'] = date('Y-m-d', strtotime($date));
            $streamy['url'] = '';
            $streamy['audio_file'] = '';
            $streamy['coverart'] = '';
            $streamy['prize'] = $this->input->post('prize', TRUE);
            $streamy['track_type'] = $this->input->post('track_type', TRUE);
            //AUDIO FILE
            if (!empty($_FILES['input_b1']['name'])) {
                $upload = $this->s3_upload('input_b1', 'Audio');
                if ($upload['status']) {
                    $streamy['audio_file'] = $upload['file_name'];
                } else {
                    
                }
            }
            //COVERART FILE
            if (!empty($_FILES['input_b2']['name'])) {
                $upload = $this->s3_upload('input_b2', 'Coverart');
                if ($upload['status']) {
                    $streamy['coverart'] = $upload['file_name'];
                } else {
                    
                }
            }
            $streamy_id = $this->Streamy_model->insert_streamy($streamy);
            $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Track Added: ' . $streamy_id));
            echo json_encode(array('status' => 'Success'));
        }
    }

    public function link_add_js() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $streamy = array();
            $streamy['user_id'] = $user['id'];
            $streamy['name'] = $this->input->post('title', TRUE);
            $streamy['status_id'] = '1';
            $streamy['type_id'] = '3'; //Link
            //$streamy['priority_id'] = '';
            //$streamy['genre_id'] = '';
            //$streamy['public'] = '';
            //$date = (!empty($this->input->post('date'))) ? $this->input->post('date', TRUE) : date('Y-m-d');
            //$streamy['publish_at'] = '';
            $streamy['url'] = $this->input->post('url', TRUE);
            //$streamy['audio_file'] = '';
            //$streamy['coverart'] = '';
            //$streamy['prize'] = '0.00';
            //$streamy['track_type'] = '';
            //$streamy['audio_file'] = '';
            //COVERART FILE
            if (!empty($_FILES['input_b2']['name'])) {
                $upload = $this->s3_upload('input_b2', 'Coverart');
                if ($upload['status']) {
                    $streamy['coverart'] = $upload['file_name'];
                } else {
                    
                }
            }
            $streamy_id = $this->Streamy_model->insert_streamy($streamy);
            $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Link Added: ' . $streamy_id));
            echo json_encode(array('status' => 'Success'));
        }
    }

    public function video_add_js() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $streamy = array();
            $streamy['user_id'] = $user['id'];
            $streamy['name'] = $this->input->post('title', TRUE);
            $streamy['status_id'] = '1';
            $streamy['type_id'] = '2'; //Youtube
            //$streamy['priority_id'] = '';
            //$streamy['genre_id'] = '';
            //$streamy['public'] = '';
            //$date = (!empty($this->input->post('date'))) ? $this->input->post('date', TRUE) : date('Y-m-d');
            //$streamy['publish_at'] = '';
            $streamy['url'] = $this->input->post('url', TRUE);
            //$streamy['audio_file'] = '';
            //$streamy['coverart'] = '';
            //$streamy['prize'] = '0.00';
            //$streamy['track_type'] = '';
            //$streamy['audio_file'] = '';
            //$streamy['coverart'] = '';
            $streamy_id = $this->Streamy_model->insert_streamy($streamy);
            $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Video Added: ' . $streamy_id));
            echo json_encode(array('status' => 'Success'));
        }
    }

    public function content_by_id_js() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $id = $this->input->post('id', TRUE);
            $content = $this->Streamy_model->fetch_streamy_by_id($id);
            echo json_encode(array('status' => 'Success', 'content' => $content));
        }
    }

    public function fetch_genres_js() {
        $genres = $this->Streamy_model->fetch_genres();
        echo json_encode(array('status' => 'Success', 'genres' => $genres));
    }

    public function test_api() {
        $response = array();
        $this->method = 'Contact';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-API-KEY:F5CE12A3-27BD-4186-866C-D9D019E85076"));
        curl_setopt($ch, CURLOPT_URL, 'https://api-dev.link.stream/v1/users/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "linkstream:LinkStream@2020");
        //curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=pa@link.stream&password=12345');
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('email' => 'pa@link.stream', 'password' => '12345'));

        $output = curl_exec($ch);
        curl_close($ch);
        $object = json_decode($output);
        print_r($object);
//        if (!empty($object)) {
//            $response['IsSuccess'] = true;
//            $response['Data'] = json_decode($object, true);
//            $response['Data']['vl'] = $id;
//        } else {
//            $response['IsSuccess'] = false;
//            $response['Messages'] = 'Error connecting to the API';
//        }
//        return $response;
    }

    public function test_api_js() {
        $data = array();
        $this->load->view($this->loc_path . 'api_test', $data);
    }

    //END NEW FUNCTIONS 02/2020
    //EMAIL TEST
    public function email_template() {
        $data = array();
        $body = $this->load->view('app/email/email-template', $data, true);
        $this->general_library->send_ses('Paul Ferra', 'paul@link.stream', 'Streamy', 'noreply@link.stream', 'Email Test', $body);
    }

    public function email_template_view() {
        $data = array();
        $this->load->view('app/email/email-template', $data);
    }

    public function yt() {
        //$cmd = '/usr/local/Cellar/youtube-dl/2020.03.01/bin/youtube-dl -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --extract-audio --audio-format mp3 "https://www.youtube.com/watch?v=BFaRWXEpFrs"'; 
        //exec($cmd . " 2>&1", $output);
        //$url = "https://www.youtube.com/watch?v=BFaRWXEpFrs";
        $url = 'https://www.youtube.com/watch?v=g4S3jUtqcyM';
        //INFO
        $cmd = '/usr/local/Cellar/youtube-dl/2020.05.08/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --get-title --get-id --get-thumbnail --get-filename  --get-format  ' . $url;
        exec($cmd, $output);
//        echo "<pre>";
//        print_r($output);
//        echo "</pre>";
//        exit;
        $title = $output[0];
        $id = $output[1];
        $thumbnail_url = $output[2];

        //GET VIDEO
        $cmd = '/usr/local/Cellar/youtube-dl/2020.05.08/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" ' . $url;
        //$cmd = '/usr/local/Cellar/youtube-dl/2020.03.01/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --write-thumbnail ' . $url;
        exec($cmd, $output);
//        echo "<pre>";
//        print_r($output);
//        echo "</pre>";

        echo 'Title: ' . $title;
        echo '<br>';
        echo 'ID: ' . $id;
        echo '<br>';
        echo 'Thumbnail Url: ' . $thumbnail_url;
        echo '<br>';

        //GET mp3
        $video = '/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/' . $id . '.mp4';
        $cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -i " . $video . " /Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/" . $id . ".mp3";
        exec($cmd, $output);
//        echo "<pre>";
//        print_r($output);
//        echo "</pre>";
        echo 'Audio: ' . $id . ".mp3";
        echo '<br>';
        unlink($video);
    }

    public function sc() {
//      $cmd = '/usr/local/Cellar/youtube-dl/2020.03.01/bin/youtube-dl -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --extract-audio --audio-format mp3 "https://www.youtube.com/watch?v=BFaRWXEpFrs"'; 
        //exec($cmd . " 2>&1", $output);

        $url = "https://soundcloud.com/iamstarinthesky/thasswassup-feat-tommy-ice-prod-tsurreal-jkei";
        //INFO
        $cmd = '/usr/local/Cellar/youtube-dl/2020.05.08/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --get-title --get-id --get-thumbnail --get-filename  --get-format  ' . $url;
        //$cmd = '/usr/local/Cellar/youtube-dl/2020.05.08/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --get-title --get-id ' . $url;

        exec($cmd, $output);
        echo "<pre>";
        print_r($output);
        echo "</pre>"; //exit;
        $title = $output[0];
        $id = $output[1];
        $thumbnail_url = $output[2];
        $filename = $output[3];
        $final_filename = str_replace("/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/", "", $filename);
        $format = $output[4];

        //GET AUDIO
//        $cmd = '/usr/local/Cellar/youtube-dl/2020.05.0/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" ' . $url;
        $cmd = '/usr/local/Cellar/youtube-dl/2020.05.08/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --write-thumbnail ' . $url;
        exec($cmd, $output);
        echo "<pre>";
        print_r($output);
        echo "</pre>";

        echo 'Title: ' . $title;
        echo '<br>';
        echo 'ID: ' . $id;
        echo '<br>';
        echo 'Thumbnail Url: ' . $thumbnail_url;
        echo '<br>';
        echo 'Audio: ' . $final_filename;
        echo '<br>';
    }

    public function bs() {
//      $cmd = '/usr/local/Cellar/youtube-dl/2020.03.01/bin/youtube-dl -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --extract-audio --audio-format mp3 "https://www.youtube.com/watch?v=BFaRWXEpFrs"'; 
        //exec($cmd . " 2>&1", $output);

        $url = "https://www.beatstars.com/beat/start-a-war-whook-free-download-4534917";
        //INFO
        $cmd = '/usr/local/Cellar/youtube-dl/2020.05.08/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --get-title --get-id --get-thumbnail --get-filename  --get-format  ' . $url;
        //$cmd = '/usr/local/Cellar/youtube-dl/2020.05.08/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --get-title --get-id ' . $url;

        exec($cmd, $output);
        echo "<pre>";
        print_r($output);
        echo "</pre>";
        exit;
        $title = $output[0];
        $id = $output[1];
        $thumbnail_url = $output[2];
        $filename = $output[3];
        $final_filename = str_replace("/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/", "", $filename);
        $format = $output[4];

        //GET AUDIO
//        $cmd = '/usr/local/Cellar/youtube-dl/2020.05.0/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" ' . $url;
        $cmd = '/usr/local/Cellar/youtube-dl/2020.05.08/bin/youtube-dl  -o "/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/%(id)s.%(ext)s" --write-thumbnail ' . $url;
        exec($cmd, $output);
        echo "<pre>";
        print_r($output);
        echo "</pre>";

        echo 'Title: ' . $title;
        echo '<br>';
        echo 'ID: ' . $id;
        echo '<br>';
        echo 'Thumbnail Url: ' . $thumbnail_url;
        echo '<br>';
        echo 'Audio: ' . $final_filename;
        echo '<br>';
    }

    //TIME TEST
    public function local_to_gtm() {
        $timezone = 'America/New_York';
        $date = '2020-04-06';
        $time = '21:10:00';
        $datetime = $date . ' ' . $time;
        echo $datetime . ' ' . $timezone;
        echo '<br>';
        $local_date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime, new DateTimeZone($timezone));
        $utc_date = $local_date;
        $utc_date->setTimeZone(new DateTimeZone('UTC'));
        echo $utc_date->format('Y-m-d H:i:s') . ' UTC';
    }

    public function gtm_to_local() {
        $timezone = 'America/New_York';
        $datetime = '2020-04-07 01:10:00';
        echo $datetime . ' UTC';
        echo '<br>';
        $utc_date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime, new DateTimeZone('UTC'));
        $local_date = $utc_date;
//        echo $timezone;
//        echo '<br>';
        $local_date->setTimeZone(new DateTimeZone($timezone));
        echo $local_date->format('Y-m-d H:i:s') . ' ' . $timezone;
//        echo '<br>';
//        echo $local_date->format('Y-m-d');
//        echo '<br>';
//        echo $local_date->format('H:i:s');
//        echo '<br>';
    }

    //Stripe
//    public function payment_intent() {
//        $this->load->library('Stripe');
//        $response = $this->stripe->payment_intent();
//        print_r($response);
//    }
//    public function payment_token() {
//        $this->load->library('Stripe');
//        $number = '4242424242424242';
//        $exp_month = 6;
//        $exp_year = 2021;
//        $cvc = '314';
//        $response = $this->stripe->create_token($number, $exp_month, $exp_year, $cvc);
//        print_r($response);
//    }
    //Definir dos funciones 1 con token y charge y otra solo charge
    public function payment_charge() {
        if ($this->input->post()) {
            $this->load->library('Stripe');
            $number = '4242424242424242';
            $exp_month = 6;
            $exp_year = 2021;
            $cvc = '314';
            $name = 'Paolo Maledeto';
            $zip = '33312';
//            $amount = 100;
//            $ls_fee = round((($amount * 0.03) + 0.30), 2);
//            $total_amount = $amount + $ls_fee;
//            echo 'Amount: ' . $amount;
//            echo '<br>';
//            echo 'LS Fee: ' . $ls_fee;
//            echo '<br>';
//            echo 'Total Amount: ' . $total_amount;
//            echo '<br>';
//            exit;
            $response = $this->stripe->create_token($number, $exp_month, $exp_year, $cvc, $name, $zip);
            if ($response['status']) {
                $amount = 100;
                //$ls_fee = round((($amount * 0.029) + 0.30), 2);
                $cc_fee = round((($amount * 0.03) + 0.30), 2);
                $ls_fee = 1.99;
                $total_amount = $amount + $cc_fee + $ls_fee;
                echo 'Amount: ' . $amount;
                echo '<br>';
                echo 'CC Fee: ' . $cc_fee;
                echo '<br>';
                echo 'LS Fee: ' . $ls_fee;
                echo '<br>';
                echo 'Total Amount: ' . $total_amount;
                echo '<br>';
                $response = $this->stripe->create_charge($total_amount, 'Payment Example', $response['payment_id']);
                echo 'Confirmation Page' . '<br>';
                echo '<pre>';
                print_r($response);
                echo '</pre>';
                echo '<br>';
                $payment_charge_id = $response['payment_charge_id'];
                $destination_a = 'acct_1GiQbXFi0TLGlCIZ'; //paolofq@gmail.com
                $destination_b = 'acct_1GiRAQIrLgupgBOM'; //legal@linkstream.com
                $destination_a_amount = 60;
                $destination_b_amount = 40;
                $transfer_group = '';
                //$charge_id = 'ch_1Gigk5KagYlVQcNzn4bD6VQf';
                $response = $this->stripe->create_transfer($destination_a_amount, $destination_a, $transfer_group, $payment_charge_id);
                echo 'Destination A';
                echo '<br>';
                echo '<pre>';
                print_r($response);
                echo '</pre>';
                echo '<br>';
                $response = $this->stripe->create_transfer($destination_b_amount, $destination_b, $transfer_group, $payment_charge_id);
                echo 'Destination B';
                echo '<br>';
                echo '<pre>';
                print_r($response);
                echo '</pre>';
                echo '<br>';
            } else {
                print_r($response);
            }
        } else {
            $data = [];
            $this->load->view($this->loc_path . 'example/payment', $data);
        }
    }

    public function payment_refund() {
        $this->load->library('Stripe');
        $response = $this->stripe->create_refund('ch_1GiKoYKagYlVQcNzwKAPNyhn', 0.25, 'requested_by_customer');
        print_r($response);
    }

    public function create_account() {

        $this->load->library('Stripe_library');
        //$type = 'custom'; //API custom
        $country = 'US';
        $currency = 'USD';
        $account_holder_name = 'Paul Ferra';
        $account_holder_type = 'individual'; //company
        $routing_number = '111000000';
        $account_number = '000123456789';
        $email = 'paul@linkstream.com';

//            $requested_capabilities = [
//                'card_payments',
//                'transfers',
//            ];
        $business_type = 'individual'; //individual-company-non_profit-government_entity(US only)
        $external_account = [
            'object' => 'bank_account',
            'country' => $country,
            'currency' => $currency,
            'account_holder_name' => $account_holder_name,
            'account_holder_type' => $account_holder_type,
            'routing_number' => $routing_number,
            'account_number' => $account_number
        ];
        $response = $this->stripe_library->create_account($country, $email, $business_type, $external_account);
        print_r($response);
    }

    public function connect_stripe() {
        $data = [];
        $this->load->view($this->loc_path . 'example/account', $data);
    }

//    public function create_account() {
//        if ($this->input->post()) {
//            $this->load->library('Stripe_library');
//            //$type = 'custom'; //API custom
//            $country = 'US';
//            $email = 'paul@linkstream.com';
////            $requested_capabilities = [
////                'card_payments',
////                'transfers',
////            ];
//            $business_type = ''; //'individual'; //individual-company-non_profit-government_entity(US only)
//
//            $response = $this->Stripe_library->create_account($country, $email);
//            print_r($response);
//        } else {
//            $data = [];
//            $this->load->view($this->loc_path . 'example/account', $data);
//        }
//    }
//
//    public function confirm_account() {
//        $this->load->library('Stripe');
//        $code = $this->input->get('code');
//        echo $code;
//        echo '<br>';
//        $response = $this->stripe->auth_token($code);
//        print_r($response);
//    }
//
//    public function separate_charges() {
//        $this->load->library('Stripe');
//        $destination_a = 'acct_1GiQbXFi0TLGlCIZ'; //paolofq@gmail.com
//        $destination_b = 'acct_1GiRAQIrLgupgBOM'; //legal@linkstream.com
//        $total_amount = 13;
//        $ls_fee = 3;
//        $customer_amount = 10;
//        $destination_a_amount = 6;
//        $destination_b_amount = 4;
//        $number = '4242424242424242';
//        $exp_month = 6;
//        $exp_year = 2021;
//        $cvc = '314';
//        $name = 'Test User';
//        $zip = '33312';
//        $response = $this->stripe->create_token($number, $exp_month, $exp_year, $cvc, $name, $zip);
//        if ($response['status']) {
//            // $amount = 0.50;
//            $response = $this->stripe->create_charge($total_amount, 'Multiple Payment Example', $response['payment_id']);
//            echo 'Confirmation Page' . '<br>';
//            print_r($response);
//            echo '<br>';
//            if ($response['status']) {
//                $response = $this->stripe->create_transfer($destination_a_amount, $destination_a);
//                print_r($response);
//                echo '<br>';
//                $response = $this->stripe->create_transfer($destination_b_amount, $destination_b);
//                print_r($response);
//                echo '<br>';
//            } else {
//                print_r($response);
//            }
//        } else {
//            print_r($response);
//        }
//    }
//
//    public function split_charges() {
//        $this->load->library('Stripe');
//        $destination_a = 'acct_1GiQbXFi0TLGlCIZ'; //paolofq@gmail.com
//        $destination_b = 'acct_1GiRAQIrLgupgBOM'; //legal@linkstream.com
//        $total_amount = 100;
//        $ls_fee = 10;
//        $destination_a_amount = 70;
//        $destination_b_amount = 20;
//        $transfer_group = '001';
//        $response = $this->stripe->create_payment_intent($total_amount, $transfer_group);
//        if ($response['status']) {
//            echo 'Intent Sucess';
//            echo '<br>';
//            print_r($response);
//            echo '<br>';
////            $response = $this->stripe->create_transfer($destination_a_amount, $destination_a, $transfer_group);
////            echo 'Destination A';
////            echo '<br>';
////            print_r($response);
////            echo '<br>';
////            $response = $this->stripe->create_transfer($destination_b_amount, $destination_b, $transfer_group);
////            echo 'Destination B';
////            echo '<br>';
////            print_r($response);
////            echo '<br>';
//        } else {
//            echo 'Intent Error';
//            echo '<br>';
//            print_r($response);
//            echo '<br>';
//        }
//    }
//
//    public function split_charges_js() {
//        $this->load->library('Stripe');
//        $destination_a = 'acct_1GiQbXFi0TLGlCIZ'; //paolofq@gmail.com
//        $destination_b = 'acct_1GiRAQIrLgupgBOM'; //legal@linkstream.com
//        $total_amount = 100;
//        $ls_fee = 10;
//        $destination_a_amount = 70;
//        $destination_b_amount = 20;
//        $transfer_group = '003';
//        $response = $this->stripe->create_payment_intent($total_amount, $transfer_group);
//        if ($response['status']) {
////            $charge_id = $response['intent_id'];
////            $response_a = $this->stripe->create_transfer($destination_a_amount, $destination_a, $transfer_group, $charge_id);
////            print_r($response_a);$transfer_a = $response_a['transfer_id'];
////            $response_b = $this->stripe->create_transfer($destination_b_amount, $destination_b, $transfer_group, $charge_id);
////            $transfer_b = $response_b['transfer_id'];
//
//
//            $output = [
//                'intent_id' => $response['intent_id'],
//                'clientSecret' => $response['client_secret'],
//                    //'$transfer_a' => $response['transfer_a'],
//                    //'$transfer_b' => $response['transfer_b']
//            ];
//            echo json_encode($output);
//        } else {
//            echo 'Intent Error';
//            echo '<br>';
//            print_r($response);
//            echo '<br>';
//        }
//    }
//
//    public function collect_payments() {
//        if ($this->input->post()) {
//            $this->load->library('Stripe');
//        } else {
//            $data = [];
//            $this->load->view($this->loc_path . 'example/collect_payments', $data);
//        }
//    }
//
//    public function split_transfer() {
//        $this->load->library('Stripe');
//        $destination_a = 'acct_1GiQbXFi0TLGlCIZ'; //paolofq@gmail.com
//        $destination_b = 'acct_1GiRAQIrLgupgBOM'; //legal@linkstream.com
//        $total_amount = 100;
//        $ls_fee = 10;
//        $destination_a_amount = 2;
//        $destination_b_amount = 5;
//        $transfer_group = '003';
//        $charge_id = 'ch_1Gigk5KagYlVQcNzn4bD6VQf';
//        $response = $this->stripe->create_transfer($destination_a_amount, $destination_a, $transfer_group, $charge_id);
//        echo 'Destination A';
//        echo '<br>';
//        print_r($response);
//        echo '<br>';
//        $response = $this->stripe->create_transfer($destination_b_amount, $destination_b, $transfer_group, $charge_id);
//        echo 'Destination B';
//        echo '<br>';
//        print_r($response);
//        echo '<br>';
//    }







    public function fetch_subscription() {
        $this->load->library('Stripe');
        $customer_id = 'cus_HIsbVuMPkHrVe6';
        $plan = 'plan_HGtTLCzgNfaGeM';
        $default_payment_method = 'pm_1GkGm8KagYlVQcNzyZRliWIv';
        $response = $this->stripe->fetch_subscription();
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        echo '<br>';
    }

    public function zip_info() {
        $zip_name = '94303c1d12cafd95fcd1a8ce45572d91.zip';
        $temp_dir = $this->general_library->get_temp_dir();
        $zip_file = $temp_dir . '/' . $zip_name;
        echo $zip_file;
        echo '<br>';
        $zip = new ZipArchive;
        $res = $zip->open($zip_file);
//        if ($res === TRUE) {
//            print_r($zip->statName('file_example_MP3_2MG.mp3'));
//            $zip->close();
//        } else {
//            echo 'falló, código:' . $res;
//        }
//        //
//        if ($zip->open($zip_file) == TRUE) {
//            for ($i = 0; $i < $zip->numFiles; $i++) {
//                $filename = $zip->getNameIndex($i);
//                echo $filename;
//                echo '<br>';
//            }
//        }
        if ($res === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $mystring = 'abc';
                $findme = 'a';
                $pos = strpos($filename, 'MACOSX/.');
                if ($pos === false) {
                    echo $filename;
                    echo '<br>';
                }
//                else {
//                    echo "La cadena '$findme' fue encontrada en la cadena '$mystring'";
//                    echo " y existe en la posición $pos";
//                }
//                echo $filename;
//                echo '<br>';
            }
        } else {
            echo 'falló, código:' . $res;
        }
    }

    public function unzip_temp() {
        $zip_file = '94303c1d12cafd95fcd1a8ce45572d91.zip';
        $folder = '94303c1d12cafd95fcd1a8ce45572d91';
//        preg_match("/^data:file\/(.*);base64/i", $file, $match);
//        $ext = (!empty($match[1])) ? $match[1] : 'zip';
//        $rand_name = md5(uniqid(rand(), true));
//        $file_name = $rand_name . '.' . $ext;
        //upload image to server 
//        file_put_contents($this->temp_dir . '/' . $file_name, file_get_contents($file));
//        print_r($this->temp_dir . '/' . $file_name);
        ## Extract the zip file ---- start
        $temp_dir = $this->general_library->get_temp_dir();
        $zip = new ZipArchive;
        $res = $zip->open($temp_dir . '/' . $zip_file);
        if ($res === TRUE) {
            // Unzip path
            //$extractpath = $this->temp_dir . '/' . "files/";
            $extractpath = $temp_dir . '/' . $folder . '/';
            // Extract file
            $zip->extractTo($extractpath);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    public function zip_file() {
        $temp_dir = $this->general_library->get_temp_dir();
        $zip_name = '94303c1d12cafd95fcd1a8ce45572d91.zip';
        $zip_file = $temp_dir . '/' . $zip_name;
        $zip = new ZipArchive;
        if ($zip->open($zip_file) === TRUE) {
            $data_file = $zip->getFromName('file_example_MP3_2MG.mp3');
            $zip->close();
            file_put_contents($temp_dir . '/' . 'file_example_MP3_2MG.mp3', $data_file);
//            $src = 'data:' . mime_content_type($this->temp_dir . '/' . $audio['coverart']) . ';base64,' . base64_encode($data_image);
//            $audio['data_image'] = $src;
        } else {
            echo 'falló';
        }
    }

    public function zip_info_2() {
        $zip_name = '94303c1d12cafd95fcd1a8ce45572d91.zip';
        $path = (ENV == 'live') ? 'Prod/Audio/' : 'Dev/Audio/';
        $zip_file = 'https://s3.us-east-2.amazonaws.com/files.link.stream/' . $path . $zip_name;
//        $temp_dir = $this->general_library->get_temp_dir();
//        $zip_file = $temp_dir . '/' . $zip_name;
        echo $zip_file;
        echo '<br>';
        $zip = new ZipArchive;
        $res = $zip->open($zip_file);
        if ($res === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $mystring = 'abc';
                $findme = 'a';
                $pos = strpos($filename, 'MACOSX/.');
                if ($pos === false) {
                    echo $filename;
                    echo '<br>';
                }
            }
        } else {
            echo 'falló, código:' . $res;
        }
    }

//    public function mp3_to_mp4() {
//        $file_name = 'file_example_MP3_2MG.mp3';
//        $path = '/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/';
//        $file_mp3 = $path . $file_name;
//        $file_name_mp4 = 'file_example_MP3_2MG.mp4';
//        $file_mp4 = $path . $file_name_mp4;
//        $image_name = 'download.jpeg';
//        $image = $path . $image_name;
////        $cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -loop 1 -i " . $image . " -i " . $file_mp3 . " -c:a copy -c:v libx264 -shortest " . $file_mp4;
////        $cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -loop 1 -i " . $image . " -i " . $file_mp3 . " -shortest -c:v libx264 -c:a copy " . $file_mp4;
//        ///////$cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg " . " -i " . $file_mp3 . " " . $file_mp4;
////        $cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -loop 1 -framerate 1 " . " -i " . $image . " -i " . $file_mp3 . " -c copy -shortest " . $file_mp4;
//        $cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -loop 1 -i " . $image . " -i " . $file_mp3 . " -c:a libmp3lame -c:v libx264 -b:a 128k -shortest " . $file_mp4;
//        exec($cmd, $output);
//        echo "<pre>";
//        print_r($output);
//        echo "</pre>";
//        //ffmpeg -i filename.mp3 newfilename.wav newfilename.ogg newfilename.mp4
//        //ffmpeg -loop 1 -framerate 1 -i image.jpg -i music.mp3 -c copy -shortest output.mkv
//        //ffmpeg -framerate 1 -i input.mp3 -i cover.jpg -c:a copy -s 1280x720 output.mp4
//        //ffmpeg -i input.mp3 -i cover.jpg -map_metadata 0 -map 0 -map 1 output.mp3
//        //ffmpeg -loop 1 -i logo.jpg -i source.mp3 -c:a libmp3lame -c:v libx264 -b:a 128k -shortest output.mp4
//    }

    public function google_beatstars_test() {
        $html = @file_get_contents('https://main.v2.beatstars.com/musician?permalink=scottstyles/&fields=social_networks', true);
        $beatstars_data = json_decode($html, TRUE);
        echo '<pre>';
        print_r($beatstars_data);
        echo '</pre>';
    }

    public function google_beatstars() {

        $num = 100; //Count of Record (100)
        $start = 300; //Page: Page = Page + Num (0)
        //LAST NUM 100, 300

        $keyword = 'site:www.beatstars.com';
        $url = "http://www.google.com/search?q=" . urlencode($keyword) . "&num=" . $num . "&start=" . $start;
        //GOOGLE CURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1); // set to 0 to eliminate header info from response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        $header = array();
        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[] = "Accept-Language: en-us,en;q=0.5";
        $header[] = "Pragma: "; // browsers keep this blank.
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($ch, CURLOPT_USERAGENT, $useragents[rand(0, sizeof($useragents) - 1)]);
        $google_response = curl_exec($ch); //execute post and get results
        curl_close($ch);
        if (!empty($google_response)) {
            $count = 0;
            $bp = 0;
            $table = '<table class="table table-striped">

  <tbody>
    <tr>
      <th scope="row"></th>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>';
            while ($bp = strpos($google_response, "https://www.beatstars.com/", $bp + 1)) {
                $count++;
                $end = strpos($google_response, "&amp;sa=", $bp);
                $beatstars_link = trim(substr($google_response, $bp, $end - $bp));
//                echo '<pre>';
//                print_r($beatstars_link);
//                echo '</pre>';
                $beatstars_profile = substr($beatstars_link, 26);

                if (!empty($beatstars_profile)) {
//                    echo '<b>';
//                    print_r($beatstars_profile);
//                    echo '</b></br>';
////                    echo '<pre>';
//                    print_r($beatstars_link);
//                    echo '</br>';
                    $beatstars_url = 'https://main.v2.beatstars.com/musician?permalink=' . $beatstars_profile . '&fields=social_networks';
//                    echo '<pre>';
//                    print_r($beatstars_url);
//                    echo '</pre>';
                    $html = @file_get_contents($beatstars_url, true);
                    if (!empty($html)) {
                        $beatstars_data = json_decode($html, TRUE);
                        if (!empty($beatstars_data['response']['data']['social_networks'])) {
//                        echo '<pre>';
//                        print_r($beatstars_data['response']['data']['social_networks']);
//                        echo '</pre>';
                            $social_networks = $beatstars_data['response']['data']['social_networks'];
                            $social = [];
                            $social['Beatstars'] = $beatstars_link;
                            $social['Youtube'] = '';
                            $social['Instagram'] = '';
                            $social['Facebook'] = '';
                            $social['Twitter'] = '';
                            $social['SoundCloud'] = '';
                            $social['TikTok'] = '';
                            foreach ($social_networks as $social_network) {
//                            echo '<pre>';
//                            print_r($social_network['uri']);
//                             print_r($social_network['uri']);
//                            echo '</br>';
                                $social[$social_network['text']] = $social_network['uri'];
                            }
                            $table .= '
    <tr>
      <td scope="row">' . $social['Beatstars'] . '</td>
      <td>' . $social['Youtube'] . '</td>
      <td>' . $social['Instagram'] . '</td>
      <td>' . $social['Facebook'] . '</td>
      <td>' . $social['Twitter'] . '</td>
      <td>' . $social['SoundCloud'] . '</td>
      <td>' . $social['TikTok'] . '</td>
    </tr>
 ';
                        }
                    }

//                    echo '<pre>';
//                    print_r($social);
//                    echo '</pre>';
//                    echo '<pre>';
//                    print_r(' ');
//                    echo '</br>';
                } else {
//                    echo 'Empty' . '<br>';
                }
                $bp++;
            }
            $table .= '

  
  </tbody>
</table>';
        }
        echo $table;
    }

    public function google_get() {
        $keyword = 'site:www.beatstars.com';
        usleep(400000 * rand(0, 16));
        $url = "http://www.google.com/search?q=" . urlencode($keyword) . "&num=3";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HEADER, 1); // set to 0 to eliminate header info from response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        $header = array();
        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[] = "Accept-Language: en-us,en;q=0.5";
        $header[] = "Pragma: "; // browsers keep this blank.
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($ch, CURLOPT_USERAGENT, $useragents[rand(0, sizeof($useragents) - 1)]);
        $resp = curl_exec($ch); //execute post and get results

        curl_close($ch);
//        echo '<pre>';
//        print_r($resp);
//        echo '</pre>';
//        $linkObjs = $resp->find('https://www.beatstars.com/');
//        echo '<pre>';
//        print_r($linkObjs);
//        echo '</pre>';
////        if (strpos($resp, "Location: http://sorry.google.com/sorry/") && strpos($resp, "302 Found"))
////            return -200;
        $count = 0;
        $bp = 0;
////
//        echo '<pre>';
//        print_r('beatstars');
//        echo '</pre>';
//        $pos = strpos($resp, "q=https://www.beatstars.com/");
//        if ($pos === false) {
//            echo "La cadena q=https://www.beatstars.com/ no fue encontrada";
//        } else {
//            echo "La cadena q=https://www.beatstars.com/ fue encontrada";
//            echo " y existe en la posición $pos";
//            $cadena2 = substr($resp, $pos, 50);
//            echo $cadena2;
//            $pos2 = strpos($cadena2, "sa=");
//            if ($pos2 === false) {
//                echo "La cadena &sa= no fue encontrada";
//            } else {
//                echo "La cadena &sa= fue encontrada";
//                echo " y existe en la posición $pos";
//            }
//            $cadena3 = substr($cadena2, 0, $pos2-2);
//            echo $cadena3;
//        }
////        $pos = strpos($resp, "&sa=");
////        if ($pos === false) {
////            echo "La cadena &sa= no fue encontrada";
////        } else {
////            echo "La cadena &sa= fue encontrada";
////            echo " y existe en la posición $pos";
////        }
//        exit;

        while ($bp = strpos($resp, "https://www.beatstars.com/", $bp + 1)) {
            $count++;
            $end = strpos($resp, "sa=", $bp);
            $link = trim(substr($resp, $bp, $end - $bp));
            $strlen = strlen($link);
//             echo '<pre>';
//            print_r($link);
//            echo '</pre>';
//            echo '<pre>';
//            print_r($strlen);
//            echo '</pre>';
            $link2 = substr($link, 0, $strlen - 5);
            $bp++;
//            if (stripos($link, ">Local business results for") && stripos($link, "href=\"http://maps.google.com/")) {
//                $count--;
//            }
//
//            if (stripos($link, "http://" . $domain) || stripos($link, "https://" . $domain) || stripos($link, "http://www." . $domain) || stripos($link, "https://www." . $domain)) {
//                $rank = $count;
//            }
            if ($count > 1) {
                echo '<pre>';
                print_r($link2);
                echo '</pre>';
                //
//                $url = $link2 . '/about';
                $url = 'https://main.v2.beatstars.com/musician?permalink=beatsbytalent&fields=social_networks';
//                echo '<pre>';
//                print_r($url);
//                echo '</pre>';
//                $this->load->helper('simple_html_dom');
//                //$this->load->helper('dom');
//                // Grab HTML From the URL
//                $html = file_get_html($url);
//                 echo '<pre>';
//                print_r($html);
//                echo '</pre>';
//                exit;

                $html = file_get_contents($url, true);
                $data = json_decode($html, TRUE);
//                echo '<pre>';
//                print_r($data);
//                echo '</pre>';
                if (!empty($data['response']['data']['social_networks'])) {
                    echo '<pre>';
                    print_r($data['response']['data']['social_networks']);
                    echo '</pre>';
                }
                exit;

//                $opts = array('http' => array('method' => "GET", 'header' => "Accept-language: en\r\n" . "Cookie: foo=bar\r\n", 'user_agent' => 'simple_html_dom'));
//                $context = stream_context_create($opts);
//
//                $html = file_get_html($url, FALSE, $context);
                // find all link on Codeigniter Site
                foreach ($html->find('div') as $e)
                    echo $e->href . '<br>';

//                $ch = curl_init();
//                curl_setopt($ch, CURLOPT_URL, $url);
//                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//                $dataFromExternalServer = curl_exec($ch);
//                curl_close($ch);
//                $data =  file_get_contents($url);
//                $getValues = file_get_contents($url . '&iframe=true');
//                print_r($getValues);
//                echo '<br>';
//                $pos = strpos($getValues, $search_link);
//                echo '<br>';
//                if ($pos === FALSE) {
//                    echo 'No exist';
//                } else {
//                    echo 'Exist, Position: ' . $pos;
//                }
//                echo '<pre>';
//                print_r($data);
//                echo '</pre>';
                exit;
            }
        }
////        if (!$rank)
////            $rank = 101;
////        return $rank;
        //$url = 'https://www.googleapis.com/customsearch/v1?key=[MY API KEY]&cx=[MY CX KEY]&q=lecture';
//        $keyword = 'site:www.beatstars.com';
//        $url = "http://www.google.com/search?q=" . urlencode($keyword) . "&num=10";
//        $body = file_get_contents($url);
//        $json = json_decode($body);
//        echo '<pre>';
//                print_r($json);
//                echo '</pre>';
//        if ($json->items) {
//            foreach ($json->items as $item) {
//                echo '<pre>';
//                print_r($item);
//                echo '</pre>';
//            }
//        }
    }

    public function paypal_login() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sandbox.paypal.com/v1/oauth2/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic QVhHRXRhYzQxZTJ3UjB5dDNGQk1rdElmNWt5TjNWRzhOUExVR3l0bnRFeHZHeVJNVTlLakdyazl4clhVSkhfdzJySldMWmVxUXZaWm9YbHc6RUpyZGc4X1B1ejRTV25aWHQ1TG55R1AzSkVPNmVpbUpEdDVCSjRXbFhXYm9zMGZRWkY3dG0tc2xsQUc0SmZ3M1hDZ2lHRElTS09OTmFfVXE=",
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        $object = json_decode($response, TRUE);
        echo '<pre>';
        print_r($object);
        echo '<pre>';
        echo $object['access_token'];
    }

    public function paypal_create_product($token) {
        $ch = curl_init();
        $param = [
            'name' => 'LS PRODUCT TEST 001',
            'type' => 'SERVICE',
            'description' => 'Testing Paypal Product',
        ];
        curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/catalogs/products');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $token;
        //$headers[] = 'Paypal-Request-Id: PLAN-18062019-001';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $object = json_decode($result, TRUE);
        echo '<pre>';
        print_r($object);
        echo '<pre>';
        //echo $object['access_token'];
    }

    public function paypal_create_plan($product, $token) {
        $ch = curl_init();
        $param = [
            'product_id' => $product,
            'name' => 'LS PLAN TEST 001',
            'status' => 'ACTIVE',
            'description' => 'Testing Paypal Plan',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => 'MONTH',
                        'interval_count' => 1
                    ],
                    'tenure_type' => 'REGULAR', //TRIAL
                    'sequence' => 1,
                    'total_cycles' => 0,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => '10',
                            'currency_code' => 'USD'
                        ]
                    ],
                ]
            ],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee' => [
                    'value' => '10',
                    'currency_code' => 'USD'
                ],
                'setup_fee_failure_action' => 'CONTINUE', //CANCEL
                'payment_failure_threshold' => 3
            ]
        ];
        curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/billing/plans');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $token;
        //$headers[] = 'Paypal-Request-Id: PLAN-18062019-001';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $object = json_decode($result, TRUE);
        echo '<pre>';
        print_r($object);
        echo '<pre>';
        //echo $object['access_token'];
    }

    public function paypal_subscription($plan, $token) {
        $ch = curl_init();
        $param = [
            'plan_id' => $plan,
            'start_time' => '2020-08-05T00:00:00Z',
            'quantity' => '1',
            'subscriber' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Doe'
                ],
                'email_address' => 'sb-4ac4711806191@personal.example.com',
                'payer_id' => 'L7Z2DLTJBYTHN'
            ],
            'application_context' => [
                'brand_name' => 'LinkStream',
                'locale' => 'en-US',
                "shipping_preference" => "SET_PROVIDED_ADDRESS",
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'
                ],
                "return_url" => "https://example.com/returnUrl",
                "cancel_url" => "https://example.com/cancelUrl",
                'setup_fee_failure_action' => 'CONTINUE', //CANCEL
                'payment_failure_threshold' => 3
            ]
        ];
        curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/billing/subscriptions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $token;
        $headers[] = 'Prefer: return=representation';
        //$headers[] = 'Paypal-Request-Id: SUBSCRIPTION-21092020-001';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $object = json_decode($result, TRUE);
        echo '<pre>';
        print_r($object);
        echo '<pre>';
        //echo $object['access_token'];
    }

    public function paypal_get_subscription() {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-DSXUN4ES9FJX');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer A21AAGs2Nz1sI4HSMgz0p0_0l3g8lxYaVXmzKVLym0mRqaqQ9nGnYs8EEoN2utwB8BDwEXIwY7AZY7S43iKAkJetrhlUjKR_A';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $object = json_decode($result, TRUE);
        echo '<pre>';
        print_r($object);
        echo '<pre>';
    }

    public function paypal_button() {
//        print_r(
//                '<div id="paypal-button-container"></div>
//<script src="https://www.paypal.com/sdk/js?client-id=AYsKfkDo19DuHtP2I7ZamBKgrW6d4_4wc8JIpWQ6k48BxtRw2cy1gn1o6INdYu3d22z4QqnfqP4VgMqv&vault=true" data-sdk-integration-source="button-factory"></script>
//' . "<script>
//  paypal.Buttons({
//      style: {
//          shape: 'rect',
//          color: 'gold',
//          layout: 'vertical',
//          label: 'subscribe',
//          
//      },
//      createSubscription: function(data, actions) {
//        return actions.subscription.create({
//          'plan_id': 'P-1VL02682SS657152EL4SCLZI'
//        });
//      },
//      onApprove: function(data, actions) {
//        alert(data.subscriptionID);
//      }
//  }).render('#paypal-button-container');
//</script>"
//        );
        print_r("<span id='cwppButton'></span>
<script src='https://www.paypalobjects.com/js/external/connect/api.js'></script>" .
                '<script>
paypal.use( ["login"], function (login) {
  login.render ({
   "appid":"AXGEtac41e2wR0yt3FBMktIf5kyN3VG8NPLUGytntExvGyRMU9KjGrk9xrXUJH_w2rJWLZeqQvZZoXlw",
    "authend":"sandbox",
    "scopes":"openid",
    "containerid":"cwppButton",
    "responseType":"code id_Token",
    "locale":"en-us",
    "buttonType":"CWP",
    "buttonShape":"pill",
    "buttonSize":"md",
    "fullPage":"false",
    "returnurl":"https://api-dev.link.stream/app/paypal_return"
  });
});
</script>');
    }

    public function paypal_return() {
        
    }

    public function paypal_user_token($code) {
        echo $code;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sandbox.paypal.com/v1/oauth2/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=authorization_code&code={$code}",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic QVhHRXRhYzQxZTJ3UjB5dDNGQk1rdElmNWt5TjNWRzhOUExVR3l0bnRFeHZHeVJNVTlLakdyazl4clhVSkhfdzJySldMWmVxUXZaWm9YbHc6RUpyZGc4X1B1ejRTV25aWHQ1TG55R1AzSkVPNmVpbUpEdDVCSjRXbFhXYm9zMGZRWkY3dG0tc2xsQUc0SmZ3M1hDZ2lHRElTS09OTmFfVXE=",
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        $object = json_decode($response, TRUE);
        echo '<pre>';
        print_r($object);
        echo '<pre>';
        echo $object['access_token'];
    }

    public function paypal_user_id($token) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.paypal.com/v1/identity/oauth2/userinfo?schema=paypalv1.1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $object = json_decode($result, TRUE);
        echo '<pre>';
        print_r($object);
        echo '<pre>';
    }

    public function paypal_suscriber() {
//       print_r(' <div id="paypal-button-container"></div>
//<script src="https://www.paypal.com/sdk/js?client-id=AYsKfkDo19DuHtP2I7ZamBKgrW6d4_4wc8JIpWQ6k48BxtRw2cy1gn1o6INdYu3d22z4QqnfqP4VgMqv&vault=true" data-sdk-integration-source="button-factory"></script>
//'."<script>
//  paypal.Buttons({
//      style: {
//          shape: 'rect',
//          color: 'gold',
//          layout: 'vertical',
//          label: 'subscribe',
//          
//      },
//      createSubscription: function(data, actions) {
//        return actions.subscription.create({
//          'plan_id': 'P-1VL02682SS657152EL4SCLZI'
//        });
//      },
//      onApprove: function(data, actions) {
//        alert(data.subscriptionID);
//      }
//  }).render('#paypal-button-container');
//</script>");

        print_r('<body>
  <script
       src="https://www.paypal.com/sdk/js?client-id=AYsKfkDo19DuHtP2I7ZamBKgrW6d4_4wc8JIpWQ6k48BxtRw2cy1gn1o6INdYu3d22z4QqnfqP4VgMqv&vault=true">
  </script>

  <div id="paypal-button-container"></div>
' . "
  <script>
    paypal.Buttons().render('#paypal-button-container');
  </script>
</body>");
    }

    //STRIPE FINAL
    //PASO 1 Crear payment_method por cada credit card 
    public function create_payment_method() {
        $this->load->library('Stripe_library');
        $number = '4242424242424242';
        $exp_month = 6;
        $exp_year = 2021;
        $cvc = '314';
        //$name = 'Paolo Maledeto';
        //$zip = '33312';
        $type = 'card';
        $card = [
            'number' => $number,
            'exp_month' => $exp_month,
            'exp_year' => $exp_year,
            'cvc' => $cvc,
        ];
        $response = $this->stripe_library->create_payment_method($type, $card);
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        echo '<br>';
    }

    //PASO 2 Suscribirse
    //2.1 Crear el Cliente con el Metodo de pago seleccionado.
    //EN LA APP Cuando el cliente agrega un payment method se crear el cliente(si ya no existe) y guardar el cliente en tabla????
    public function create_customer() {
        $this->load->library('Stripe_library');
        $email = 'paul@linkstream.com';
        $name = 'Paolo Test';
        $phone = ''; //'1111111111';
        $description = 'My First Test Customer (created for API docs)';
//        $line1 = '2210 Coral Reef Ct';
//        $line2 = '';
//        $city = 'Fort Lauderdale';
//        $state = 'FL';
//        $postal_code = '33312';
//        $country = 'US';
        $address = [
//            'line1' => $line1,
//            'line2' => $line2,
//            'city' => $city,
//            'state' => $state,
//            'postal_code' => $postal_code,
//            'country' => $country
        ];
        $shipping = [
//            'name' => $name,
//            'address' => [
//                'line1' => $line1,
//                'line2' => $line2,
//                'city' => $city,
//                'state' => $state,
//                'postal_code' => $postal_code,
//                'country' => $country
//            ]
        ];
        $payment_method = 'pm_1HKwSBKagYlVQcNzKWfBxuZd';
        $metadata = ['ls_user_id' => '100'];
        $response = $this->stripe_library->create_customer($name, $email, $phone, $address, $shipping, $payment_method, $description, $metadata);
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        echo '<br>';
    }

    //2.2 CREAR SUSCRIPCION CON CUSTOMER ID Y CON PRODUCTO definido previamente en stripe
    //*LOS PRODUCTOS Y PRECIOS DEBEN ESTAR DEFINIDOS EN LA TABLA user_plan(agregar prod id y price id de stripe)
    //PRICES: prod_HumHoGxSvPjFr9 - price_1HKwjDKagYlVQcNzDE7TlNaL - $ 49.99 
    //AL CREAR LA SUSCRIPCION UNA VEZ EL CLIENTE SELECCIONA EL PLAN Y SELECCIONA EL METODO DE PAGO SE OBTIENE EL Subscription ID EXAMPLE (sub_HumMuAwWYqxxkT) guardar en tabla???
    public function create_subscription() {
        $this->load->library('Stripe_library');
        $customer_id = 'cus_Hum2ongahI9IfG';
        $plan = '';
        $price = 'price_1HKwjDKagYlVQcNzDE7TlNaL';

        $default_payment_method = '';
        $response = $this->stripe_library->create_subscription($customer_id, $plan, $price, $default_payment_method);
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        echo '<br>';
    }

    //
    //
    //CREA LA CUENTA EN STRIPE. - GUARDAR LA INFO EN TABLA?
    public function express_account_complex() {
        $this->load->library('Stripe_library');
        $country = 'US';
        $email = 'paolofq@gmail.com';
        $first_name = 'Paul';
        $last_name = 'Ferra';
        $url = 'link.stream/paolo_linkstream';


        $business_type = 'individual'; //individual-company-non_profit-government_entity(US only)
        $account_holder_name = $first_name . ' ' . $last_name;
        $account_holder_type = 'individual'; //company
        $routing_number = '111000000';
        $account_number = '000123456789';
        $external_account = [
            'object' => 'bank_account',
            'country' => $country,
            //'currency' => $currency,
            'account_holder_name' => $account_holder_name,
            'account_holder_type' => $account_holder_type,
            'routing_number' => $routing_number,
            'account_number' => $account_number
        ];
        $business_profile = [
            'url' => $url,
            'name' => $account_holder_name
        ];
        $individual = [
            'first_name' => $first_name,
            'last_name' => $last_name
        ];
        $tos_acceptance = [
            'date' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'], // Assumes you're not using a proxy
        ];
        $response = $this->stripe_library->express_account_complex($country, $email, $external_account, $business_type, $business_profile, $individual, $tos_acceptance);
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        echo '<br>';
    }

    public function express_account() {
        $this->load->library('Stripe_library');
        $country = 'US';
        $email = 'paolofq@gmail.com';
        $response = $this->stripe_library->express_account($country, $email);
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        echo '<br>';
    }

    public function account_link() {
        $this->load->library('Stripe_library');
        $account = 'acct_1HP421DsXWkQeiuJ';
        $response = $this->stripe_library->account_link($account);
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        echo '<br>';
    }

    public function retrieve_account() {
        $this->load->library('Stripe_library');
        $account = 'acct_1HP9vyCs1GUpNRzD';
        $response = $this->stripe_library->retrieve_account($account);
        echo '<pre>';
        print_r($response);
        echo '</pre>';
        echo '<br>';
    }

    //END STRIPE FINAL
    //TEST SES
    public function multi_email() {
//        $data = array();
//        $body = $this->load->view('app/email/email-template', $data, true);
//        $this->general_library->send_ses('Paul Ferra', 'paul@link.stream', 'Streamy', 'noreply@link.stream', 'Email Test', $body);

        $this->load->library('Aws_ses');
        //$this->aws_ses->verify_email();
        //$this->aws_ses->send();
        $sender_email = 'LinkStream <paul@linkstream.com>';
        $reply_to = 'paul@linkstream.com';
        $recipient_emails = ['paolofq@gmail.com'];
        $subject = 'Amazon SES test (AWS SDK for PHP)';
        //$plaintext_body = 'This email was sent with Amazon SES using the AWS SDK for PHP.';
        $html_body = '<h1>AWS Amazon Simple Email Service Test Email</h1>' .
                '<p>This email was sent with <a href="https://aws.amazon.com/ses/">' .
                'Amazon SES</a> using the <a href="https://aws.amazon.com/sdk-for-php/">' .
                'AWS SDK for PHP</a>.</p>';
        foreach ($recipient_emails as $recipient_email) {
            $this->aws_ses->send_email($sender_email, $reply_to, $recipient_email, $subject, $html_body, 'UTF-8');
        }
    }

    public function testing_file() {
        $this->temp_dir = FCPATH . 'tmp';
        $file_name = 'SoundKit.zip';
        $file = file_get_contents($this->temp_dir . '/' . $file_name);
        $src = 'data:' . mime_content_type($this->temp_dir . '/' . $file_name) . ';base64,' . base64_encode($file);



        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "localhost/api.link.stream/v1/audios",
            CURLOPT_URL => "https://api-dev.link.stream/v1/audios",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            //CURLOPT_POSTFIELDS => json_encode("user_id=35&title=Testing%20KIT&track_stems=".$src),
            CURLOPT_HTTPHEADER => array(
                "X-API-KEY: F5CE12A3-27BD-4186-866C-D9D019E85076",
                //"Content-Type: application/x-www-form-urlencoded",
                "Token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoiMzUiLCJ0b2tlbiI6ImVkNWQwZGNhY2IzZjA0YTIzYTQxYTBiNmI5YjNmMGE4OTgzYTYwMzI2MDM0OTZhYTQyZDZiZjg4MzA3OGMzY2ZjNjgxZTk5ZDU1NGM3Y2UyYjRhODY3M2I5ZmI2NmFmNDQ3ZjFlMDkyMTdmYmI1ODZjNmQ4ZjdjNGQ3N2MxMGJhIiwiZXhwaXJlcyI6IjIwMjAtMDktMjIgMjE6MjE6NDIifQ.1kuSnpSwINhnBPoDpn2KkeMQi-68VLMrFCPU8Z-KREs",
                "Authorization: Basic bGlua3N0cmVhbTpMaW5rU3RyZWFtRGV2QDIwMjA=",
                "Cookie: ci_session=50178370578c3265abc802d2aacedf1f1e9b1b44"
            ),
        ));
        $post = array(
            "user_id" => "35",
            "title" => "Testing",
            "track_stems" => $src
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);

        $response = curl_exec($curl);

        curl_close($curl);
        echo '<pre>';
        print_r($response);
        echo '</pre>';
    }

    public function testing_json() {
        $list = [
            [
                'email' => 'a@a.com',
                'phone' => '1111111111',
                'name' => 'Name',
                'birthday' => '01/15',
                'tags' => 'beats, links, videos',
                'email_status' => 'subscribed',
                'sms_status' => 'unsubscribed',
            ],
            [
                'email' => 'b@a.com',
                'phone' => '1111111112',
                'name' => 'Name2',
                'birthday' => '02/15',
                'tags' => 'beats, links, audios',
                'email_status' => 'subscribed',
                'sms_status' => 'subscribed',
            ]
        ];
        $list = [
            'beat', 'tag', 'paolo'
        ];
        print_r(json_encode($list));


        $array = [
            'user_id' => '35',
            'payment' => [
                'exp_month' => '10',
                'exp_year' => '2021',
                'number' => '4242424242424242',
                'cvc' => '314',
                'name' => 'John Doe',
                'address_zip' => '33312',
                'subtotal' => '230',
                'feeCC' => '7.05',
                'feeService' => '4.99',
                'total' => '242.04'
            ],
            'cart' => [
                ['item_id' => '10', 'item_title' => 'Title 10', 'item_amount' => '50', 'item_track_type' => 'beat', 'producer_id' => '30', 'license_id' => '5', 'genre_id' => '3'],
                ['item_id' => '25', 'item_title' => 'Title 25', 'item_amount' => '150', 'item_track_type' => 'kit', 'producer_id' => '30', 'license_id' => '', 'genre_id' => '3'],
                ['item_id' => '67', 'item_title' => 'Title 67', 'item_amount' => '30', 'item_track_type' => 'pack', 'producer_id' => '24', 'license_id' => '', 'genre_id' => '3']
            ]
        ];


        print_r(json_encode($array));
    }

    public function testing_yt() {
//         $this->load->library('google_library');
//         $this->google_library->youtube();
        $this->load->view($this->loc_path . 'example/account');
    }

    public function testing_mp3_to_mp4() {

        $temp_dir = $this->general_library->get_temp_dir();
        echo $temp_dir;
        echo '<br>';
//        $file_name = 'file_example_MP3_1MG.mp3';
        $path = '/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/';
        $path = $temp_dir . '/';
//        $file_mp3 = $path . $file_name;
//        $file_name_mp4 = 'video.avi';
//        $file_name_mp4_2 = 'video_image.avi';
//        $file_mp4 = $path . $file_name_mp4;
//        $file_mp4_2 = $path . $file_name_mp4_2;
//        $image_name = 'download.jpeg';
//        $image = $path . $image_name;
        //$image_input = $path . '01_Image_Test_thumb.png';
        $image_input = $path . '01_Image.png';
        $audio_input = $path . '01_Audio.mp3';
        $video_output = $path . '01_Video_Final.mp4';
        if (file_exists($image_input)) {
            echo 'Exist: ' . $image_input;
            echo '<br>';
        } else {
            echo 'No Exist: ' . $image_input;
            echo '<br>';
            exit;
        }
        if (file_exists($audio_input)) {
            echo 'Exist: ' . $audio_input;
            echo '<br>';
        } else {
            echo 'No Exist: ' . $audio_input;
            echo '<br>';
            exit;
        }
        //IMAGE RESIZE
        //$file_source = $path . $image_input;
        $this->load->library('image_lib');
        $config['image_library'] = 'gd2';
        $config['source_image'] = $image_input;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = 480;
        $config['height'] = 480;
        //ACTION
        $this->image_lib->clear();
        $this->image_lib->initialize($config);
        $this->image_lib->resize();
        //END IMAGE RESIZE
        $image_input_resize = $path . '01_Image_thumb.png';
        if (file_exists($image_input_resize)) {
            echo 'Exist: ' . $image_input_resize;
            echo '<br>';
            //*******REAL OPCION********//
            $time_start = microtime(true);
            $ffmpeg = ($_SERVER['HTTP_HOST'] == 'localhost') ? '/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg' : 'ffmpeg';
            $cmd = $ffmpeg . " -loop 1 -y -i " . $image_input_resize . " -i " . $audio_input . " -shortest " . $video_output . ""; //TIME: 2.6338345011075 Mins, MB:52.2MB
            //$cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -loop 1 -y -i " . $image_input . " -i " . $audio_input . " -s 640x480 -b:v 512k -vcodec mpeg1video -acodec copy -shortest " . $video_image_output . ""; //TIME:2.0092757026354 Mins  Mins, MB: 14.3MB
            echo $cmd;
            exec($cmd, $output);
            echo "<pre>";
            //print_r($output);
            $time_end = microtime(true);
            //dividing with 60 will give the execution time in minutes otherwise seconds
            $execution_time = ($time_end - $time_start) / 60;
            echo '<b>Total Execution Time:</b> ' . $execution_time . ' Mins';
            echo '<br>';
            unlink($image_input_resize);
            if (file_exists($video_output)) {
                echo 'Exist: ' . $video_output;
                echo '<br>';
            } else {
                echo 'No Exist: ' . $video_output;
                echo '<br>';
            }
            exit;
        } else {
            echo 'No Exist: ' . $image_input_resize;
            echo '<br>';
        }
        //
//        
//        
//        
//        $time_start = microtime(true);
//        //$cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -i " . $audio_input . " " . $video_output; 
//        $cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg  -i " . $audio_input . " -c:v copy -c:a copy " . $video_output;
//        exec($cmd, $output);
//        echo "<pre>";
//        print_r($output);
//        $time_end = microtime(true);
//        //dividing with 60 will give the execution time in minutes otherwise seconds
//        $execution_time = ($time_end - $time_start) / 60;
//        echo '<b>Total Execution Time:</b> ' . $execution_time . ' Mins';
//        $time_start = microtime(true);
////        $cmd = "usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -i " . $video_output . " -i " . $image_input . " -filter_complex 'overlay=10:main_h-overlay_h-10' ".$video_image_output;
////        //$cmd = '/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -loop 1 -y -i ' . $image_input . ' -i ' . $video_output . ' -shortest -preset veryfast ' . $video_image_output;
////        exec($cmd, $output);
//        $this->mix_video($audio_input, $image_input, $video_image_output);
////        echo "<pre>";
////        print_r($output);
//        $time_end = microtime(true);
//        //dividing with 60 will give the execution time in minutes otherwise seconds
//        $execution_time = ($time_end - $time_start) / 60;
//        echo '<b>Total Execution Time:</b> ' . $execution_time . ' Mins';
//
//        exit;
        //***************//
//        $time_start = microtime(true);
//        $video_encoded = $path . '01_Video_Final_Encoded.mpg';
//        $cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -i " . $video_output . " -s 640x480 -b:v 512k -vcodec mpeg1video -acodec copy " . $video_encoded;//0.3108925819397 Mins, 15.4MB
//        exec($cmd, $output);
//        echo "<pre>";
//        print_r($output);
//        $time_end = microtime(true);
//        //dividing with 60 will give the execution time in minutes otherwise seconds
//        $execution_time = ($time_end - $time_start) / 60;
//        echo '<b>Total Execution Time:</b> ' . $execution_time . ' Mins';
//        exit;
    }

    public function mix_video($audio_file, $img_file, $video_file) {
        $mix = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -loop -i " . $img_file . " -i " . $audio_file . " -vcodec mpeg4 -s 720x576 -b 10k -r 1 -acodec copy -shortest " . $video_file;
        //exec($mix);
        echo $mix;
    }

    public function youtube_upload_video() {
        $this->load->view($this->loc_path . 'example/account');
    }

//    public function youtube_upload_image() {
//        $this->load->view($this->loc_path . 'example/account_2');
//    }

    public function imagen_resize() {
        $file_name = '01_Image.png';
        $path = '/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/';
        $file_source = $path . $file_name;
        $this->load->library('image_lib');
        $config['image_library'] = 'gd2';
        $config['source_image'] = $file_source;
        $config['create_thumb'] = FALSE;
        $config['new_image'] = $path . 'new_image.png';
        $config['maintain_ratio'] = TRUE;
        $config['width'] = 640;
        $config['height'] = 640;

        $this->image_lib->clear();
        $this->image_lib->initialize($config);
        $this->image_lib->resize();
    }

    public function mp3_to_mp4() {
        echo 'mp3 to mp4';
        echo '<br>';
        $this->load->model(array('User_model', 'Audio_model', 'Marketing_model', 'Video_model'));
        $this->load->library('image_lib');
        $this->temp_dir = $this->general_library->get_temp_dir();
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_coverart = 'Coverart';
        $this->s3_audio = 'Audio';
        //*******REAL OPCION********//
        $time_start = microtime(true);
        $audio = $this->Audio_model->fetch_audio_by_id('379');
        if (!empty($audio)) {
            $path = $this->s3_path . $this->s3_coverart;
            echo $path;
            echo '<br>';
            $image_input = '';
            if (!empty($audio['coverart'])) {
                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $audio['coverart']);
                if (!empty($data_image)) {
                    $image_name = $audio['coverart'];
                    file_put_contents($this->temp_dir . '/' . $image_name, $data_image);
                    //Image_Resize
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $this->temp_dir . '/' . $image_name;
                    $config['create_thumb'] = FALSE;
                    $resize_img = 'tmp_' . $image_name;
                    $config['new_image'] = $this->temp_dir . '/' . $resize_img;
                    $config['maintain_ratio'] = TRUE;
                    $config['width'] = 480;
                    $config['height'] = 480;
                    $this->image_lib->clear();
                    $this->image_lib->initialize($config);
                    $this->image_lib->resize();
                    $image_input = $this->temp_dir . '/' . $resize_img;
                    unlink($this->temp_dir . '/' . $image_name);
                    echo 'IMAGE: ' . $image_input;
                    echo '<br>';
                }
                $path = $this->s3_path . $this->s3_audio;
                $audio_input = '';
                if (!empty($audio['untagged_mp3'])) {
                    $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_mp3']);
                    if (!empty($data_file)) {
                        $audio_input = $this->temp_dir . '/' . $audio['untagged_mp3'];
                        file_put_contents($audio_input, $data_file);
                        //echo 'AUDIO untagged_mp3:' . $audio_input;
                        //echo '<br>';
                    }
                } elseif (!empty($audio['untagged_wav']) && empty($audio_input)) {
                    $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['untagged_wav']);
                    if (!empty($data_file)) {
                        $audio_input = $this->temp_dir . '/' . $audio['untagged_wav'];
                        file_put_contents($audio_input, $data_file);
                        //echo 'AUDIO untagged_wav:' . $audio_input;
                        //echo '<br>';
                    }
                } elseif (!empty($audio['tagged_file']) && empty($audio_input)) {
                    $data_file = $this->aws_s3->s3_read($this->bucket, $path, $audio['tagged_file']);
                    if (!empty($data_file)) {
                        $audio_input = $this->temp_dir . '/' . $audio['tagged_file'];
                        file_put_contents($audio_input, $data_file);
                        //echo 'AUDIO tagged_file:' . $audio_input;
                        //echo '<br>';
                    }
                }
                if (empty($audio_input)) {
                    echo 'Audio File not Found';
                    echo '<br>';
                } else {
                    echo 'AUDIO: ' . $audio_input;
                    echo '<br>';
                    $video_output = $this->temp_dir . '/' . md5(uniqid(rand(), true)) . '.mp4';
                    $ffmpeg = ($_SERVER['HTTP_HOST'] == 'localhost') ? '/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg' : 'ffmpeg';
                    echo 'ffmpeg: ' . $ffmpeg;
                    echo '<br>';
                    $cmd = $ffmpeg . " -loop 1 -y -i " . $image_input . " -i " . $audio_input . " -shortest " . $video_output . "";
                    echo 'cmd: ' . $cmd;
                    echo '<br>';
                    exec($cmd, $output);
                    unlink($image_input);
                    unlink($audio_input);
                    echo 'VIDEO: ' . $video_output;
                    echo '<br>';
                    unlink($video_output);
                    $time_end = microtime(true);
                    //dividing with 60 will give the execution time in minutes otherwise seconds
                    $execution_time = ($time_end - $time_start) / 60;
                    echo '<b>Total Execution Time:</b> ' . $execution_time . ' Mins';
                    echo '<br>';
                }
            } else {
                echo 'No Coverart';
                echo '<br>';
            }
        }
        exit;
        $temp_dir = $this->general_library->get_temp_dir();
        echo $temp_dir;
        echo '<br>';
//        $file_name = 'file_example_MP3_1MG.mp3';
        $path = '/Applications/XAMPP/xamppfiles/htdocs/api.link.stream/tmp/';
        $path = $temp_dir . '/';
//        $file_mp3 = $path . $file_name;
//        $file_name_mp4 = 'video.avi';
//        $file_name_mp4_2 = 'video_image.avi';
//        $file_mp4 = $path . $file_name_mp4;
//        $file_mp4_2 = $path . $file_name_mp4_2;
//        $image_name = 'download.jpeg';
//        $image = $path . $image_name;
        //$image_input = $path . '01_Image_Test_thumb.png';
        $image_input = $path . '01_Image.png';
        $audio_input = $path . '01_Audio.mp3';
        $video_output = $path . '01_Video_Final.mp4';
        if (file_exists($image_input)) {
            echo 'Exist: ' . $image_input;
            echo '<br>';
        } else {
            echo 'No Exist: ' . $image_input;
            echo '<br>';
            exit;
        }
        if (file_exists($audio_input)) {
            echo 'Exist: ' . $audio_input;
            echo '<br>';
        } else {
            echo 'No Exist: ' . $audio_input;
            echo '<br>';
            exit;
        }
        //IMAGE RESIZE
        //$file_source = $path . $image_input;
        $this->load->library('image_lib');
        $config['image_library'] = 'gd2';
        $config['source_image'] = $image_input;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = 480;
        $config['height'] = 480;
        //ACTION
        $this->image_lib->clear();
        $this->image_lib->initialize($config);
        $this->image_lib->resize();
        //END IMAGE RESIZE
        $image_input_resize = $path . '01_Image_thumb.png';
        if (file_exists($image_input_resize)) {
            echo 'Exist: ' . $image_input_resize;
            echo '<br>';
            //*******REAL OPCION********//
            $time_start = microtime(true);
            $ffmpeg = ($_SERVER['HTTP_HOST'] == 'localhost') ? '/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg' : 'ffmpeg';
            $cmd = $ffmpeg . " -loop 1 -y -i " . $image_input_resize . " -i " . $audio_input . " -shortest " . $video_output . ""; //TIME: 2.6338345011075 Mins, MB:52.2MB
            //$cmd = "/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg -loop 1 -y -i " . $image_input . " -i " . $audio_input . " -s 640x480 -b:v 512k -vcodec mpeg1video -acodec copy -shortest " . $video_image_output . ""; //TIME:2.0092757026354 Mins  Mins, MB: 14.3MB
            echo $cmd;
            exec($cmd, $output);
            echo "<pre>";
            //print_r($output);
            $time_end = microtime(true);
            //dividing with 60 will give the execution time in minutes otherwise seconds
            $execution_time = ($time_end - $time_start) / 60;
            echo '<b>Total Execution Time:</b> ' . $execution_time . ' Mins';
            echo '<br>';
            unlink($image_input_resize);
            if (file_exists($video_output)) {
                echo 'Exist: ' . $video_output;
                echo '<br>';
            } else {
                echo 'No Exist: ' . $video_output;
                echo '<br>';
            }
            exit;
        } else {
            echo 'No Exist: ' . $image_input_resize;
            echo '<br>';
        }
    }

    public function ff_version() {
        $ffmpeg = ($_SERVER['HTTP_HOST'] == 'localhost') ? '/usr/local/Cellar/ffmpeg/4.2.3/bin/ffmpeg' : 'ffmpeg';
        $cmd = $ffmpeg . " -version";
        exec($cmd, $output);
        print_r($output);
    }

    public function view_email() {
        $this->load->library('parser');
        $data = [
            'EMAIL_REF_ID' => '100',
            'EMAIL_UTM_SOURCE' => 'email_campaing'
        ];
        // $this->load->view($this->loc_path . 'example/email',$data);
        $this->parser->parse($this->loc_path . 'example/email', $data);
    }

    public function s3_url() {
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_audio = 'Audio';
        $file_name = 'aaa.mp3';
        $destination = $this->s3_path . $this->s3_audio . '/' . $file_name;
        $destination = $file_name;
        $presignedUrl = $this->aws_s3->pre_signed_url($this->bucket, $destination);
        //$url = $this->aws_s3->object_url($this->bucket);
        print_r($presignedUrl);
    }

    public function s3_post() {
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_audio = 'Audio';
        $file_name = md5(uniqid(rand(), true)) . '.jpeg';
        //$file_name = 'te.mp3';
        $destination = $this->s3_path . $this->s3_audio . '/';
        //$destination = $file_name;
        $presignedUrl = $this->aws_s3->pre_signed_post($this->bucket, $file_name, $destination);
        echo '<pre>';
        print_r($presignedUrl);
        echo '</pre>';
    }

    public function file_s3() {




        $curl = curl_init();
        $source = "tmp/test_02.jpeg";
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://s3.us-east-2.amazonaws.com/files.link.stream",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array(
                'acl' => 'public-read',
                'key' => 'Dev/Audio/9a2b33a504f29f2e7d7dbb96d68b4333.jpeg',
                'X-Amz-Credential' => 'AKIAXBDC73PHUL3JUCGP/20201015/us-east-2/s3/aws4_request',
                'X-Amz-Algorithm' => 'AWS4-HMAC-SHA256', 'X-Amz-Date' => '20201015T183840Z',
                'Policy' => 'eyJleHBpcmF0aW9uIjoiMjAyMC0xMC0xNVQxOTowODo0MFoiLCJjb25kaXRpb25zIjpbeyJhY2wiOiJwdWJsaWMtcmVhZCJ9LHsiYnVja2V0IjoiZmlsZXMubGluay5zdHJlYW0ifSxbInN0YXJ0cy13aXRoIiwiJGtleSIsIkRldlwvQXVkaW9cLzlhMmIzM2E1MDRmMjlmMmU3ZDdkYmI5NmQ2OGI0MzMzLmpwZWciXSx7IlgtQW16LURhdGUiOiIyMDIwMTAxNVQxODM4NDBaIn0seyJYLUFtei1DcmVkZW50aWFsIjoiQUtJQVhCREM3M1BIVUwzSlVDR1BcLzIwMjAxMDE1XC91cy1lYXN0LTJcL3MzXC9hd3M0X3JlcXVlc3QifSx7IlgtQW16LUFsZ29yaXRobSI6IkFXUzQtSE1BQy1TSEEyNTYifV19',
                'X-Amz-Signature' => 'e0adff6b50e4305ddec82ca9a8ca0204c27689495c86fa67fe7740a79d9b0d15',
                'file' => file_get_contents($source)
            )
//                'file' => new CURLFILE($source)),
//            CURLOPT_HTTPHEADER => array(
//                "Content-Type: multipart/form-data"
//            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
    
    public function unique_id(){
        echo uniqid('6780'.'600000');echo '<br>';echo uniqid('6780'.'600000');
    }

}
