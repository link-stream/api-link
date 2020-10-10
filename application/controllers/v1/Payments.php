<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Payments extends RestController {

    private $error;
    private $bucket;
    private $s3_path;
    private $s3_folder;
    private $temp_dir;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model('User_model');
        //Libraries
        //$this->load->library(array('aws_s3', 'Aws_pinpoint'));
        $this->load->library('Stripe_library');
        //Helpers
        //$this->load->helper('email');
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_path = (ENV == 'live') ? 'Prod/' : 'Dev/';
        $this->s3_folder = 'Coverart';
        $this->temp_dir = $this->general_library->get_temp_dir();
    }

    public function cc_payment_post() {

//        $array = [
//            'user_id' => '35',
//            'payment' => [
//                'exp_month' => '10',
//                'exp_year' => '2021',
//                'number' => '4242424242424242',
//                'cvc' => '314',
//                'name' => 'John Doe',
//                'address_zip' => '33312',
//                'subtotal' => '180',
//                'feeCC' => '10',
//                'feeService' => '10',
//                'total' => '200'
//            ],
//            'cart' => [
//                ['item_id' => '10', 'item_title' => 'Title 10', 'item_amount' => '45', 'item_track_type' => 'beat', 'producer_id' => '30', 'license_id' => '5'],
//                ['item_id' => '25', 'item_title' => 'Title 25', 'item_amount' => '90', 'item_track_type' => 'kit', 'producer_id' => '30', 'license_id' => ''],
//                ['item_id' => '67', 'item_title' => 'Title 67', 'item_amount' => '45', 'item_track_type' => 'pack', 'producer_id' => '24', 'license_id' => '']
//            ]
//        ];
        $data = (!empty($this->input->post('data'))) ? $this->input->post('data') : '';
        if (!empty($data)) {
            $data_info = json_decode($data, TRUE);
            if (is_array($data_info)) {
                $user_id = (!empty($data_info['user_id'])) ? $data_info['user_id'] : null;
                if (!$this->general_library->header_token($user_id)) {
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $payment = (!empty($data_info['payment'])) ? $data_info['payment'] : null;
                if (empty($payment)) {
                    $this->error = 'Provide Payment Info';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
                $exp_month = (!empty($payment['exp_month'])) ? $payment['exp_month'] : null;
                $exp_year = (!empty($payment['exp_year'])) ? $payment['exp_year'] : null;
                $number = (!empty($payment['number'])) ? $payment['number'] : null;
                $cvc = (!empty($payment['cvc'])) ? $payment['cvc'] : null;
                $name = (!empty($payment['name'])) ? $payment['name'] : null;
                $address_zip = (!empty($payment['address_zip'])) ? $payment['address_zip'] : null;
                $subtotal = (!empty($payment['subtotal'])) ? $payment['subtotal'] : 0;
                $feeService = (!empty($payment['feeService'])) ? $payment['feeService'] : 0;
                $feeCC = (!empty($payment['feeCC'])) ? $payment['feeCC'] : 0;
                $total = (!empty($payment['total'])) ? $payment['total'] : 0;
                if (empty($exp_month) || empty($exp_year) || empty($number) || empty($cvc) || empty($name) || empty($address_zip) || empty($subtotal) || empty($total)) {
                    $this->error = 'Provide Complete Payment Info';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
                $cart = (!empty($data_info['cart'])) ? $data_info['cart'] : null;
                if (empty($cart)) {
                    $this->error = 'Provide Cart Info';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
                $subtotal_cart = 0;
                $cart_success = true;
                foreach ($cart as $item) {
                    $subtotal_cart += (!empty($item['item_amount'])) ? $item['item_amount'] : 0;
                    $item_id = (!empty($item['item_id'])) ? $item['item_id'] : null;
                    $item_title = (!empty($item['item_title'])) ? $item['item_title'] : null;
                    $item_track_type = (!empty($item['item_track_type'])) ? $item['item_track_type'] : null;
                    $producer_id = (!empty($item['producer_id'])) ? $item['producer_id'] : null;
                    if (empty($item_id) || empty($item_title) || empty($item_track_type) || empty($producer_id)) {
                        $cart_success = false;
                        break;
                    }
                }
                if (!$cart_success) {
                    $this->error = 'Provide Complete Cart Info';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
                if ($subtotal_cart != $subtotal) {
                    $this->error = 'The sum of the amount of the items is not equal to the subtotal amount';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }

                $subtotal_fees = $subtotal + $feeService + $feeCC;
                $a = (string) $subtotal_fees;
                $b = $total;
                if ($a != $b) {
                    $this->error = 'The total amount is not equal to the sum of other amounts';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
                //Validation OK
                //PAYMENT
                //CREATE TOKEN
                $card_token = $this->stripe_library->create_a_card_token($exp_month, $exp_year, $number, $cvc, $name, $address_zip);
                if (!$card_token['status']) {
                    $this->error = 'Payment Error: ' . $card_token['error'];
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                } else {
                    $token_id = $card_token['token_id'];
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
                    $transfer_group = $invoice_number;
                    $description = 'Linkstream - Invoice: ' . $invoice_number;
                    $user_data = $this->User_model->fetch_user_by_id($user_id);
                    $receipt_email = $user_data['email'];
                    $charge = $this->stripe_library->create_a_charge($total, $description, $receipt_email, $token_id, $transfer_group);
                    if (!$charge['status']) {
                        $this->error = 'Payment Error: ' . $charge['error'];
                        $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                    } else {
                        $charge_id = $charge['charge_id'];
                        $receipt_url = $charge['receipt_url'];
                        $invoice['invoice_number'] = $invoice_number;
                        $invoice['status'] = 'COMPLETED';
                        $invoice['payment_charge_id'] = $charge_id;
                        $invoice['billingZip'] = $address_zip;
                        $invoice['billingCVV'] = $cvc;
                        $invoice['billingCC6'] = substr($number, 6);
                        $invoice['billingCC'] = substr($number, -4);
                        $invoice['billingName'] = $name;
                        //UPDATE PURSHASE
                        $this->User_model->update_user_purchase($invoice_id, $invoice);
                        //UPDATE DETAILS
                        foreach ($cart as $item) {
                            $item['invoice_id'] = $invoice_id;
                            //$item_id = (!empty($item['item_id'])) ? $item['item_id'] : null;
                            //$item_title = (!empty($item['item_title'])) ? $item['item_title'] : null;
                            //$item_amount =  (!empty($item['item_amount'])) ? $item['item_amount'] : 0;
                            //$producer_id = (!empty($item['producer_id'])) ? $item['producer_id'] : null;
                            //license_id = (!empty($item['license_id'])) ? $item['license_id'] : null;
                            //$item_track_type = (!empty($item['item_track_type'])) ? $item['item_track_type'] : null;
                            $item['item_table'] = ($item['item_track_type'] == 'pack') ? 'st_album' : 'st_audio';
                            $this->User_model->insert_user_purchase_details($item);
                        }
                        //RESPONSE TRUE
                        $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The order was created succefully', 'id' => $invoice_number), RestController::HTTP_OK);
                    }
                }
            } else {
                $this->error = 'Provide Correct Data Format';
                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
            }
        } else {
            $this->error = 'Provide Data.';
            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
        }
    }

    //NOTE: 
//    private function image_decode_put($image) {
//        preg_match("/^data:image\/(.*);base64/i", $image, $match);
//        $ext = (!empty($match[1])) ? $match[1] : '.png';
//        $image_name = md5(uniqid(rand(), true)) . '.' . $ext;
//        //upload image to server 
//        file_put_contents($this->temp_dir . '/' . $image_name, file_get_contents($image));
//        //SAVE S3
//        $this->s3_push($image_name);
//        return $image_name;
//    }
//
//    private function s3_push($image_name) {
//        //SAVE S3
//        $source = $this->temp_dir . '/' . $image_name;
//        $destination = $this->s3_path . $this->s3_folder . '/' . $image_name;
//        $this->aws_s3->s3push($source, $destination, $this->bucket);
//        unlink($this->temp_dir . '/' . $image_name);
//    }
//
//    private function link_clean($link, $images = true) {
//        $link['scheduled'] = true;
//        if ($link['publish_at'] == '0000-00-00 00:00:00' || empty($link['publish_at'])) {
//            $link['scheduled'] = false;
//        }
//        $link['date'] = ($link['scheduled']) ? substr($link['publish_at'], 0, 10) : '';
//        $link['time'] = ($link['scheduled']) ? substr($link['publish_at'], 11) : '';
//        $link['end_date'] = ($link['scheduled']) ? (($link['publish_end'] != '0000-00-00 00:00:00') ? substr($link['publish_end'], 0, 10) : '') : '';
//        $link['end_time'] = ($link['scheduled']) ? (($link['publish_end'] != '0000-00-00 00:00:00') ? substr($link['publish_end'], 11) : '') : '';
//
//
//
//        //Coverart
//        $path = $this->s3_path . $this->s3_folder;
//        $link['data_image'] = '';
//        if ($images) {
//            if (!empty($link['coverart'])) {
//                $data_image = $this->aws_s3->s3_read($this->bucket, $path, $link['coverart']);
//                //$link['data_image'] = (!empty($data_image)) ? base64_encode($data_image) : '';
//                if (!empty($data_image)) {
//                    $img_file = $link['coverart'];
//                    file_put_contents($this->temp_dir . '/' . $link['coverart'], $data_image);
//                    $src = 'data: ' . mime_content_type($this->temp_dir . '/' . $link['coverart']) . ';base64,' . base64_encode($data_image);
//                    $link['data_image'] = $src;
//                    unlink($this->temp_dir . '/' . $link['coverart']);
//                }
//            }
//        }
//        unset($link['publish_at']);
//        unset($link['publish_end']);
//        //unset($link['timezone']);
//        return $link;
//    }
//
//    public function index_get($id = null, $link_id = null) {
//        if (!empty($id)) {
//            if (!$this->general_library->header_token($id)) {
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//            }
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
//                $links = $this->Link_model->fetch_links_by_user_id($id, $link_id, false, $limit, $offset);
//                $links_reponse = array();
//                foreach ($links as $link) {
//                    $link_reponse = $this->link_clean($link);
//                    $links_reponse[] = $link_reponse;
//                }
//                $this->response(array('status' => 'success', 'env' => ENV, 'data' => $links_reponse), RestController::HTTP_OK);
//            }
//        } else {
//            $this->error = 'Provide User ID.';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
//
//    public function index_post() {
//        $link = array();
//        $link['user_id'] = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
//        $link['status_id'] = '1';
//        $link['title'] = (!empty($this->input->post('title'))) ? $this->input->post('title') : '';
//        $link['url'] = (!empty($this->input->post('url'))) ? $this->input->post('url') : '';
//        if ((!empty($link['user_id']) || !empty($link['title'])) && !empty($link['url'])) {
//            if (!$this->general_library->header_token($link['user_id'])) {
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//            }
//            $link['public'] = (!empty($this->input->post('public'))) ? $this->input->post('public') : '1';
//            $scheduled = (!empty($this->input->post('scheduled'))) ? true : false;
//            if ($scheduled) {
//                $date = (!empty($this->input->post('date'))) ? substr($this->input->post('date'), 0, 10) : '0000-00-00';
//                $time = (!empty($this->input->post('time'))) ? $this->input->post('time') : '00:00:00';
//                $link['publish_at'] = $date . ' ' . $time;
//                $end_date = (!empty($this->input->post('end_date'))) ? substr($this->input->post('end_date'), 0, 10) : '0000-00-00';
//                $end_time = (!empty($this->input->post('end_time'))) ? $this->input->post('end_time') : '00:00:00';
//                $link['publish_end'] = $end_date . ' ' . $end_time;
//            } else {
//                $date = '0000-00-00';
//                $time = '00:00:00';
//                $link['publish_at'] = $date . ' ' . $time;
//                $link['publish_end'] = $date . ' ' . $time;
//            }
//            if (!empty($this->input->post('image'))) {
//                $image = $this->input->post("image");
//                $link['coverart'] = $this->image_decode_put($image);
//            }
//            $link['sort'] = $this->get_last_link_sort($link['user_id']);
//            $id = $this->Link_model->insert_link($link);
//            //REPONSE
//            $link_response = $this->Link_model->fetch_link_by_id($id);
//            $link_response = $this->link_clean($link_response);
//            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The link has been created successfully.', 'id' => $id, 'data' => $link_response), RestController::HTTP_OK);
//        } else {
//            $this->error = 'Provide complete link info to add';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
//
//    private function get_last_link_sort($user_id) {
//        $max = $this->Link_model->fetch_max_link_sort($user_id);
//        $sort = (empty($max)) ? '1' : ($max + 1);
//        return $sort;
//    }
//
//    public function index_put($id = null) {
//        if (!empty($id)) {
//            $link = $this->Link_model->fetch_link_by_id($id);
//            if (!empty($link)) {
//                if (!$this->general_library->header_token($link['user_id'])) {
//                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//                }
//                if (!empty($this->put('title'))) {
//                    $link['title'] = $this->put('title');
//                }
//                if (!empty($this->put('url'))) {
//                    $link['url'] = $this->put('url');
//                }
//                if (!empty($this->put('public'))) {
//                    $link['public'] = $this->put('public');
//                }
//                if ($this->put('scheduled') !== null) {
//                    $scheduled = (!empty($this->put('scheduled'))) ? true : false;
//                    if ($scheduled) {
//                        $date = (!empty($this->put('date'))) ? substr($this->put('date'), 0, 10) : '0000-00-00';
//                        $time = (!empty($this->put('time'))) ? $this->put('time') : '00:00:00';
//                        $link['publish_at'] = $date . ' ' . $time;
//                        $end_date = (!empty($this->put('end_date'))) ? substr($this->put('end_date'), 0, 10) : '0000-00-00';
//                        $end_time = (!empty($this->put('end_time'))) ? $this->put('end_time') : '00:00:00';
//                        $link['publish_end'] = $end_date . ' ' . $end_time;
//                    } else {
//                        $date = '0000-00-00';
//                        $time = '00:00:00';
//                        $link['publish_at'] = $date . ' ' . $time;
//                        $link['publish_end'] = $date . ' ' . $time;
//                    }
//                }
//
//                if ($this->put('image') !== null) {
//                    if (!empty($this->put('image'))) {
//                        $image = $this->put("image");
//                        $link['coverart'] = $this->image_decode_put($image);
//                    } else {
//                        $link['coverart'] = '';
//                    }
//                }
//                $this->Link_model->update_link($id, $link);
//                //REPONSE
//                $link_response = $this->link_clean($link);
//                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Link info has been updated successfully.', 'data' => $link_response), RestController::HTTP_OK);
//            } else {
//                $this->error = 'Link Not Found.';
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            }
//        } else {
//            $this->error = 'Provide Link ID.';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
//
//    public function sort_links_post() {
////        echo json_encode(array(
////        array('id' => '10', 'sort' => '1'),
////        array('id' => '1', 'sort' => '2'),
////        ));
//        $id = (!empty($this->input->post('user_id'))) ? $this->input->post('user_id') : '';
//        if (!$this->general_library->header_token($id)) {
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//        }
//        $list = (!empty($this->input->post('list'))) ? $this->input->post('list') : '';
//        if (!empty($list)) {
//            $links = json_decode($list, true);
//            foreach ($links as $link) {
//                $id = $link['id'];
//                $sort = $link['sort'];
//                $this->Link_model->update_link($id, array('sort' => $sort));
//            }
//            $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The information of the links has been updated correctly'), RestController::HTTP_OK);
//        } else {
//            $this->error = 'Provide list of links.';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
//
//    public function index_delete($id = null) {
//        if (!empty($id)) {
//            $link = $this->Link_model->fetch_link_by_id($id);
//            if (!empty($link)) {
//                if (!$this->general_library->header_token($link['user_id'])) {
//                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
//                }
//                $this->Link_model->update_link($id, ['status_id' => '3']);
//                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The Link has been deleted successfully.'), RestController::HTTP_OK);
//            } else {
//                $this->error = 'Link Not Found.';
//                $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//            }
//        } else {
//            $this->error = 'Provide Link ID.';
//            $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
//        }
//    }
}
