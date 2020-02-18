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

    public function email_test() {
        $this->general_library->send_ses('Paul Ferra', 'paul@link.stream', 'Streamy', 'noreply@link.stream', 'Email Test', 'Email Test: Body');
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
    public function customize() {
        if ($this->input->cookie($this->general_library->ses_name) != '') {
            $user = $this->general_library->get_cookie();
            $register_user = $this->User_model->fetch_user_by_search(array('id' => $user['id']));
            $data = array();
            $data['user'] = $register_user;
            $data['order'] = $this->Streamy_model->fetch_content_order($register_user['id']);
            $this->load->view($this->loc_path . 'customize', $data);
        } else {
            redirect($this->loc_url . '/login', 'location', 302);
        }
    }

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
            $this->aws_pinpoint->send($phone, "Welcome! Let's bring social media and streaming music together in 2020. Thanks for registering, we are giving free Pro accounts to all early birds ;) Stay tuned in, we will notify you as soon as you can start your stream! Reply “STOP” to cancel this reminder.");
        }
        $this->Streamy_model->insert_early_access(array('email' => $email, 'phone' => $phone));
        echo json_encode(array('status' => 'Success'));
    }

    public function email_coming_soon() {
        $data = array();
        $this->load->view('email/email_coming_soon', $data);
    }

    public function email_register() {
        $data = array();
        $this->load->view('email/email_register', $data);
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

    //END NEW FUNCTIONS 02/2020
}
