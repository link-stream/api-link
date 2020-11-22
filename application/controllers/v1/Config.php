<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

//require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

require(APPPATH . 'libraries/RestController.php');

class Config extends RestController {

    public function __construct() {
        parent::__construct();
    }

    public function fees_get() {

        $data['fees'] = [
            ['name' => 'Convenience Fee', 'type' => 'Percent', 'value' => '0', 'var' => 'feeCC'],
            ['name' => 'Service Fee', 'type' => 'Amount', 'value' => '0', 'var' => 'feeService'],
        ];
        $this->response(array('status' => 'success', 'env' => ENV, 'data' => $data), RestController::HTTP_OK);
    }

    public function plans_get() {
        $plans = [];
        $pro_plan = [
            'Name' => 'PRO',
            'Monthly Price' => '$19',
            'Ways To Sell' => [
                'Online Stores' => '1',
                'Custom Store URL' => 'Yes',
                'Custom Domain Name' => 'Yes',
                'Social selling (Instagram, Facebook)' => 'Yes',
                'Fast, secure checkout' => 'Yes',
            ],
            'Store Management' => [
                'Tracks' => '100',
                'Sound Kits' => '25',
                'Beat Packs' => '10',
                'Links' => 'Unlimited',
                'Videos' => 'Unlimited',
                'Pro Licensing Agreements' => 'Yes',
                'Performance Analytics' => 'Yes',
                'Financial Reports' => 'Yes',
            ],
            'Marketing' => [
                'Subscribers' => '5,000',
                'Marketing Emails' => '25,000/mo',
                'SMS Promotions' => '25,000/mo',
                'Landing Pages' => 'Unlimited',
                'Discounts' => 'Unlimited',
                'Store Optimization (SEO)' => 'Yes',
                'YouTube Uploader' => 'Yes',
            ],
            'ProdBy Payments' => [
                'Accept All Major Credit Cards' => 'Yes',
                //'Accept ApplePay, GooglePay, AmazonPay' => 'Yes',
                'Accept PayPal Express Checkout' => 'Yes',
                'Collaborator Revenue Splits' => 'Yes',
            ]
        ];
        $plans[] = $pro_plan;
        $platinum_plan = [
            'Name' => 'PLATINUM',
            'Monthly Price' => '$99',
            'Ways To Sell' => [
                'Online Stores' => '1',
                'Custom Store URL' => 'Yes',
                'Custom Domain Name' => 'Yes',
                'Social selling (Instagram, Facebook)' => 'Yes',
                'Fast, secure checkout' => 'Yes',
            ],
            'Store Management' => [
                'Tracks' => 'Unlimited',
                'Sound Kits' => 'Unlimited',
                'Beat Packs' => 'Unlimited',
                'Links' => 'Unlimited',
                'Videos' => 'Unlimited',
                'Pro Licensing Agreements' => 'Yes',
                'Performance Analytics' => 'Yes',
                'Financial Reports' => 'Yes',
            ],
            'Marketing' => [
                'Subscribers' => '20,000',
                'Marketing Emails' => '100,000/mo',
                'SMS Promotions' => '100,000/mo',
                'Landing Pages' => 'Unlimited',
                'Discounts' => 'Unlimited',
                'Store Optimization (SEO)' => 'Yes',
                'YouTube Uploader' => 'Yes',
            ],
            'ProdBy Payments' => [
                'Accept All Major Credit Cards' => 'Yes',
                //'Accept ApplePay, GooglePay, AmazonPay' => 'Yes',
                'Accept PayPal Express Checkout' => 'Yes',
                'Collaborator Revenue Splits' => 'Yes',
            ]
        ];
        $plans[] = $platinum_plan;
        $business_plan = [
            'Name' => 'BUSINESS',
            'Monthly Price' => '$299',
            'Ways To Sell' => [
                'Online Stores' => '5',
                'Custom Store URL' => 'Yes',
                'Custom Domain Name' => 'Yes',
                'Social selling (Instagram, Facebook)' => 'Yes',
                'Fast, secure checkout' => 'Yes',
            ],
            'Store Management' => [
                'Tracks' => 'Unlimited',
                'Sound Kits' => 'Unlimited',
                'Beat Packs' => 'Unlimited',
                'Links' => 'Unlimited',
                'Videos' => 'Unlimited',
                'Pro Licensing Agreements' => 'Yes',
                'Performance Analytics' => 'Yes',
                'Financial Reports' => 'Yes',
            ],
            'Marketing' => [
                'Subscribers' => '20,000',
                'Marketing Emails' => '500,000/mo',
                'SMS Promotions' => '500,000/mo',
                'Landing Pages' => 'Unlimited',
                'Discounts' => 'Unlimited',
                'Store Optimization (SEO)' => 'Yes',
                'YouTube Uploader' => 'Yes',
            ],
            'ProdBy Payments' => [
                'Accept All Major Credit Cards' => 'Yes',
                //'Accept ApplePay, GooglePay, AmazonPay' => 'Yes',
                'Accept PayPal Express Checkout' => 'Yes',
                'Collaborator Revenue Splits' => 'Yes',
            ]
        ];
        $plans[] = $platinum_plan;

        $description = [
            'Ways To Sell' => [
                'Online Stores' => '',
                'Custom Store URL' => '',
                'Custom Domain Name' => "Not only will we let you use a custom domain, if you don't already have one you can register one with Prodby Domains and immediately integrate it with your online store(s), landing pages, marketing message and more",
                'Social selling (Instagram, Facebook)' => '',
                'Fast, secure checkout' => '',
            ],
            'Store Management' => [
                'Tracks' => "With Prodby Tracks you are able to host .mp3, .wav and .zip files, and seamlessly stream your tracks in your online store, on your landing pages and even in your marketing messages",
                'Sound Kits' => "Want to be able to easily upload, manage and sell your sound kits? Now you can with Prodby Kits",
                'Beat Packs' => '',
                'Links' => '',
                'Videos' => "Monetize your YouTube content right from your Prodby store",
                'Pro Licensing Agreements' => '',
                'Performance Analytics' => '',
                'Financial Reports' => '',
            ],
            'Marketing' => [
                'Subscribers' => "Manage and market to your own list of email subscribers and seemlessly integrate your beats and kits into each promotion or message.",
                'Marketing Emails' => '',
                'SMS Promotions' => '',
                'Landing Pages' => "Create high-converting landing pages to capture leads and sell beats, kits and more, and publish them anywhere!",
                'Discounts' => "Create unique links and codes at the same time with our handy Discounts tool, run promotional offers, seasonal sales and more",
                'Store Optimization (SEO)' => '',
                'YouTube Uploader' => '',
            ],
            'ProdBy Payments' => [
                'Accept All Major Credit Cards' => "Make sales, accept online payments, and deliver digital files from any profile page, track page, kit page, landing page or email/SMS with Prodby Checkouts powered by Stripe.",
                //'Accept ApplePay, GooglePay, AmazonPay' => 'Yes',
                'Accept PayPal Express Checkout' => '',
                'Collaborator Revenue Splits' => '',
            ]
        ];

        $this->response(array('status' => 'success', 'env' => ENV, 'plans' => $plans, 'description' => $description), RestController::HTTP_OK);
    }

}
