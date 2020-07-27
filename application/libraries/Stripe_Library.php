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

}
