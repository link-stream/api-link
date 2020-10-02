<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Google_library {

    private $api_error;
    private $publishable_key;
    private $secret_key;
    private $stripe;

    public function __construct() {
        //require_once('application/libraries/stripe-php/init.php');
        require_once 'application/libraries/google-api-php-client-v2.7.2-PHP7.4/vendor/autoload.php';
        //include_once dirname(__FILE__) . '/stripe-php/init.php';
//        if (ENV == 'live') {
//            $this->publishable_key = 'pk_live_xikoJK3piFoIvgUgvz8wfsN000CtztAYSd';
//            $this->secret_key = 'sk_live_21yE6cimzdvqs1v4N1jcfUaU00jgxzh3s9';
//        } else {
//            $this->publishable_key = 'pk_test_PGu3tHgt28WnXtSyX5R8Yg4b004V9SJypw';
//            $this->secret_key = 'sk_test_UsIQFzySI1SZUIXXejLPufm6002Oa44VfP';
//        }
    }

    public function youtube() {
        $client = new Google_Client();
        //$client->setApplicationName("LinkStream");
        //$client->setDeveloperKey("AIzaSyCM1-izp7ntGrIGN1enJBXJIRhj7wP5luY");

        $client->setAccessToken('AIzaSyCM1-izp7ntGrIGN1enJBXJIRhj7wP5luY');
        $youtube = new Google_Service_YouTube($client);

        $htmlBody = '';
        if ($client->getAccessToken()) {
            echo 'ENTRA';

            try {
                // REPLACE this value with the path to the file you are uploading.
                //$videoPath = "/path/to/file.mp4";

                $this->temp_dir = FCPATH . 'tmp';
                $file_name = 'file_example_MP3_1MG.mp3';
                $videoPath = $this->temp_dir . '/' . $file_name;

                // Create a snippet with title, description, tags and category ID
                // Create an asset resource and set its snippet metadata and type.
                // This example sets the video's title, description, keyword tags, and
                // video category.
                $snippet = new Google_Service_YouTube_VideoSnippet();
                $snippet->setTitle("Test title");
                $snippet->setDescription("Test description");
                $snippet->setTags(array("tag1", "tag2"));

                // Numeric video category. See
                // https://developers.google.com/youtube/v3/docs/videoCategories/list
                $snippet->setCategoryId("22");

                // Set the video's status to "public". Valid statuses are "public",
                // "private" and "unlisted".
                $status = new Google_Service_YouTube_VideoStatus();
                $status->privacyStatus = "public";

                // Associate the snippet and status objects with a new video resource.
                $video = new Google_Service_YouTube_Video();
                $video->setSnippet($snippet);
                $video->setStatus($status);

                // Specify the size of each chunk of data, in bytes. Set a higher value for
                // reliable connection as fewer chunks lead to faster uploads. Set a lower
                // value for better recovery on less reliable connections.
                $chunkSizeBytes = 1 * 1024 * 1024;

                // Setting the defer flag to true tells the client to return a request which can be called
                // with ->execute(); instead of making the API call immediately.
                $client->setDefer(true);

                // Create a request for the API's videos.insert method to create and upload the video.
                $insertRequest = $youtube->videos->insert("status,snippet", $video);

                // Create a MediaFileUpload object for resumable uploads.
                $media = new Google_Http_MediaFileUpload(
                        $client,
                        $insertRequest,
                        'video/*',
                        null,
                        true,
                        $chunkSizeBytes
                );
                $media->setFileSize(filesize($videoPath));


                // Read the media file and upload it chunk by chunk.
                $status = false;
                $handle = fopen($videoPath, "rb");
                while (!$status && !feof($handle)) {
                    $chunk = fread($handle, $chunkSizeBytes);
                    $status = $media->nextChunk($chunk);
                }

                fclose($handle);

                // If you want to make other calls after the file upload, set setDefer back to false
                $client->setDefer(false);


                $htmlBody .= "<h3>Video Uploaded</h3><ul>";
                $htmlBody .= sprintf('<li>%s (%s)</li>',
                        $status['snippet']['title'],
                        $status['id']);

                $htmlBody .= '</ul>';
            } catch (Google_Service_Exception $e) {
                $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
                        htmlspecialchars($e->getMessage()));
            } catch (Google_Exception $e) {
                $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
                        htmlspecialchars($e->getMessage()));
            }
        }
        print_r($htmlBody);

//        $service = new Google_Service_Books($client);
//        $optParams = array(
//            'filter' => 'free-ebooks',
//            'q' => 'Henry David Thoreau'
//        );
//        $results = $service->volumes->listVolumes($optParams);
//
//        foreach ($results->getItems() as $item) {
//            echo $item['volumeInfo']['title'], "<br /> \n";
//        }
//
//        print_r($youTubeService);
    }

    public function youtube555() {


        /**
         * Library Requirements
         *
         * 1. Install composer (https://getcomposer.org)
         * 2. On the command line, change to this directory (api-samples/php)
         * 3. Require the google/apiclient library
         *    $ composer require google/apiclient:~2.0
         */
//if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
//  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
//}
//
//require_once __DIR__ . '/vendor/autoload.php';
//session_start();
//
///*
// * You can acquire an OAuth 2.0 client ID and client secret from the
// * Google API Console <https://console.developers.google.com/>
// * For more information about using OAuth 2.0 to access Google APIs, please see:
// * <https://developers.google.com/youtube/v3/guides/authentication>
// * Please ensure that you have enabled the YouTube Data API for your project.
// */
        $OAUTH2_CLIENT_ID = '117861365621076635720';
        $OAUTH2_CLIENT_SECRET = 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImIxNmRlMWIyYWIwYzE2YWMwYWNmNjYyZWYwMWY3NTY3ZTU0NDI1MmEiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJhY2NvdW50cy5nb29nbGUuY29tIiwiYXpwIjoiNTA4NjIwMzMyMDcxLWJsdTdzZDl0M29zZ2M1NnNnMWhucTltO';

        $client = new Google_Client();
        $client->setClientId($OAUTH2_CLIENT_ID);
        $client->setClientSecret($OAUTH2_CLIENT_SECRET);
        $client->setScopes('https://www.googleapis.com/auth/youtube');
        $redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
                FILTER_SANITIZE_URL);
        $client->setRedirectUri($redirect);

// Define an object that will be used to make all API requests.
        $youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes
        $tokenSessionKey = 'token-' . $client->prepareScopes();
        if (isset($_GET['code'])) {
            if (strval($_SESSION['state']) !== strval($_GET['state'])) {
                die('The session state did not match.');
            }

            $client->authenticate($_GET['code']);
            $_SESSION[$tokenSessionKey] = $client->getAccessToken();
            header('Location: ' . $redirect);
        }

        if (isset($_SESSION[$tokenSessionKey])) {
            $client->setAccessToken($_SESSION[$tokenSessionKey]);
        }

// Check to ensure that the access token was successfully acquired.
        if ($client->getAccessToken()) {
            $htmlBody = '';
            try {
                // REPLACE this value with the path to the file you are uploading.
                $videoPath = "/path/to/file.mp4";

                // Create a snippet with title, description, tags and category ID
                // Create an asset resource and set its snippet metadata and type.
                // This example sets the video's title, description, keyword tags, and
                // video category.
                $snippet = new Google_Service_YouTube_VideoSnippet();
                $snippet->setTitle("Test title");
                $snippet->setDescription("Test description");
                $snippet->setTags(array("tag1", "tag2"));

                // Numeric video category. See
                // https://developers.google.com/youtube/v3/docs/videoCategories/list
                $snippet->setCategoryId("22");

                // Set the video's status to "public". Valid statuses are "public",
                // "private" and "unlisted".
                $status = new Google_Service_YouTube_VideoStatus();
                $status->privacyStatus = "public";

                // Associate the snippet and status objects with a new video resource.
                $video = new Google_Service_YouTube_Video();
                $video->setSnippet($snippet);
                $video->setStatus($status);

                // Specify the size of each chunk of data, in bytes. Set a higher value for
                // reliable connection as fewer chunks lead to faster uploads. Set a lower
                // value for better recovery on less reliable connections.
                $chunkSizeBytes = 1 * 1024 * 1024;

                // Setting the defer flag to true tells the client to return a request which can be called
                // with ->execute(); instead of making the API call immediately.
                $client->setDefer(true);

                // Create a request for the API's videos.insert method to create and upload the video.
                $insertRequest = $youtube->videos->insert("status,snippet", $video);

                // Create a MediaFileUpload object for resumable uploads.
                $media = new Google_Http_MediaFileUpload(
                        $client,
                        $insertRequest,
                        'video/*',
                        null,
                        true,
                        $chunkSizeBytes
                );
                $media->setFileSize(filesize($videoPath));


                // Read the media file and upload it chunk by chunk.
                $status = false;
                $handle = fopen($videoPath, "rb");
                while (!$status && !feof($handle)) {
                    $chunk = fread($handle, $chunkSizeBytes);
                    $status = $media->nextChunk($chunk);
                }

                fclose($handle);

                // If you want to make other calls after the file upload, set setDefer back to false
                $client->setDefer(false);


                $htmlBody .= "<h3>Video Uploaded</h3><ul>";
                $htmlBody .= sprintf('<li>%s (%s)</li>',
                        $status['snippet']['title'],
                        $status['id']);

                $htmlBody .= '</ul>';
            } catch (Google_Service_Exception $e) {
                $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
                        htmlspecialchars($e->getMessage()));
            } catch (Google_Exception $e) {
                $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
                        htmlspecialchars($e->getMessage()));
            }

            $_SESSION[$tokenSessionKey] = $client->getAccessToken();
        } elseif ($OAUTH2_CLIENT_ID == '117861365621076635720') {
            $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
        } else {
            // If the user hasn't authorized the app, initiate the OAuth flow
            $state = mt_rand();
            $client->setState($state);
            $_SESSION['state'] = $state;

            $authUrl = $client->createAuthUrl();
            $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
        }print_r($htmlBody);
    }

    public function youtube_post_video($youtube_video) {
        $response = [];
        $OAUTH2_CLIENT_ID = '508620332071-blu7sd9t3osgc56sg1hnq9m8mu9a6tda.apps.googleusercontent.com';
        $OAUTH2_CLIENT_SECRET = 'tdHz4NpGzjI1s_OGVNwyYY76';

        $client = new Google_Client();
        $client->setClientId($OAUTH2_CLIENT_ID);
        $client->setClientSecret($OAUTH2_CLIENT_SECRET);



        // Define an object that will be used to make all API requests.
        $youtube = new Google_Service_YouTube($client);
        //$client->authenticate($youtube_video['code']);
        $client->setAccessToken($youtube_video['access_token']);
        if ($client->getAccessToken()) {
            try {
                $videoPath = $youtube_video['video_output'];
                // Create a snippet with title, description, tags and category ID
                // Create an asset resource and set its snippet metadata and type.
                // This example sets the video's title, description, keyword tags, and
                // video category.
                $snippet = new Google_Service_YouTube_VideoSnippet();
                $snippet->setTitle($youtube_video['title']);
                $snippet->setDescription($youtube_video['description']);
                $snippet->setTags($youtube_video['tags']);
                print_r($youtube_video['tags']);print_r($snippet);exit;
                // Numeric video category. See
                // https://developers.google.com/youtube/v3/docs/videoCategories/list
                //$snippet->setCategoryId("22");
                // Set the video's status to "public". Valid statuses are "public",
                // "private" and "unlisted".
                $status = new Google_Service_YouTube_VideoStatus();
                $status->privacyStatus = $youtube_video['privacy'];

                // Associate the snippet and status objects with a new video resource.
                $video = new Google_Service_YouTube_Video();
                $video->setSnippet($snippet);
                $video->setStatus($status);

                // Specify the size of each chunk of data, in bytes. Set a higher value for
                // reliable connection as fewer chunks lead to faster uploads. Set a lower
                // value for better recovery on less reliable connections.
                $chunkSizeBytes = 1 * 1024 * 1024;

                // Setting the defer flag to true tells the client to return a request which can be called
                // with ->execute(); instead of making the API call immediately.
                $client->setDefer(true);

                // Create a request for the API's videos.insert method to create and upload the video.
                $insertRequest = $youtube->videos->insert("status,snippet", $video);

                // Create a MediaFileUpload object for resumable uploads.
                $media = new Google_Http_MediaFileUpload(
                        $client,
                        $insertRequest,
                        'video/*',
                        null,
                        true,
                        $chunkSizeBytes
                );
                $media->setFileSize(filesize($videoPath));

                // Read the media file and upload it chunk by chunk.
                $status = false;
                $handle = fopen($videoPath, "rb");
                while (!$status && !feof($handle)) {
                    $chunk = fread($handle, $chunkSizeBytes);
                    $status = $media->nextChunk($chunk);
                }

                fclose($handle);

                // If you want to make other calls after the file upload, set setDefer back to false
                $client->setDefer(false);


                $response['title'] = $status['snippet']['title'];
                $response['id'] = $status['id'];
                $response['status'] = TRUE;
            } catch (Google_Service_Exception $e) {
//                $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
//                        htmlspecialchars($e->getMessage()));
                $response['status'] = FALSE;
                $response['message'] = $e->getMessage();
            } catch (Google_Exception $e) {
//                $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
//                        htmlspecialchars($e->getMessage()));
                $response['status'] = FALSE;
                $response['message'] = $e->getMessage();
            }
        } else {
            $response['status'] = FALSE;
            $response['message'] = 'Not Found AccessToken';
        }

        return $response;
    }

//    public function create_payment_method($type, $card) {
//        $response = [];
//        $this->stripe = new \Stripe\StripeClient($this->secret_key);
//        try {
//            $object = $this->stripe->paymentMethods->create([
//                'type' => $type,
//                'card' => $card,
//            ]);
////            echo '<pre>';
////            print_r($object);
////            echo '</pre>';
//            $response['status'] = true;
//            $response['payment_method_id'] = $object->id;
//        } catch (Exception $e) {
//            $this->api_error = $e->getMessage();
//            $response['status'] = false;
//            $response['error'] = $this->api_error;
//        }
//        return $response;
//    }
//
//    public function create_customer($name, $email, $phone, $address, $shipping, $payment_method, $description, $metadata) {
//        $response = [];
//        $this->stripe = new \Stripe\StripeClient($this->secret_key);
//        try {
//            $object = $this->stripe->customers->create([
//                'name' => $name,
//                'email' => $email,
//                //'phone' => $phone,
//                'payment_method' => $payment_method,
//                'description' => $description,
//                'metadata' => $metadata,
//                'invoice_settings' => ['default_payment_method' => $payment_method],
//            ]);
////            echo '<pre>';
////            print_r($object);
////            echo '</pre>';
//            $response['status'] = true;
//            $response['customer_id'] = $object->id;
//        } catch (Exception $e) {
//            $this->api_error = $e->getMessage();
//            $response['status'] = false;
//            $response['error'] = $this->api_error;
//        }
//        return $response;
//    }
//
//    public function create_subscription($customer_id, $plan, $price, $default_payment_method) {
//        $response = [];
//        $this->stripe = new \Stripe\StripeClient($this->secret_key);
//        try {
//            $object = $this->stripe->subscriptions->create([
//                'customer' => $customer_id,
//                'items' => [['price' => $price]],
//            ]);
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
//            $response['status'] = true;
//            $response['subscription_id'] = $object->id;
//        } catch (Exception $e) {
//            $this->api_error = $e->getMessage();
//            $response['status'] = false;
//            $response['error'] = $this->api_error;
//        }
//        return $response;
//    }
//
//    public function create_account($country, $email, $business_type, $external_account) {
//        $response = [];
//        $this->stripe = new \Stripe\StripeClient($this->secret_key);
//        $type = 'custom'; //API custom
//        $requested_capabilities = [
//            'card_payments',
//            'transfers',
//        ];
//        try {
//            $object = $this->stripe->accounts->create([
//                'type' => $type,
//                'country' => $country,
//                'email' => $email,
//                'business_type' => $business_type,
//                'requested_capabilities' => $requested_capabilities,
//                'external_account' => $external_account
//            ]);
//            echo '<pre>';
//            print_r($object);
//            echo '</pre>';
////            $response['status'] = true;
////            $response['payment_method_id'] = $object->id;
//        } catch (Exception $e) {
//            $this->api_error = $e->getMessage();
//            $response['status'] = false;
//            $response['error'] = $this->api_error;
//        }
//        return $response;
//    }
//
//    //
//    public function express_account($country, $email) {
//        $response = [];
//        $this->stripe = new \Stripe\StripeClient($this->secret_key);
//        try {
//            $requested_capabilities = [
//                //'card_payments',
//                'transfers',
//            ];
//            $object = $this->stripe->accounts->create([
////                'country' => $country,
////                'type' => 'custom',
////                'email' => $email,
////                'requested_capabilities' => $requested_capabilities,
////                
//                'country' => $country,
//                'type' => 'express'
//            ]);
////            echo '<pre>';
////            print_r($object);
////            echo '</pre>';
//            $response['status'] = true;
//            $response['account_id'] = $object->id;
//        } catch (Exception $e) {
//            $this->api_error = $e->getMessage();
//            $response['status'] = false;
//            $response['error'] = $this->api_error;
//        }
//        return $response;
//    }
//
//    public function express_account_complex($country, $email, $external_account, $business_type, $business_profile, $individual, $tos_acceptance) {
//        $response = [];
//        $this->stripe = new \Stripe\StripeClient($this->secret_key);
//        $requested_capabilities = [
//            //'card_payments',
//            'transfers',
//        ];
//        try {
//            $object = $this->stripe->accounts->create([
//                'country' => $country,
//                'type' => 'custom',
//                'email' => $email,
//                'requested_capabilities' => $requested_capabilities,
//                'business_type' => $business_type,
//                'external_account' => $external_account,
//                'business_profile' => $business_profile,
//                'individual' => $individual,
//                'tos_acceptance' => $tos_acceptance
//            ]);
////            echo '<pre>';
////            print_r($object);
////            echo '</pre>';
//            $response['status'] = true;
//            $response['account_id'] = $object->id;
//        } catch (Exception $e) {
//            $this->api_error = $e->getMessage();
//            $response['status'] = false;
//            $response['error'] = $this->api_error;
//        }
//        return $response;
//    }
//
//    public function account_link($account) {
//        $response = [];
//        $this->stripe = new \Stripe\StripeClient($this->secret_key);
//        try {
//            $object = $this->stripe->accountLinks->create([
//                'account' => $account,
//                'refresh_url' => 'http://localhost/api.link.stream',
//                'return_url' => 'http://localhost/api.link.stream/app/confirm_account',
//                'type' => 'account_onboarding',
//            ]);
////            echo '<pre>';
////            print_r($object);
////            echo '</pre>';
//            $response['status'] = true;
//            $response['account_url'] = $object->url;
//        } catch (Exception $e) {
//            $this->api_error = $e->getMessage();
//            $response['status'] = false;
//            $response['error'] = $this->api_error;
//        }
//        return $response;
//    }
//
//    public function retrieve_account($account) {
//        $response = [];
//        $this->stripe = new \Stripe\StripeClient($this->secret_key);
//        try {
//            $object = $this->stripe->accounts->retrieve(
//                    $account
//            );
////            echo '<pre>';
////            print_r($object);
////            echo '</pre>';
//            $response['status'] = true;
//            $response['payouts_enabled'] = $object->payouts_enabled;
//        } catch (Exception $e) {
//            $this->api_error = $e->getMessage();
//            $response['status'] = false;
//            $response['error'] = $this->api_error;
//        }
//        return $response;
//    }
}
