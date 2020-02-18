<?php

class Aws_sns {

    private $error;

    public function __construct() {
        //require dirname(__FILE__) . '/aws_sns/aws-autoloader.php';
        include_once dirname(__FILE__) . '/aws_sns/aws-autoloader.php';
        $this->aws_key = 'AKIAXBDC73PH3KDCPMFN';
        $this->aws_secret = 'bxG/oH206zApXEsN9NrfZdnsfVf0LT9SktQUvawp';
    }

    private function cleanPhone($phone) {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) == 10)
            return '+1' . $phone;
        elseif (strlen($phone) == 11)
            return '+' . $phone;;
        return false;
    }

    public function check($phone) {
        $phone = $this->cleanPhone($phone);
        $curl = curl_init();
        $agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.81 Safari/537.36';
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_USERAGENT, $agent);
        curl_setopt($curl, CURLOPT_USERPWD, urlencode('ACa75867f635a47414724b6a62ef5d9663') . ':' . urlencode('c830a570cd31551b7d461608f5ebd966'));
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_URL, 'https://lookups.twilio.com/v1/PhoneNumbers/' . $phone . '?Type=carrier&Type=caller-name');
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        // Exec
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        // Data
        $header = trim(substr($response, 0, $info['header_size']));
        $body = substr($response, $info['header_size']);

        $result = array('status' => $info['http_code'], 'header' => $header, 'data' => json_decode($body, true));

        //echo "<pre>\n";
        //print_r($result['data']);
        if ($result['status'] == 200)
            return $result['data']['carrier']['type'];
        else
            return false;
    }

    public function send($phone, $message) {
        //error_reporting(E_ALL);
        //ini_set("display_errors", 1);		
        $sns = Aws\Sns\SnsClient::factory(array(
                    'credentials' => array(
                       'key' => $this->aws_key,
                        'secret' => $this->aws_secret
                    ),
                    'bucket' => 'logs.link.stream',
                    'region' => 'us-east-2',
                    'version' => '2010-03-31',
                    'debug' => false, // bool|array
                    'options' => ['scheme' => 'http',],
        ));

        $args = array(
            "SenderID" => "eTags",
            "SMSType" => "Transactional",
            "Message" => $message,
            "PhoneNumber" => $this->cleanPhone($phone)
        );

        try {
            $result = $sns->publish($args);
            /* echo "<pre>";
              print_r($result);
              echo "</pre>"; */
            return true;
        } catch (Exception $e) {
            echo $e->getMessage(); // I didn´t get exception, i got server error 503
            $this->error = $e->getMessage();
            return false;
        }
    }

}
