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
    private $server_url;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model(array('User_model', 'Audio_model', 'Album_model', 'Marketing_model', 'License_model', 'Visitor_model'));
        //Libraries
        $this->load->library(array('Instagram_api', 'aws_s3', 'Aws_pinpoint', 'Stripe_library'));
        //Helpers
        $this->load->helper(array('email'));
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'prod/' : 'dev/';
        $this->s3_folder = 'profile';
        $this->s3_coverart = 'coverart';
        $this->temp_dir = $this->general_library->get_temp_dir();
        $this->server_url = 'https://s3.us-east-2.amazonaws.com/files.link.stream/';
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

    private function user_account_clean($user) {
        unset($user['password']);
        unset($user['email_confirmed']);
        unset($user['status_id']);
        unset($user['platform']);
        unset($user['platform_id']);
        unset($user['platform_token']);
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
                if (!empty($this->put('type'))) {
                    $register_user['type'] = $this->put('type');
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
            $this->error = 'Type ' . $type . ' is not allowed, only username,email or url are allowed as type';
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

//    
//    
    //MULTI STORE
    public function login_new_post() {
        // Get the post data
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        // Validate the post data
        if (!empty($email) && !empty($password)) {
            // Check if any user exists with the given credentials
            $password_e = $this->general_library->encrypt_txt($password);
            //Check Email And User
            $register_user = $this->User_model->fetch_user_by_search_store(array('email' => $email, 'password' => $password_e));
            if (!empty($register_user)) {
                if ($register_user['status_id'] == 1) {
                    $register_user['store'] = [];
                    $stores = $this->User_model->fetch_store_by_id($register_user['id']);
                    $path = $this->s3_path . $this->s3_folder;
                    foreach ($stores as $store) {
                        $store['token'] = $this->User_model->create_token($store['id']);
                        //Avatar & Banner            
                        $store['data_image'] = '';
                        $store['data_banner'] = '';
                        if (!empty($store['image'])) {
                            $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                            $store['data_image'] = $final_url;
                        } else {
                            $store['image'] = 'LS_avatar.png';
                            $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                            $store['data_image'] = $final_url;
                        }
                        if (!empty($store['banner'])) {
                            $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['banner']);
                            $store['data_banner'] = $final_url;
                        }
                        $register_user['store'][] = $store;
                    }

//                    $register_user['store'] = $this->User_model->fetch_store_by_id($register_user['id']);
                    //$register_user['token'] = $this->User_model->create_token($register_user['store'][0]['id']);
                    //$register_user['token'] = $this->User_model->create_token($register_user['store'][0]['id']);
                    $register_user['token'] = $this->User_model->create_token($register_user['id']);

                    $user_response = $this->user_account_clean($register_user);
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

    public function stores_get($user_id) {
        if (!empty($user_id)) {
//            if (!$this->general_library->header_token($user_id)) {
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//            }
            $stores = $this->User_model->fetch_store_by_id($user_id);
            $response = [];
//            $path = $this->s3_path . $this->s3_coverart;
            $path = $this->s3_path . $this->s3_folder;
            foreach ($stores as $store) {
                $store['token'] = $this->User_model->create_token($store['id']);
                //Avatar & Banner            
                $store['data_image'] = '';
                $store['data_banner'] = '';
                if (!empty($store['image'])) {
                    $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                    $store['data_image'] = $final_url;
                } else {
                    $store['image'] = 'LS_avatar.png';
                    $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                    $store['data_image'] = $final_url;
                }
                if (!empty($store['banner'])) {
                    $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['banner']);
                    $store['data_banner'] = $final_url;
                }
                $response[] = $store;
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $response), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID.';
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
        $user_name = !empty($this->input->post('user_name')) ? strip_tags($this->input->post('user_name')) : null;
        $type = !empty($this->input->post('type')) ? strip_tags($this->input->post('type')) : 'producer';
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
                $user['type'] = $type;
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

    public function registration_new_post() {
        // Get the post data
        $email = strip_tags($this->input->post('email'));
        $password = $this->input->post('password');
        $user_name = !empty($this->input->post('user_name')) ? strip_tags($this->input->post('user_name')) : null;
        $type = !empty($this->input->post('type')) ? strip_tags($this->input->post('type')) : 'producer';
        if (!empty($email) && !empty($user_name) && !empty($password)) {
            //Check Email And User
            $register_user = $this->User_model->fetch_user_by_search_store(array('email' => $email));
            if (empty($register_user)) {

                $user_account = [];
                $user_store = [];
                $user_name = $this->generate_username($user_name);
                $encrypt_pass = $this->general_library->encrypt_txt($password);
                //ACCOUNT
                $user_account['user_name'] = strtolower(str_replace(' ', '', $user_name));
                $user_account['email'] = $email;
                $user_account['password'] = $encrypt_pass;
                $user_account['plan_id'] = '1';
                $user_account['status_id'] = '1';
                $user_account['type'] = $type;
                $user_account['platform'] = 'LinkStream';
                $user_account_id = $this->User_model->insert_user_account($user_account);
                $user_account['id'] = $user_account_id;
                //STORE
                $user_store['user_account_id'] = $user_account_id;
                $user_store['user_name'] = $user_store['display_name'] = $user_store['url'] = strtolower(str_replace(' ', '', $user_name));
                $user_store['email'] = $email;
                $user_store['password'] = $encrypt_pass;
                $user_store['plan_id'] = '1';
                $user_store['status_id'] = '1';
                $user_store['type'] = $type;
                $user_store['platform'] = 'LinkStream';
                $user_store['id'] = $this->User_model->insert_user($user_store);
                $this->User_model->insert_user_log(array('user_id' => $user_account['id'], 'event' => 'Registered'));
                $this->register_email($user_store);
                $user_account['token'] = $this->User_model->create_token($user_account['id']);
                $user_account['store'] = [];
                $stores = $this->User_model->fetch_store_by_id($user_account['id']);
                $path = $this->s3_path . $this->s3_folder;
                foreach ($stores as $store) {
                    $store['token'] = $this->User_model->create_token($store['id']);
                    //Avatar & Banner            
                    $store['data_image'] = '';
                    $store['data_banner'] = '';
                    if (!empty($store['image'])) {
                        $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                        $store['data_image'] = $final_url;
                    } else {
                        $store['image'] = 'LS_avatar.png';
                        $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                        $store['data_image'] = $final_url;
                    }
                    if (!empty($store['banner'])) {
                        $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['banner']);
                        $store['data_banner'] = $final_url;
                    }
                    $user_account['store'][] = $store;
                }
                $user_response = $this->user_clean($user_account);
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

//    public function registration_new_post() {
//        // Get the post data
//        $email = strip_tags($this->input->post('email'));
//        $password = $this->input->post('password');
//        $user_name = !empty($this->input->post('user_name')) ? strip_tags($this->input->post('user_name')) : null;
//        $type = !empty($this->input->post('type')) ? strip_tags($this->input->post('type')) : 'producer';
//        if (!empty($email) && !empty($user_name) && !empty($password)) {
//            //Check Email And User
//            $register_user = $this->User_model->fetch_user_by_search_store(array('email' => $email));
//            if (empty($register_user)) {
//                $user_name = $this->generate_username($user_name);
//                $user = array();
//                $user['user_name'] = $user['display_name'] = $user['url'] = strtolower(str_replace(' ', '', $user_name));
//                $user['email'] = $email;
//                $user['password'] = $this->general_library->encrypt_txt($password);
//                $user['plan_id'] = '1';
//                $user['status_id'] = '3';
//                $user['type'] = $type;
//                $user['platform'] = 'LinkStream';
//                $user['id'] = $this->User_model->insert_user($user);
//                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
//                $this->register_email($user);
//                $user['token'] = $this->User_model->create_token($user['id']);
//                $user_response = $this->user_clean($user);
//                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
//            } else {
//                $this->error = 'The given email already exists.';
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            }
//        } else {
//            $this->error = 'Provide complete user info to add';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }

    private function register_email($user) {
        $email_e = $this->general_library->urlsafe_b64encode($user['email']);
        $id_e = $this->general_library->urlsafe_b64encode($user['id']);
        $base = (ENV == 'dev' || ENV == 'staging') ? 'https://dev-link-vue.link.stream' : 'https://linkstream.com';
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
                    $this->User_model->update_user_account($register_user['user_account_id'], array('email_confirmed' => '1', 'status_id' => '1'));
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

//    public function instagram_post() {
//        $code = $this->input->post('code');
//        $redirect_url = $this->input->post('redirect_url');
//        $auth_response = $this->instagram_api->authorize_2($code, $redirect_url);
//        if (!empty($auth_response->error_type)) {
//            $this->error = $auth_response->code . ':' . $auth_response->error_message;
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        } else {
//            $access_token = $auth_response->access_token;
//            $user_id = $auth_response->user_id;
//            $instagram_user = $this->instagram_api->getUserInfo($user_id, 'id,username,account_type,media_count', $access_token);
//            //Check User
//            $register_user = $this->User_model->fetch_user_by_search(array('platform' => 'IG', 'platform_id' => $user_id));
//            if (empty($register_user)) {
//                //Create Account
//                $user = array();
//                $user['user_name'] = $user['display_name'] = $user['url'] = (!empty($instagram_user->username)) ? $this->generate_username($instagram_user->username) : $this->generate_username();
//                $user['email'] = '';
//                $user['plan_id'] = '1';
//                $user['platform'] = 'IG';
//                $user['platform_id'] = $user_id;
//                $user['platform_token'] = $access_token;
//                $instagram_avatar = (!empty($instagram_user->username)) ? $this->instagram_get_photo($instagram_user->username) : '';
//                $user['image'] = '';
//                if (!empty($instagram_avatar)) {
//                    $content = file_get_contents($instagram_avatar);
//                    $image_name = md5(uniqid(rand(), true)) . '.png';
//                    //upload cropped image to server 
//                    file_put_contents($this->temp_dir . '/' . $image_name, $content);
//                    //SAVE S3
//                    $this->s3_push($image_name);
//                    $user['image'] = $image_name;
//                }
//                $user['status_id'] = '1';
//                $user['id'] = $this->User_model->insert_user($user);
//                $user['token'] = $this->User_model->create_token($user['id']);
//                $user_response = $this->user_clean($user);
//                $this->User_model->insert_user_log(array('user_id' => $user['id'], 'event' => 'Registered'));
//                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
//            } else {
//                $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Logged in'));
//                $register_user['token'] = $this->User_model->create_token($register_user['id']);
//                $user_response = $this->user_clean($register_user);
//                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
//            }
//        }
//    }
//
//    private function instagram_get_photo($user_name) {
//        $getValues = file_get_contents('https://www.instagram.com/' . $user_name . '/?__a=1');
//        $jsonObj = json_decode($getValues, TRUE);
//        $photoURL = $jsonObj["graphql"]["user"]["profile_pic_url_hd"];
//        return $photoURL;
//    }
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
        $type = !empty($this->input->post('type')) ? strip_tags($this->input->post('type')) : 'producer';
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
                $user['type'] = $type;
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

    public function google_new_post() {
        //$this->output->set_content_type('application/json');
        $token = $this->input->post('platform_token');
        $type = !empty($this->input->post('type')) ? strip_tags($this->input->post('type')) : 'producer';
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
            $register_user = $this->User_model->fetch_user_by_search_store(array('platform' => 'Google', 'platform_id' => $token_info->sub));
            //$register_user = $this->User_model->fetch_user_by_search_store(array('email' => $email, 'password' => $password_e));
            if (empty($register_user)) {
                //Create Account
                $user_account = [];
                $user_store = [];
                $user_name = (!empty($token_info->name)) ? $this->generate_username($token_info->name) : $this->generate_username();
                //ACCOUNT
                $user_account['user_name'] = $user_name;
                $user_account['first_name'] = (!empty($token_info->given_name)) ? $token_info->given_name : '';
                $user_account['last_name'] = (!empty($token_info->family_name)) ? $token_info->family_name : '';
                $user_account['email'] = (!empty($token_info->email)) ? $token_info->email : '';
                $user_account['email_confirmed'] = '1';
                $user_account['status_id'] = '1';
                $user_account['plan_id'] = '1';
                $user_account['type'] = $type;
                $user_account['platform'] = 'Google';
                $user_account['platform_id'] = $token_info->sub;
                $user_account['platform_token'] = $token;
                //STORE
                $user_store['user_name'] = $user_store['display_name'] = $user_store['url'] = $user_name;
                $user_store['first_name'] = (!empty($token_info->given_name)) ? $token_info->given_name : '';
                $user_store['last_name'] = (!empty($token_info->family_name)) ? $token_info->family_name : '';
                $user_store['email'] = (!empty($token_info->email)) ? $token_info->email : '';
                $user_store['plan_id'] = '1';
                $user_store['type'] = $type;
                $user_store['platform'] = 'Google';
                $user_store['platform_id'] = $token_info->sub;
                $user_store['platform_token'] = $token;
                $user_store['image'] = '';
                if (!empty($token_info->picture)) {
                    $content = file_get_contents($token_info->picture);
                    $image_name = md5(uniqid(rand(), true)) . '.png';
                    //upload cropped image to server 
                    file_put_contents($this->temp_dir . '/' . $image_name, $content);
                    //SAVE S3
                    $this->s3_push($image_name);
                    $user_store['image'] = $image_name;
                }
                $user_store['status_id'] = '1';
                $user_store['email_confirmed'] = '1';

                $user_account_id = $this->User_model->insert_user_account($user_account);
                $user_account['id'] = $user_account_id;

                $user_store['user_account_id'] = $user_account_id;

                $user_store['id'] = $this->User_model->insert_user($user_store);
                $user_account['token'] = $this->User_model->create_token($user_account['id']);
                $this->User_model->insert_user_log(array('user_id' => $user_account['id'], 'event' => 'Registered'));
                $user_account['store'] = [];
                $stores = $this->User_model->fetch_store_by_id($user_account['id']);
                $path = $this->s3_path . $this->s3_folder;
                foreach ($stores as $store) {
                    $store['token'] = $this->User_model->create_token($store['id']);
                    //Avatar & Banner            
                    $store['data_image'] = '';
                    $store['data_banner'] = '';
                    if (!empty($store['image'])) {
                        $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                        $store['data_image'] = $final_url;
                    } else {
                        $store['image'] = 'LS_avatar.png';
                        $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                        $store['data_image'] = $final_url;
                    }
                    if (!empty($store['banner'])) {
                        $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['banner']);
                        $store['data_banner'] = $final_url;
                    }
                    $user_account['store'][] = $store;
                }
                $user_response = $this->user_clean($user_account);
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_response), RestController::HTTP_OK);
            } else {
                $this->User_model->insert_user_log(array('user_id' => $register_user['id'], 'event' => 'Logged in'));
                $register_user['token'] = $this->User_model->create_token($register_user['id']);
                $register_user['store'] = [];
                $stores = $this->User_model->fetch_store_by_id($register_user['id']);
                $path = $this->s3_path . $this->s3_folder;
                foreach ($stores as $store) {
                    $store['token'] = $this->User_model->create_token($store['id']);
                    //Avatar & Banner            
                    $store['data_image'] = '';
                    $store['data_banner'] = '';
                    if (!empty($store['image'])) {
                        $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                        $store['data_image'] = $final_url;
                    } else {
                        $store['image'] = 'LS_avatar.png';
                        $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['image']);
                        $store['data_image'] = $final_url;
                    }
                    if (!empty($store['banner'])) {
                        $final_url = $this->general_library->encode_image_url($store['id'], $this->s3_path . $this->s3_folder . '/' . $store['banner']);
                        $store['data_banner'] = $final_url;
                    }
                    $register_user['store'][] = $store;
                }
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
                $invoice['created_at'] = $this->general_library->gmt_to_est($invoice['created_at']);
                $details = $this->User_model->fetch_user_purchases_details_2($invoice['id']);
                $response_details = [];
                foreach ($details as $detail) {
                    $item_id = $detail['item_id'];
                    $item_track_type = $detail['item_track_type'];
                    if ($item_track_type == 'beat' || $item_track_type == 'kit') {
                        $audio = $this->Audio_model->fetch_audio_by_id($item_id);
                    } else {
                        $audio = $this->Album_model->fetch_album_by_id($item_id);
                    }
                    $detail['data_image'] = '';
                    if (!empty($audio['coverart'])) {
                        $detail['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
                    }
                    $detail['download_url'] = $this->general_library->encode_download_url($detail['invoice_id'], $user_id, $detail['item_id'], $detail['producer_id'], $detail['id']);
                    $response_details[] = $detail;
                }
                $invoice['details'] = $response_details;
                $invoice['amount'] = $invoice['total'];
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
            $type = 'card';
            $card = [
                'number' => $cc_number,
                'exp_month' => $exp_month,
                'exp_year' => $exp_year,
                'cvc' => $cvv,
            ];
            $payment_methods = $this->User_model->fetch_payment_method_by_user_id($user_id);
            if (empty($payment_methods)) {
                $payment_method['is_default'] = 1;
            }
            $response = $this->stripe_library->create_payment_method($type, $card);
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
                //STRIPE CUSTOMER
                $customer_response = $this->create_stripe_customer($user_id);
                //
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The payment method has been created successfully.', 'customer' => $customer_response), RestController::HTTP_OK);
            } else {
                $this->error = $response['error'];
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
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

    public function notification_get($user_id = null) {
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $notification = $this->User_model->fetch_notification_by_user_id($user_id);
            //$response = [];
            if (empty($notification)) {
                $st_user_notification = [
                    'user_id' => $user_id,
                ];
                $this->User_model->insert_notification($st_user_notification);
                $notification = $this->User_model->fetch_notification_by_user_id($user_id);
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $notification), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function notification_put($id = null) {
        if (!empty($id)) {
            $notification = $this->User_model->fetch_notification_by_id($id);
            if (!empty($notification)) {
                if (!$this->general_library->header_token($notification['user_id'])) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                if ($this->put('sales_email') !== null) {
                    $notification['sales_email'] = (!empty($this->put('sales_email'))) ? '1' : '0';
                }
                if ($this->put('sales_push') !== null) {
                    $notification['sales_push'] = (!empty($this->put('sales_push'))) ? '1' : '0';
                }
                if ($this->put('follows_email') !== null) {
                    $notification['follows_email'] = (!empty($this->put('follows_email'))) ? '1' : '0';
                }
                if ($this->put('follows_push') !== null) {
                    $notification['follows_push'] = (!empty($this->put('follows_push'))) ? '1' : '0';
                }
                if ($this->put('likes_email') !== null) {
                    $notification['likes_email'] = (!empty($this->put('likes_email'))) ? '1' : '0';
                }
                if ($this->put('likes_push') !== null) {
                    $notification['likes_push'] = (!empty($this->put('likes_push'))) ? '1' : '0';
                }
                if ($this->put('reposts_email') !== null) {
                    $notification['reposts_email'] = (!empty($this->put('reposts_email'))) ? '1' : '0';
                }
                if ($this->put('reposts_push') !== null) {
                    $notification['reposts_push'] = (!empty($this->put('reposts_push'))) ? '1' : '0';
                }
                if ($this->put('collaborations_email') !== null) {
                    $notification['collaborations_email'] = (!empty($this->put('collaborations_email'))) ? '1' : '0';
                }
                if ($this->put('collaborations_push') !== null) {
                    $notification['collaborations_push'] = (!empty($this->put('collaborations_push'))) ? '1' : '0';
                }
                if ($this->put('ls_features_email') !== null) {
                    $notification['ls_features_email'] = (!empty($this->put('ls_features_email'))) ? '1' : '0';
                }
                if ($this->put('ls_features_push') !== null) {
                    $notification['ls_features_push'] = (!empty($this->put('ls_features_push'))) ? '1' : '0';
                }
                if ($this->put('surveys_email') !== null) {
                    $notification['surveys_email'] = (!empty($this->put('surveys_email'))) ? '1' : '0';
                }
                if ($this->put('surveys_push') !== null) {
                    $notification['surveys_push'] = (!empty($this->put('surveys_push'))) ? '1' : '0';
                }
                if ($this->put('ls_newsletter_email') !== null) {
                    $notification['ls_newsletter_email'] = (!empty($this->put('ls_newsletter_email'))) ? '1' : '0';
                }
                $this->User_model->update_notification($id, $notification);
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Notification info has been updated successfully.', 'data' => $notification), RestController::HTTP_OK);
            } else {
                $this->error = 'NotificationNot Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Notification ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    private function create_stripe_customer($user_id) {
        $response = [];
        $register_user = $this->User_model->fetch_user_by_id($user_id);
        $email = $register_user['email'];
        $name = $register_user['first_name'] . ' ' . $register_user['last_name'];
        $phone = '';
        $description = 'LinkStream Customer ' . $user_id;
        $address = [];
        $shipping = [];
        $metadata = ['linkstream_user_id' => $user_id];
        $payment_method_key = '';
        $payment_methods = $this->User_model->fetch_payment_method_by_user_id($user_id);
        if (!empty($payment_methods)) {
            foreach ($payment_methods as $cc) {
                if ($cc['is_default']) {
                    $payment_method_key = $cc['payment_method_key'];
                }
            }
            $subscription_account = $this->User_model->fetch_user_subscriptions_by_user_id($user_id, 'Stripe');
            if (empty($subscription_account)) {
                $customer = $this->stripe_library->create_customer($name, $email, $phone, $address, $shipping, $payment_method_key, $description, $metadata);
                if ($customer['status']) {
                    $response = ['status' => true, 'customer_id' => $customer['customer_id']];
                    $this->User_model->insert_user_subscriptions(['customer_id' => $customer['customer_id'], 'user_id' => $user_id, 'processor' => 'Stripe', 'status' => 'ACTIVE']);
                    //$this->User_model->update_subscriptions_by_user_id($user_id, ['customer_id' => $customer['customer_id']]);
                } else {
                    $response = ['status' => false, 'msg' => $customer['error']];
                }
            } else {
                $response = ['status' => false, 'msg' => 'Exist Subscription Account'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'No Payment Method'];
        }
        return $response;
    }

    //STRIPE CONNECT//
    //

    public function connect_stripe_account_post() {
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $debug = (!empty($this->input->post('debug'))) ? $this->input->post('debug') : FALSE;
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $register_user = $this->User_model->fetch_user_by_id($user_id);
            if (!empty($register_user)) {
                $temp_stripe_account = $this->User_model->fetch_stripe_account_by_user_id($user_id, 'Stripe');
                if (!empty($temp_stripe_account) && $temp_stripe_account['status'] == 'ACTIVE') {
                    $this->error = 'Stripe Account Already Created and Activated';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                } elseif (!empty($temp_stripe_account) && $temp_stripe_account['status'] == 'APPROVED') {
                    $this->error = 'Stripe Account Already Created and Approved, no Active';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                } elseif (!empty($temp_stripe_account) && $temp_stripe_account['status'] == 'PENDING') {
                    $account_id = $temp_stripe_account['account_id'];
                } else {
                    //CREATE ACCOUNT
                    $stripe_response = $this->stripe_library->express_account($register_user['country'], $register_user['email']);
                    if ($stripe_response['status']) {
                        $account_id = $stripe_response['account_id'];
                        //Guardar Account ID **
                        $this->User_model->insert_user_connect(['user_id' => $user_id, 'processor' => 'Stripe', 'account_id' => $account_id]);
                    } else {
                        $this->error = $stripe_response['error'];
                        $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                    }
                }
                //CREATE LINK
                if (!empty($account_id)) {
                    //Crear Link
                    $stripe_response = $this->stripe_library->account_link($account_id, $debug);
                    if ($stripe_response['status']) {
                        $account_url = $stripe_response['account_url'];
                        $this->response(array('status' => 'success', 'env' => ENV, 'account_id' => $account_id, 'account_url' => $account_url), RestController::HTTP_OK);
                    } else {
                        $this->error = $stripe_response['error'];
                        $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                    }
                } else {
                    $this->error = 'Stripe Error - Account Not Found.';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function confirm_stripe_account_post() {
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $account_id = (!empty($this->input->post('account_id'))) ? $this->input->post('account_id') : '';
        if (!empty($user_id) || !empty($account_id)) {
            //LLAMAR API PARA CONFIRMAR ACCOUNT.
            $stripe_response = $this->stripe_library->retrieve_account($account_id);
            if ($stripe_response['status']) {
                if ($stripe_response['payouts_enabled']) {
                    //UPDATE Account ID
                    $this->User_model->update_connect_by_user_id($user_id, $account_id, ['payouts_enabled' => 1, 'status' => 'ACTIVE']);
                } else {
                    //UPDATE Account ID
                    $this->User_model->update_connect_by_user_id($user_id, $account_id, ['payouts_enabled' => 1, 'status' => 'APPROVED']);
                }
                //LOGIN LINK
                //$login_links = $stripe_response['login_links'];
                $stripe_login = $this->stripe_library->retrieve_login($account_id);
                if ($stripe_login['status']) {
                    $stripe_login_url = $stripe_login['url'];
                    $this->User_model->update_connect_by_user_id($user_id, $account_id, ['login_url' => $stripe_login_url]);
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'account_id' => $account_id, 'payouts_enabled' => $stripe_response['payouts_enabled'], 'login_links' => $stripe_login_url), RestController::HTTP_OK);
            } else {
                $this->error = $stripe_response['error'];
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID and/or Account ID';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function decline_stripe_account_post() {
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $account_id = (!empty($this->input->post('account_id'))) ? $this->input->post('account_id') : '';
        if (!empty($user_id) || !empty($account_id)) {

            //UPDATE Account ID
            $this->User_model->update_connect_by_user_id($user_id, $account_id, ['status' => 'DECLINED']);
            $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID and/or Account ID';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function decline_stripe_account_delete($user_id = null, $account_id = null) {
//        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
//        $account_id = (!empty($this->input->post('account_id'))) ? $this->input->post('account_id') : '';
        if (!empty($user_id) || !empty($account_id)) {
            //UPDATE Account ID
            $this->User_model->update_connect_by_user_id($user_id, $account_id, ['status' => 'DELETED']);
            $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID and/or Account ID';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function stripe_account_get($user_id = null) {
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                //$this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $temp_stripe_account = $this->User_model->fetch_stripe_account_by_user_id($user_id, 'Stripe');
            if (!empty($temp_stripe_account) && $temp_stripe_account['status'] == 'APPROVED') {
                $account_id = $temp_stripe_account['account_id'];
                //LLAMAR API PARA CONFIRMAR ACCOUNT.
                $stripe_response = $this->stripe_library->retrieve_account($account_id);
                if ($stripe_response['status']) {
                    if ($stripe_response['payouts_enabled']) {
                        //UPDATE Account ID
                        $this->User_model->update_connect_by_user_id($user_id, $account_id, ['payouts_enabled' => 1, 'status' => 'ACTIVE']);
                        $temp_stripe_account['payouts_enabled'] = 1;
                        $temp_stripe_account['status'] = 'ACTIVE';
                    }
                } else {
                    $this->error = $stripe_response['error'];
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $temp_stripe_account), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    //END STRIPE CONNECT//
    //
    //
    public function dashboard_get($user_id = null) {
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                //$this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $register_user = $this->User_model->fetch_user_by_id($user_id);
            if (!empty($register_user)) {
                // $user_response = $this->user_clean($register_user);
                $data = [];
                $data['beat'] = false;
                $data['store'] = false;
                $data['campaign'] = false;
                $data['email_confirmed'] = ($register_user['email_confirmed']) ? true : false; //resend_email_confirm_post en caso necesite comfirmar.
                if (!empty($register_user['image']) && !empty($register_user['banner']) && !empty($register_user['bio'])) {
                    $data['store'] = true;
                }
                $beats = $this->Audio_model->fetch_audio_by_search(['user_id' => $user_id], 1, 0);
                if (!empty($beats)) {
                    $data['beat'] = true;
                }
                $campaign = $this->Marketing_model->fetch_messages_by_user_id($user_id, '', false, 1, 0);
                if (!empty($campaign)) {
                    $data['campaign'] = true;
                }

                $date = date('Y-m-d 00:00:00', strtotime(date('Y-m-d 00:00:00', strtotime('-7 days'))));
                $data['plays'] = $this->Audio_model->fetch_audio_log_count($user_id, 'PLAY', $date);
                $data['free_downloads'] = $this->Audio_model->fetch_audio_log_count($user_id, 'FREE_DOWNLOAD', $date);
                $sales = $this->Audio_model->fetch_sales_report($user_id, $date);
                $data['sales_count'] = (!empty($sales['Count'])) ? (int) $sales['Count'] : 0;
                $data['sales_amount'] = (!empty($sales['Total'])) ? (float) $sales['Total'] : 0;
                $data['conversion'] = 0;
                if ($data['plays'] > 0) {
                    $data['conversion'] = number_format(($data['sales_count'] * 100 / $data['plays']), 2);
                }
                $top_5 = $this->Audio_model->fetch_top_played($user_id, $date, 5);
                $data['top_5'] = [];
                foreach ($top_5 as $item) {
                    $item['image_url'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $item['coverart'];
                    //NEW ENCRYPTED IMAGE
                    $final_url = $this->general_library->encode_image_url($user_id, $this->s3_path . $this->s3_coverart . '/' . $item['coverart']);
                    $item['image_url'] = $final_url;
                    //END ENCRYPTED IMAGE
                    $data['top_5'][] = $item;
                }
                //ACTIVITY LOG
                $activity_5 = $this->Audio_model->fetch_top_activity($user_id, $date, 5);
                $data['activity'] = [];
                foreach ($activity_5 as $item) {

                    if (!empty($item['ref_user_id'])) {
                        $item['image_url'] = $this->server_url . $this->s3_path . $this->s3_folder . '/' . $item['image'];
                        //NEW ENCRYPTED IMAGE
                        $final_url = $this->general_library->encode_image_url($user_id, $this->s3_path . $this->s3_folder . '/' . $item['image']);
                        $item['image_url'] = $final_url;
                        //END ENCRYPTED IMAGE 
                    } else {
                        $item['image'] = 'LS_avatar.png';
                        $item['image_url'] = $this->server_url . $this->s3_path . $this->s3_folder . '/LS_avatar.png';
                        //NEW ENCRYPTED IMAGE
                        $final_url = $this->general_library->encode_image_url($user_id, $this->s3_path . $this->s3_folder . '/' . $item['image']);
                        $item['image_url'] = $final_url;
                        //END ENCRYPTED IMAGE
                        $item['display_name'] = 'LS Visitor ';
                    }
                    $data['activity'][] = $item;
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $data), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function analytics_get($user_id = null, $days = 7) {
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $register_user = $this->User_model->fetch_user_by_id($user_id);
            if (!empty($register_user)) {
                // $user_response = $this->user_clean($register_user);
                $data = [];
//                $data['beat'] = false;
//                $data['store'] = false;
//                $data['campaign'] = false;
//                $data['email_confirmed'] = ($register_user['email_confirmed']) ? true : false; //resend_email_confirm_post en caso necesite comfirmar.
//                if (!empty($register_user['image']) && !empty($register_user['banner']) && !empty($register_user['bio'])) {
//                    $data['store'] = true;
//                }
//                $beats = $this->Audio_model->fetch_audio_by_search(['user_id' => $user_id], 1, 0);
//                if (!empty($beats)) {
//                    $data['beat'] = true;
//                }
//                $campaign = $this->Marketing_model->fetch_messages_by_user_id($user_id, '', false, 1, 0);
//                if (!empty($campaign)) {
//                    $data['campaign'] = true;
//                }


                $date = date('Y-m-d 00:00:00', strtotime(date('Y-m-d 00:00:00', strtotime('-' . $days . ' days'))));
                $data['plays'] = $this->Audio_model->fetch_audio_log_count($user_id, 'PLAY', $date);
                $data['free_downloads'] = $this->Audio_model->fetch_audio_log_count($user_id, 'FREE_DOWNLOAD', $date);
                $sales = $this->Audio_model->fetch_sales_report($user_id, $date);
                $data['sales_count'] = (!empty($sales['Count'])) ? (int) $sales['Count'] : 0;
                $data['sales_amount'] = (!empty($sales['Total'])) ? (float) $sales['Total'] : 0;
                $data['conversion'] = 0;
                if ($data['plays'] > 0) {
                    $data['conversion'] = number_format(($data['sales_count'] * 100 / $data['plays']), 2);
                }
//                $top_5 = $this->Audio_model->fetch_top_played($user_id, $date, 5);
//                $data['top_5'] = [];
//                foreach ($top_5 as $item) {
//                    $item['image_url'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $item['coverart'];
//                    $data['top_5'][] = $item;
//                }
//                //ACTIVITY LOG
//                $activity_5 = $this->Audio_model->fetch_top_activity($user_id, $date, 5);
//                $data['activity'] = [];
//                foreach ($activity_5 as $item) {
//                    $item['image_url'] = $this->server_url . $this->s3_path . $this->s3_folder . '/' . $item['image'];
//                    $data['activity'][] = $item;
//                }

                $data['beats_info'] = $this->Audio_model->fetch_earning($user_id, $date);
                $data['free_downloads_info'] = $this->Audio_model->fetch_audio_log_data($user_id, 'FREE_DOWNLOAD', $date);
                $data['plays_info'] = $this->Audio_model->fetch_audio_log_data($user_id, 'PLAY', $date);
                $data['marketing_info'] = $this->Audio_model->fetch_earning_marketing($user_id, $date);
                $data['top_beat_sales'] = $this->Audio_model->fetch_top_sales($user_id, $date, 5);
                //$data['top_referrers'] = $this->Audio_model->fetch_top_referrers($user_id, $date, 5);

                $data['top_referrers'] = $this->Visitor_model->fetch_top_referrers($user_id, $date, 5);
                $data['visitors'] = $this->Visitor_model->fetch_visitors($user_id, $date);


                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $data), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function orders_get($user_id = null, $invoice_id = null) {
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $response = [];
            if (empty($invoice_id)) {
                $orders = $this->User_model->fetch_user_orders($user_id);
                foreach ($orders as $order) {
                    $order['invoice_number'] .= '-' . $user_id;
                    $order['created_at'] = $this->general_library->gmt_to_est($order['created_at']);
                    $response[] = $order;
                }
            } else {
                $orders = $this->User_model->fetch_user_order_detail($user_id, $invoice_id);

                $response['invoice_number'] = '';
                $response['customer'] = '';
                $response['email'] = '';
                $response['total'] = 0;
                $response['created_at'] = '';
                $response['cc_type'] = '';
                foreach ($orders as $order) {
                    $response['invoice_number'] = $order['invoice_number'] .= '-' . $user_id;
                    $response['created_at'] = $this->general_library->gmt_to_est($order['created_at']);
                    $response['customer'] = $order['first_name'] . ' ' . $order['last_name'];
                    $response['email'] = $order['email'];
                    $item_id = $order['item_id'];
                    $item_track_type = $order['item_track_type'];
                    if ($item_track_type == 'beat' || $item_track_type == 'kit') {
                        $audio = $this->Audio_model->fetch_audio_by_id($item_id);
                    } else {
                        $audio = $this->Album_model->fetch_album_by_id($item_id);
                    }
                    $order['data_image'] = '';
                    if (!empty($audio['coverart'])) {
                        $order['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $audio['coverart'];
                        //NEW ENCRYPTED IMAGE
                        $final_url = $this->general_library->encode_image_url($user_id, $this->s3_path . $this->s3_coverart . '/' . $audio['coverart']);
                        $order['data_image'] = $final_url;
                        //END ENCRYPTED IMAGE
                    }
                    $order['license'] = '';
                    if (!empty($order['license_id'])) {
                        $license = $this->License_model->fetch_license_by_id($order['license_id']);
                        if (!empty($license)) {
                            $order['license'] = $license['title'];
                        }
                    }

                    unset($order['billingCC6']);
                    unset($order['billingCC']);
                    unset($order['invoice_number']);
                    unset($order['created_at']);
                    unset($order['first_name']);
                    unset($order['last_name']);
                    unset($order['email']);
                    $response['total'] += $order['item_amount'];
                    $response['cc_type'] = (!empty($order['cc_type'])) ? $order['cc_type'] : 'Visa';
                    $response['items'][] = $order;
                }
                $response['total'] = number_format($response['total'], 2);
            }
            $this->response(array('status' => 'success', 'env' => ENV, 'data' => $response), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    //
    //PAYPAL CONNECT//
    public function confirm_paypal_account_post() {
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $paypal_user_id = (!empty($this->input->post('paypal_user_id'))) ? $this->input->post('paypal_user_id') : '';
        $paypal_email = (!empty($this->input->post('paypal_email'))) ? $this->input->post('paypal_email') : '';
        $account_type = (!empty($this->input->post('account_type'))) ? $this->input->post('account_type') : 'payout';
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                //$this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $register_user = $this->User_model->fetch_user_by_id($user_id);
            if (!empty($register_user)) {
                //Guardar Account ID **
                if ($account_type == 'payout') {
                    $user_connect = [];
                    //$user_connect['TEMP'] = '';
                    $user_connect['user_id'] = $user_id;
                    $user_connect['status'] = 'ACTIVE';
                    $user_connect['processor'] = 'Paypal';
                    $user_connect['login_url'] = $paypal_user_id;
                    $user_connect['payouts_enabled'] = '1';
                    $user_connect['email'] = $paypal_email;
                    $this->User_model->insert_user_connect($user_connect);
                } else {
                    $payment_method = [];
                    $payment_method['user_id'] = $user_id;
                    $payment_method['status'] = 'ACTIVE';
                    $payment_method['payment_processor'] = 'Paypal';
                    //$payment_method['first_name'] = $first_name;
                    //$payment_method['last_name'] = $last_name;
                    //$payment_method['first_cc_number'] = substr($cc_number, 0, 6);
                    //$payment_method['last_cc_number'] = substr($cc_number, -4);
                    //$payment_method['expiration_date'] = $expiration_date;
                    //$payment_method['cvv'] = $cvv;
                    $payment_method['paypal_email'] = $paypal_email;
                    $payment_method['paypal_user_id'] = $paypal_user_id;
                    $this->User_model->insert_payment_method($payment_method);
                }
                $this->response(array('status' => 'success', 'env' => ENV, 'paypal_enabled' => TRUE), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function paypal_account_get($user_id = null, $account_type = 'payout') {
        if (!empty($user_id)) {
            if (!$this->general_library->header_token($user_id)) {
                //$this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            if ($account_type == 'payout') {
                $temp_paypal_account = $this->User_model->fetch_stripe_account_by_user_id($user_id, 'Paypal');
                if (empty($temp_paypal_account)) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'paypal_enabled' => FALSE), RestController::HTTP_OK);
                } elseif ($temp_paypal_account['status'] != 'ACTIVE') {
                    $this->response(array('status' => 'false', 'env' => ENV, 'paypal_enabled' => FALSE), RestController::HTTP_OK);
                } else {
                    $this->response(array('status' => 'success', 'env' => ENV, 'paypal_enabled' => TRUE, 'paypal_email' => $temp_paypal_account['email']), RestController::HTTP_OK);
                }
            } else {
                $temp_paypal_account = $this->User_model->fetch_paypal_method_by_user_id($user_id);
                if (empty($temp_paypal_account)) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'paypal_enabled' => FALSE), RestController::HTTP_OK);
                } else {
//                    print_r($temp_paypal_account);
                    $this->response(array('status' => 'success', 'env' => ENV, 'paypal_enabled' => TRUE, 'paypal_email' => $temp_paypal_account['paypal_email']), RestController::HTTP_OK);
                }
            }
        } else {
            $this->error = 'Provide User ID.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    public function paypal_account_delete($user_id = null, $account_type = 'payout') {
//        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
//        $account_id = (!empty($this->input->post('account_id'))) ? $this->input->post('account_id') : '';
        if (!empty($user_id)) {
            //UPDATE Account ID
            if ($account_type == 'payout') {
                $this->User_model->update_connect_by_user_id_pp($user_id, ['status' => 'DELETED']);
            } else {
                $temp_paypal_account = $this->User_model->fetch_paypal_method_by_user_id($user_id);
                if (empty($temp_paypal_account)) {
                    $this->User_model->update_payment_method($temp_paypal_account['id'], ['status' => 'INACTIVE']);
                }
            }
            $this->response(array('status' => 'success', 'env' => ENV), RestController::HTTP_OK);
        } else {
            $this->error = 'Provide User ID';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    //END PAYPAL CONNECT//
    //
//    public function connect_account_post() {
//        $connect_account = [];
//        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
//        if (!empty($user_id)) {
//            if (!$this->general_library->header_token($user_id)) {
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//            }
//            $holder_name = (!empty($this->input->post('holder_name'))) ? $this->input->post('holder_name') : '';
//            $routing_number = (!empty($this->input->post('routing_number'))) ? $this->input->post('routing_number') : '';
//            $account_number = (!empty($this->input->post('account_number'))) ? $this->input->post('account_number') : '';
//            //Connect Account
//            $express_account = $this->express_account_complex($user_id, $holder_name, $routing_number, $account_number);
//            if ($express_account['status']) {
//                $connect_account['account_id'] = $express_account['account_id'];
//                $connect_account['user_id'] = $user_id;
//                //$this->User_model->insert_payment_method($payment_method);
//                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The account has been connected successfully.', 'account' => $express_account), RestController::HTTP_OK);
//            } else {
//                $this->error = $express_account['error'];
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            }
//        } else {
//            $this->error = 'Provide User ID.';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
//
//    private function express_account_complex($user_id, $holder_name, $routing_number, $account_number) {
//        $response = [];
//        $register_user = $this->User_model->fetch_user_by_id($user_id);
//        $country = $register_user['country'];
//        $email = $register_user['email'];
//        $first_name = $register_user['first_name'];
//        $last_name = $register_user['last_name'];
//        $url = 'https://linkstream.com/' . $register_user['url'];
////        $email = 'paolofq@gmail.com';
////        $first_name = 'Paul';
////        $last_name = 'Ferra';
////        $url = 'link.stream/paolo_linkstream';
//        $business_type = 'individual'; //individual-company-non_profit-government_entity(US only)
//        $account_holder_name = $holder_name;
//        $account_holder_type = 'individual'; //company
//        $routing_number = $routing_number;
//        $account_number = $account_number;
////        $routing_number = '111000000';
////        $account_number = '000123456789';
//        $external_account = [
//            'object' => 'bank_account',
//            'country' => $country,
//            //'currency' => $currency,
//            'account_holder_name' => $account_holder_name,
//            'account_holder_type' => $account_holder_type,
//            'routing_number' => $routing_number,
//            'account_number' => $account_number
//        ];
//        $business_profile = [
//            'url' => $url,
//            'name' => $account_holder_name,
//        ];
//        $individual = [
//            'first_name' => $first_name,
//            'last_name' => $last_name,
//            'email' => $email
//        ];
//        $tos_acceptance = [
//            'date' => time(),
//            'ip' => $_SERVER['REMOTE_ADDR'], // Assumes you're not using a proxy
//        ];
//        $response = $this->stripe_library->express_account_complex($country, $email, $external_account, $business_type, $business_profile, $individual, $tos_acceptance);
//        return $response;
//    }
    //STORE
    public function add_store_post() {
        $user_id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
        $store_name = (!empty($this->input->post('store_name'))) ? $this->input->post('store_name') : '';
        $store_url = (!empty($this->input->post('store_url'))) ? $this->input->post('store_url') : '';
        if (!empty($user_id) && !empty($store_name) && !empty($store_url)) {
            if (!$this->general_library->header_token($user_id)) {
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
            }
            $register_user = $this->User_model->fetch_user_store_by_id($user_id);
            if (!empty($register_user)) {
                $user_store = [];
                //STORE
                $user_store['user_account_id'] = $user_id;
                $user_store['user_name'] = $user_store['display_name'] = $store_name;
                $user_store['url'] = $store_url;
                $user_store['email'] = $register_user['email'];
                $user_store['password'] = $register_user['password'];
                $user_store['plan_id'] = $register_user['plan_id'];
                $user_store['status_id'] = $register_user['status_id'];
                $user_store['type'] = $register_user['type'];
                $user_store['platform'] = $register_user['platform'];
                $user_store['id'] = $this->User_model->insert_user($user_store);
                $user_store['token'] = $this->User_model->create_token($user_store['id']);
                //Avatar & Banner            
                $user_store['data_image'] = '';
                $user_store['data_banner'] = '';
                $user_store['banner'] = '';
                $user_store['image'] = 'LS_avatar.png';
                $final_url = $this->general_library->encode_image_url($user_store['id'], $this->s3_path . $this->s3_folder . '/' . $user_store['image']);
                $user_store['data_image'] = $final_url;
                $this->User_model->insert_user_log(array('user_id' => $user_id, 'event' => 'New Store: ' . $store_name));
                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $user_store), RestController::HTTP_OK);
            } else {
                $this->error = 'User Not Found.';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Store Info';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

}
