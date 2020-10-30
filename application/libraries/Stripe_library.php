<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Stripe_library {

    private $api_error;
    private $publishable_key;
    private $secret_key;
    private $stripe;

    public function __construct() {
        require_once('application/libraries/stripe-php/init.php');
        //include_once dirname(__FILE__) . '/stripe-php/init.php';
        if (ENV == 'live') {
            $this->publishable_key = 'pk_live_xikoJK3piFoIvgUgvz8wfsN000CtztAYSd';
            $this->secret_key = 'sk_live_21yE6cimzdvqs1v4N1jcfUaU00jgxzh3s9';
        } else {
            $this->publishable_key = 'pk_test_PGu3tHgt28WnXtSyX5R8Yg4b004V9SJypw';
            $this->secret_key = 'sk_test_UsIQFzySI1SZUIXXejLPufm6002Oa44VfP';
        }
    }

    public function create_payment_method($type, $card) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        try {
            $object = $this->stripe->paymentMethods->create([
                'type' => $type,
                'card' => $card,
            ]);
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
            $response['status'] = true;
            $response['payment_method_id'] = $object->id;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    public function create_customer($name, $email, $phone, $address, $shipping, $payment_method, $description, $metadata) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        try {
            $object = $this->stripe->customers->create([
                'name' => $name,
                'email' => $email,
                //'phone' => $phone,
                'payment_method' => $payment_method,
                'description' => $description,
                'metadata' => $metadata,
                'invoice_settings' => ['default_payment_method' => $payment_method],
            ]);
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
            $response['status'] = true;
            $response['customer_id'] = $object->id;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    public function create_subscription($customer_id, $plan, $price, $default_payment_method) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        try {
            $object = $this->stripe->subscriptions->create([
                'customer' => $customer_id,
                'items' => [['price' => $price]],
            ]);
            echo '<pre>';
            print_r($object);
            echo '</pre>';
            $response['status'] = true;
            $response['subscription_id'] = $object->id;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    public function create_account($country, $email, $business_type, $external_account) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        $type = 'custom'; //API custom
        $requested_capabilities = [
            'card_payments',
            'transfers',
        ];
        try {
            $object = $this->stripe->accounts->create([
                'type' => $type,
                'country' => $country,
                'email' => $email,
                'business_type' => $business_type,
                'requested_capabilities' => $requested_capabilities,
                'external_account' => $external_account
            ]);
            echo '<pre>';
            print_r($object);
            echo '</pre>';
//            $response['status'] = true;
//            $response['payment_method_id'] = $object->id;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    //
    public function express_account($country, $email) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        try {
            $requested_capabilities = [
                //'card_payments',
                'transfers',
            ];
            $object = $this->stripe->accounts->create([
//                'country' => $country,
//                'type' => 'custom',
//                'email' => $email,
//                'requested_capabilities' => $requested_capabilities,
//                
                //'country' => $country,
                'type' => 'express'//standard
            ]);
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
            $response['status'] = true;
            $response['account_id'] = $object->id;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    public function express_account_complex($country, $email, $external_account, $business_type, $business_profile, $individual, $tos_acceptance) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        $requested_capabilities = [
            //'card_payments',
            'transfers',
        ];
        try {
            $object = $this->stripe->accounts->create([
                'country' => $country,
                'type' => 'custom',
                'email' => $email,
                'requested_capabilities' => $requested_capabilities,
                'business_type' => $business_type,
                'external_account' => $external_account,
                'business_profile' => $business_profile,
                'individual' => $individual,
                'tos_acceptance' => $tos_acceptance
            ]);
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
            $response['status'] = true;
            $response['account_id'] = $object->id;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    public function account_link($account, $debug) {
        $response = [];
        if (ENV == 'live') {
            $refresh_url = 'https://linkstream.com/app/account/payments/stripe_cancel';
            $return_url = 'https://linkstream.com/app/account/payments/stripe_confirm';
        } else {
            $refresh_url = 'https://dev-link-vue.link.stream/app/account/payments/stripe_cancel';
            $return_url = 'https://dev-link-vue.link.stream/app/account/payments/stripe_confirm';
        }
        //$refresh_url = ($debug == false) ? 'https://dev-link-vue.link.stream/app/account/payments/stripe_cancel' : 'http://localhost:8080/app/account/payments/stripe_cancel';
        //$return_url = ($debug == false) ? 'https://dev-link-vue.link.stream/app/account/payments/stripe_confirm' : 'http://localhost:8080/app/account/payments/stripe_confirm';
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        try {
            $object = $this->stripe->accountLinks->create([
                'account' => $account,
                'refresh_url' => $refresh_url,
                'return_url' => $return_url,
                'type' => 'account_onboarding',
            ]);
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
            $response['status'] = true;
            $response['account_url'] = $object->url;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    public function retrieve_account($account) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        try {
            $object = $this->stripe->accounts->retrieve(
                    $account
            );
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
            $response['status'] = true;
            $response['payouts_enabled'] = $object->payouts_enabled;
            $response['login_links'] = $object->login_links['url'];
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    public function retrieve_login($account) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        try {
            $object = $this->stripe->accounts->createLoginLink(
                    $account,
                    []
            );
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
            $response['status'] = true;
            $response['url'] = $object->url;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    //PAYMENT
    public function create_a_card_token($exp_month, $exp_year, $number, $cvc, $name, $address_zip) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        try {
            $object = $this->stripe->tokens->create([
                'card' => [
                    'number' => $number,
                    'exp_month' => $exp_month,
                    'exp_year' => $exp_year,
                    'cvc' => $cvc,
                    'name' => $name,
                    'address_zip' => $address_zip
                ],
            ]);
//            'card' => [
//                    'number' => '4242424242424242',
//                    'exp_month' => 10,
//                    'exp_year' => 2021,
//                    'cvc' => '314',
//                ],
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
            $response['status'] = true;
            $response['token_id'] = $object->id;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

    public function create_a_charge($amount, $description, $receipt_email, $source, $transfer_group) {
        $response = [];
        $this->stripe = new \Stripe\StripeClient($this->secret_key);
        try {
            $object = $this->stripe->charges->create([
                'amount' => ($amount * 100),
                'currency' => 'usd',
                'description' => $description,
                'source' => 'tok_mastercard',
                'receipt_email' => $receipt_email,
                'source' => $source,
                'transfer_group' => $transfer_group
            ]);

//            $stripe->charges->create([
//  'amount' => 2000,
//  'currency' => 'usd',
//  'source' => 'tok_mastercard',
//  'description' => 'My First Test Charge (created for API docs)',
//]);
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
            $response['status'] = true;
            $response['charge_id'] = $object->id;
            $response['receipt_url'] = $object->receipt_url;
        } catch (Exception $e) {
            $this->api_error = $e->getMessage();
            $response['status'] = false;
            $response['error'] = $this->api_error;
        }
        return $response;
    }

}
