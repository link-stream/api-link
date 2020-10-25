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
    private $s3_coverart;
    private $temp_dir;
    private $server_url;
    private $linkstream_url;
    private $api_url;

    public function __construct() {
        parent::__construct();
        //Models
        $this->load->model('User_model');
        $this->load->model('Marketing_model');
        $this->load->model('License_model');
        $this->load->model('Album_model');
        //Libraries
        //$this->load->library(array('aws_s3', 'Aws_pinpoint'));
        $this->load->library('Stripe_library');
        //Helpers
        //$this->load->helper('email');
        //VARS
        $this->error = '';
        $this->bucket = 'files.link.stream';
        $this->s3_coverart = 'Coverart';
        $this->s3_path = (ENV == 'live') ? 'prod/' : 'dev/';
        $this->s3_folder = 'coverart';
        $this->temp_dir = $this->general_library->get_temp_dir();
        $this->server_url = 'https://s3.us-east-2.amazonaws.com/files.link.stream/';
        $this->linkstream_url = (ENV == 'live') ? 'https://www.linkstream.com/' : 'https://dev-link-vue.link.stream/';
        $this->api_url = (ENV == 'live') ? 'https://api.link.stream/' : 'https://api-dev.link.stream/';
    }

    public function cc_payment_post() {

//        $array = [
//            'user_id' => '35',
//            'utm_source' => '35',
//            'ref_id' => '35',
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
//                ['item_id' => '33', 'item_title' => 'Title 10', 'item_amount' => '45', 'item_track_type' => 'beat', 'producer_id' => '35', 'license_id' => '1', 'genre_id' => '3'],
//                ['item_id' => '381', 'item_title' => 'Title 25', 'item_amount' => '90', 'item_track_type' => 'kit', 'producer_id' => '35', 'license_id' => '', 'genre_id' => '3'],
//                ['item_id' => '67', 'item_title' => 'Title 67', 'item_amount' => '45', 'item_track_type' => 'pack', 'producer_id' => '35', 'license_id' => '1', 'genre_id' => '3']
//            ]
//        ];
        $data = (!empty($this->input->post('data'))) ? $this->input->post('data') : '';
        if (!empty($data)) {
            $data_info = json_decode($data, TRUE);
            if (is_array($data_info)) {
                $user_id = (!empty($data_info['user_id'])) ? $data_info['user_id'] : null;
                $utm_source = (!empty($data_info['utm_source'])) ? $data_info['utm_source'] : '';
                $ref_id = (!empty($data_info['ref_id'])) ? $data_info['ref_id'] : '';
                if (!$this->general_library->header_token($user_id)) {
                    //////$this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $payment = (!empty($data_info['payment'])) ? $data_info['payment'] : null;
                if (empty($payment)) {
                    $this->error = 'Provide Payment Info';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }
                $exp_month = (!empty($payment['exp_month'])) ? $payment['exp_month'] : null;
                $exp_year = (!empty($payment['exp_year'])) ? $payment['exp_year'] : null;
                $number = (!empty($payment['number'])) ? trim(str_replace(' ', '', $payment['number'])) : null;
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
                        $cc_type = $this->general_library->card_type($number);
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
                        $invoice['cc_type'] = $cc_type;
                        $invoice['utm_source'] = $utm_source;
                        $invoice['ref_id'] = $ref_id;
                        //UPDATE PURSHASE
                        $this->User_model->update_user_purchase($invoice_id, $invoice);
                        $cart_email = [];
                        $confirmation_url = [];
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



                            //NEW -LICENSE//
                            if ($item['item_track_type'] == 'pack') {
                                $item_album = $this->Album_model->fetch_album_by_id($item['item_id']);
                                if (!empty($item_album)) {
                                    $item['license_id'] = $item_album['license_id'];
                                }
                            }

                            if (!empty($item['license_id'])) {
                                //LICENSE
                                $license = $this->License_model->fetch_license_by_id($item['license_id']);
                                if (!empty($license)) {
                                    $item['license_title'] = $license['title'];
                                    $item['mp3'] = $license['mp3'];
                                    $item['wav'] = $license['wav'];
                                    $item['trackout_stems'] = $license['trackout_stems'];
                                    $item['distribution_copies'] = $license['distribution_copies'];
                                    $item['free_download'] = $license['free_download'];
                                    $item['audio_streams'] = $license['audio_streams'];
                                    $item['music_videos'] = $license['music_videos'];
                                    $item['video_streams'] = $license['video_streams'];
                                    $item['broadcasting_rights'] = $license['broadcasting_rights'];
                                    $item['radio_station'] = $license['radio_station'];
                                    $item['paid_performances'] = $license['paid_performances'];
                                    $item['non_profit_performances'] = $license['non_profit_performances'];
                                }
                            }
                            //END-LICENSE//
                            $invoice_detail_id = $this->User_model->insert_user_purchase_details($item);
                            //LOG Item License.
                            //$log_license = $this->log_item_license($invoice, $invoice_detail_id, $item);
                            //$confirmation_url[$item['item_id']] = $this->general_library->encode_download_url($item['invoice_id'], $invoice['user_id'], $item['item_id'], $item['producer_id'], $invoice_detail_id);
                            $confirmation_url[] = ['item_id' => $item['item_id'], 'item_track_type' => $item['item_track_type'], 'url' => $this->general_library->encode_download_url($item['invoice_id'], $invoice['user_id'], $item['item_id'], $item['producer_id'], $invoice_detail_id)];

                            //
                            $item['extra_info'] = $this->producer_item_info($item['item_id'], $item['item_track_type']);
                            $cart_email[] = $item;
                        }
                        //SEND CONFIRMATION EMAIL
                        $linkstream = $this->linkstream_url;
                        if ($cc_type == 'Amex') {
                            $cc = $linkstream . 'static/img/amex.svg';
                        } elseif ($cc_type == 'Diners Club') {
                            $cc = $linkstream . 'static/img/credit-card.svg';
                        } elseif ($cc_type == 'Discover') {
                            $cc = $linkstream . 'static/img/discover.svg';
                        } elseif ($cc_type == 'Jcb') {
                            $cc = $linkstream . 'static/img/credit-card.svg';
                        } elseif ($cc_type == 'Mastercard') {
                            $cc = $linkstream . 'static/img/mastercard.svg';
                        } elseif ($cc_type == 'Visa') {
                            $cc = $linkstream . 'static/img/visa.svg';
                        } elseif ($cc_type == 'Pay Pal') {
                            $cc = $linkstream . 'static/img/paypal.svg';
                        } else {
                            $cc = $linkstream . 'static/img/credit-card.svg';
                        }
                        $data = ['invoice' => $invoice, 'cart' => $cart_email, 'linkstream' => $linkstream, 'email' => $receipt_email, 'cc' => $cc];
                        $body = $this->load->view('app/email/email-confirm-pay6', $data, true);
                        $this->general_library->send_ses($name, $receipt_email, 'LinkStream', 'noreply@linkstream.com', "LinkStream Order Confirmation", $body);
                        if (!empty($invoice['ref_id'])) {
                            $this->Marketing_model->update_revenue_message_log($invoice['ref_id'], $invoice['total']);
                        }
                        //RESPONSE TRUE
                        $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The order was created succefully', 'id' => $invoice_number, 'email' => $receipt_email, 'cc_type' => $cc_type, 'billingCC' => $invoice['billingCC'], 'download' => $confirmation_url), RestController::HTTP_OK);
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

    private function producer_item_info($item_id, $item_track_type) {
        $producer_item = $this->User_model->fetch_confirmation_detail_item($item_id, $item_track_type);
        if (!empty($producer_item['coverart'])) {
            $producer_item['data_image'] = $this->server_url . $this->s3_path . $this->s3_coverart . '/' . $producer_item['coverart'];
        }
        return $producer_item;
    }

    private function log_item_license($invoice, $invoice_detail_id, $item) {
        $item_license = [];
        $item_license['user_id'] = $invoice['user_id'];
        $item_license['invoice_id'] = $item['invoice_id'];
        $item_license['invoice_detail_id'] = $invoice_detail_id;
        $item_license['item_id'] = $item['item_id'];
        $item_license['item_title'] = $item['item_title'];
        $item_license['item_amount'] = $item['item_amount'];
        $item_license['producer_id'] = $item['producer_id'];
        $item_license['item_track_type'] = $item['item_track_type'];
        $item_license['license_id'] = $item['license_id'];
        if (!empty($item_license['license_id'])) {
            //LICENSE
            $license = $this->License_model->fetch_license_by_id($item_license['license_id']);
            if (!empty($license)) {
                $item_license['title'] = $license['title'];
                $item_license['mp3'] = $license['mp3'];
                $item_license['wav'] = $license['wav'];
                $item_license['trackout_stems'] = $license['trackout_stems'];
                $item_license['distribution_copies'] = $license['distribution_copies'];
                $item_license['free_download'] = $license['free_download'];
                $item_license['audio_streams'] = $license['audio_streams'];
                $item_license['music_videos'] = $license['music_videos'];
                $item_license['video_streams'] = $license['video_streams'];
                $item_license['broadcasting_rights'] = $license['broadcasting_rights'];
                $item_license['radio_station'] = $license['radio_station'];
                $item_license['paid_performances'] = $license['paid_performances'];
                $item_license['non_profit_performances'] = $license['non_profit_performances'];
            }
        }
//        $item_license['code'] = uniqid('LS');
//        $item_license['hash'] = sha1($item_license['user_id'] . $item_license['item_id'] . $item_license['code']);
        //INSERT Item license.
        $item_license_id = $this->License_model->insert_item_license($item_license);
        //URL
        $item_license['url'] = $this->general_library->encode_download_url($item_license['invoice_id'], $item_license['user_id'], $item_license['item_id'], $item_license['producer_id']);
        return $item_license;
    }

    public function paypal_payment_post() {

//        $array = [
//            'user_id' => '35',
//            'utm_source' => 'email',
//            'ref_id' => '54645',
//            'country' => 'United States',
//            'payment' => [
//                'paymentID' => 'PAYID-L6I46DY94893457Y3361140Y',
//                'paymentToken' => 'EC-9LX554202G293421G',
//                'name' => 'John Doe',
//                'subtotal' => '180',
//                'feeCC' => '10',
//                'feeService' => '10',
//                'total' => '200'
//            ],
//            'cart' => [
//                ['item_id' => '33', 'item_title' => 'Title 10', 'item_amount' => '45', 'item_track_type' => 'beat', 'producer_id' => '35', 'license_id' => '1', 'genre_id' => '3'],
//                ['item_id' => '381', 'item_title' => 'Title 25', 'item_amount' => '90', 'item_track_type' => 'kit', 'producer_id' => '35', 'license_id' => '', 'genre_id' => '3'],
//                ['item_id' => '67', 'item_title' => 'Title 67', 'item_amount' => '45', 'item_track_type' => 'pack', 'producer_id' => '35', 'license_id' => '1', 'genre_id' => '3']
//            ]
//        ];
        $data = (!empty($this->input->post('data'))) ? $this->input->post('data') : '';
        if (!empty($data)) {
            $data_info = json_decode($data, TRUE);
            if (is_array($data_info)) {
                $user_id = (!empty($data_info['user_id'])) ? $data_info['user_id'] : null;
                $utm_source = (!empty($data_info['utm_source'])) ? $data_info['utm_source'] : '';
                $ref_id = (!empty($data_info['ref_id'])) ? $data_info['ref_id'] : '';
                if (!$this->general_library->header_token($user_id)) {
                    //////$this->response(array('status' => 'false', 'env' => ENV, 'error' => 'Unauthorized Access!'), RestController::HTTP_UNAUTHORIZED);
                }
                $payment = (!empty($data_info['payment'])) ? $data_info['payment'] : null;
                if (empty($payment)) {
                    $this->error = 'Provide Payment Info';
                    $this->response(array('status' => 'false', 'env' => ENV, 'error' => $this->error), RestController::HTTP_BAD_REQUEST);
                }

                $paymentID = (!empty($payment['paymentID'])) ? $payment['paymentID'] : null;
                $paymentToken = (!empty($payment['paymentToken'])) ? $payment['paymentToken'] : null;
                $name = (!empty($payment['name'])) ? $payment['name'] : null;
                $subtotal = (!empty($payment['subtotal'])) ? $payment['subtotal'] : 0;
                $feeService = (!empty($payment['feeService'])) ? $payment['feeService'] : 0;
                $feeCC = (!empty($payment['feeCC'])) ? $payment['feeCC'] : 0;
                $total = (!empty($payment['total'])) ? $payment['total'] : 0;

                if (empty($paymentID) || empty($paymentToken) || empty($subtotal) || empty($total)) {
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
                //
                $invoice = [
                    'user_id' => $user_id,
                    'status' => 'COMPLETED',
                    'sub_total' => $subtotal,
                    'feeCC' => $feeCC,
                    'feeService' => $feeService,
                    'total' => $total,
                    'payment_customer_id' => $paymentToken,
                ];
                $invoice_id = $this->User_model->insert_user_purchase($invoice);
                $invoice_number = 'LS' . str_pad($invoice_id, 7, "0", STR_PAD_LEFT);
                $invoice['invoice_number'] = $invoice_number;
                $invoice['status'] = 'COMPLETED';
                $invoice['payment_charge_id'] = $paymentID;
                //$invoice['billingZip'] = $address_zip;
                //$invoice['billingCVV'] = $cvc;
                //$invoice['billingCC6'] = substr($number, 6);
                //$invoice['billingCC'] = substr($number, -4);
                $invoice['billingName'] = $name;
                $invoice['cc_type'] = 'PayPal';
                $invoice['utm_source'] = $utm_source;
                $invoice['ref_id'] = $ref_id;
                //UPDATE PURSHASE
                $this->User_model->update_user_purchase($invoice_id, $invoice);
                //$invoice_id = $this->User_model->insert_user_purchase($invoice);
                //$invoice_number = 'LS' . str_pad($invoice_id, 7, "0", STR_PAD_LEFT);
                $transfer_group = $invoice_number;
                $description = 'Linkstream - Invoice: ' . $invoice_number;
                $user_data = $this->User_model->fetch_user_by_id($user_id);
                $receipt_email = $user_data['email'];
                $cart_email = [];
                $confirmation_url = [];
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
                    //NEW -LICENSE//
                    if ($item['item_track_type'] == 'pack') {
                        $item_album = $this->Album_model->fetch_album_by_id($item_id);
                        if (!empty($item_album)) {
                            $item['license_id'] = $item_album['license_id'];
                        }
                    }
                    if (!empty($item['license_id'])) {
                        //LICENSE
                        $license = $this->License_model->fetch_license_by_id($item['license_id']);
                        if (!empty($license)) {
                            $item['license_title'] = $license['title'];
                            $item['mp3'] = $license['mp3'];
                            $item['wav'] = $license['wav'];
                            $item['trackout_stems'] = $license['trackout_stems'];
                            $item['distribution_copies'] = $license['distribution_copies'];
                            $item['free_download'] = $license['free_download'];
                            $item['audio_streams'] = $license['audio_streams'];
                            $item['music_videos'] = $license['music_videos'];
                            $item['video_streams'] = $license['video_streams'];
                            $item['broadcasting_rights'] = $license['broadcasting_rights'];
                            $item['radio_station'] = $license['radio_station'];
                            $item['paid_performances'] = $license['paid_performances'];
                            $item['non_profit_performances'] = $license['non_profit_performances'];
                        }
                    }
                    //END-LICENSE//
                    $invoice_detail_id = $this->User_model->insert_user_purchase_details($item);
                    //LOG Item License.
                    //$log_license = $this->log_item_license($invoice, $invoice_detail_id, $item);
                    //$confirmation_url[$item['item_id']] = $this->general_library->encode_download_url($item['invoice_id'], $invoice['user_id'], $item['item_id'], $item['producer_id'], $invoice_detail_id);
                    $confirmation_url[] = ['item_id' => $item['item_id'], 'item_track_type' => $item['item_track_type'], 'url' => $this->general_library->encode_download_url($item['invoice_id'], $invoice['user_id'], $item['item_id'], $item['producer_id'], $invoice_detail_id)];
                    //
                    $item['extra_info'] = $this->producer_item_info($item['item_id'], $item['item_track_type']);
                    $cart_email[] = $item;
                }
                //
                //SEND CONFIRMATION EMAIL
                $linkstream = $this->linkstream_url;
                $cc_type = 'PayPal';
                $cc = $linkstream . 'static/img/paypal.svg';
                $data = ['invoice' => $invoice, 'cart' => $cart_email, 'linkstream' => $linkstream, 'email' => $receipt_email, 'cc' => $cc];
                $body = $this->load->view('app/email/email-confirm-pay6', $data, true);
                $this->general_library->send_ses($name, $receipt_email, 'LinkStream', 'noreply@linkstream.com', "LinkStream Order Confirmation", $body);
                if (!empty($invoice['ref_id'])) {
                    $this->Marketing_model->update_revenue_message_log($invoice['ref_id'], $invoice['total']);
                }
                //RESPONSE TRUE
                $this->response(array('status' => 'success', 'env' => ENV, 'message' => 'The order was created succefully', 'id' => $invoice_number, 'email' => $receipt_email, 'cc_type' => $cc_type, 'billingCC' => '', 'url' => $confirmation_url), RestController::HTTP_OK);
                //
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
}
