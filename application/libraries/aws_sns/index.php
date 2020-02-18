<?php
require './aws-autoloader.php';
error_reporting(E_ALL);
ini_set("display_errors", 1);

$sns = Aws\Sns\SnsClient::factory(array(
    'credentials' => array(
        'key' => 'AKIAJHATZUU5RMPUJAAQ',
        'secret' => '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',
    ),
	'bucket' => 'logs.etags.com',
    'region' => 'us-east-1',
	'version' => '2010-03-31',
	'debug' => false, // bool|array
	'options' => ['scheme' => 'http',],	
));

$args = array(
    "SenderID" => "eTags",
    "SMSType" => "Transational",
    "Message" => "Hello World! Visit www.etags.com!",
    "PhoneNumber" => "+13055054308"
);

try {
	$result = $sns->publish($args);
	echo "<pre>";
	print_r($result);
	echo "</pre>";
 } catch(Exception $e) {
     echo $e->getMessage(); // I didn´t get exception, i got server error 503
 }
 //14352322707 working
?>