<?php

class Aws_ses {

    private $error;
    private $aws_key;
    private $aws_secret;
    private $ses;

    public function __construct() {
        //require dirname(__FILE__) . '/aws_sns/aws-autoloader.php';
        include_once dirname(__FILE__) . '/aws/aws-autoloader.php';
        $this->aws_key = 'AKIAXBDC73PHUL3JUCGP';
        $this->aws_secret = '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';
        $this->ses = Aws\Ses\SesClient::factory(array(
                    'credentials' => array(
                        'key' => $this->aws_key,
                        'secret' => $this->aws_secret
                    ),
                    'region' => 'us-east-2',
                    'version' => 'latest',
                    'debug' => false, // bool|array
                        //'profile' => 'default'
        ));
    }

    public function verify_email() {
        $email = 'paul@linkstream.com';

        try {
            $result = $this->ses->verifyEmailIdentity([
                'EmailAddress' => $email, // REQUIRED
            ]);
            echo '<pre>';
            print_r($result);
            echo '</pre>';
        } catch (AwsException $e) {
            // output error message if fails
            echo $e->getMessage();
            echo("The email was not sent. Error message: " . $e->getAwsErrorMessage() . "\n");
            echo "\n";
        }
    }

    public function send_email($sender_email, $reply_to, $recipient_email, $subject, $html_body, $char_set = 'UTF-8') {
        try {

            $result = $this->ses->sendEmail([
                'Destination' => [
                    'ToAddresses' => [$recipient_email],
                ],
                'ReplyToAddresses' => [$reply_to],
                'Source' => $sender_email,
                'Message' => [
                    'Body' => [
                        'Html' => [
                            'Charset' => $char_set,
                            'Data' => $html_body,
                        ],
//                        'Text' => [
//                            'Charset' => $char_set,
//                            'Data' => $plaintext_body,
//                        ],
                    ],
                    'Subject' => [
                        'Charset' => $char_set,
                        'Data' => $subject,
                    ],
                ],
            ]);
            $messageId = $result['MessageId'];
            echo("Email sent! Message ID: $messageId" . "\n");
        } catch (AwsException $e) {
            // output error message if fails
            echo $e->getMessage();
            echo("The email was not sent. Error message: " . $e->getAwsErrorMessage() . "\n");
            echo "\n";
        }
    }

    public function send() {

        // Create an SesClient. Change the value of the region parameter if you're 
// using an AWS Region other than US West (Oregon). Change the value of the
// profile parameter if you want to use a profile in your credentials file
// other than the default.
//        $SesClient = new SesClient([
//            'profile' => 'default',
//            'version' => '2010-12-01',
//            'region' => 'us-west-2'
//        ]);
// Replace sender@example.com with your "From" address.
// This address must be verified with Amazon SES.
        $sender_email = 'paul@linkstream.com';

// Replace these sample addresses with the addresses of your recipients. If
// your account is still in the sandbox, these addresses must be verified.
        $recipient_emails = ['paolofq@gmail.com', 'pferra@sfe.ec'];

// Specify a configuration set. If you do not want to use a configuration
// set, comment the following variable, and the
// 'ConfigurationSetName' => $configuration_set argument below.
        //$configuration_set = 'ConfigSet';

        $subject = 'Amazon SES test (AWS SDK for PHP)';
        $plaintext_body = 'This email was sent with Amazon SES using the AWS SDK for PHP.';
        $html_body = '<h1>AWS Amazon Simple Email Service Test Email</h1>' .
                '<p>This email was sent with <a href="https://aws.amazon.com/ses/">' .
                'Amazon SES</a> using the <a href="https://aws.amazon.com/sdk-for-php/">' .
                'AWS SDK for PHP</a>.</p>';
        $char_set = 'UTF-8';



        try {


            // $result = $this->ses->sendBulkTemplatedEmail([
            $result = $this->ses->sendBulkTemplatedEmail([
                'Destination' => [
                    'ToAddresses' => $recipient_emails,
                ],
//                "Template" => "A",
//                'Destinations' => [
//                    0 => ['Destination' => [
//                            'ToAddresses' => ['paolofq@gmail.com'],
//                        ],
//                    ],
//                    1 => ['Destination' => [
//                            'ToAddresses' => ['pferra@sfe.ec'],
//                        ],
//                    ],
//                ],
//            'Destinations' => [
//            'Destination' => [
//            'ToAddresses' => 'paolofq@gmail.com',
//            ],
//            //"ReplacementTemplateData" => "{ \"name\":\"Paul\", \"favoriteanimal\":\"water buffalo\" }",
//            'Destination' => [
//            'ToAddresses' => 'pferra@sfe.ec',
//            ],
//            //"ReplacementTemplateData" => "{ \"name\":\"Paul\", \"favoriteanimal\":\"water buffalo\" }"
//            ],
//            'Destinations' =>
//            array (
//            0 =>
//            array (
//            'Destination' =>
//            array (
//            'ToAddresses' =>
//            array (
//            0 => 'anaya.iyengar@example.com',
//            ),
//            ),
//            'ReplacementTemplateData' => '{ "name":"Anaya", "favoriteanimal":"yak" }',
//            ),
//            1 =>
//            array (
//            'Destination' =>
//            array (
//            'ToAddresses' =>
//            array (
//            0 => 'liu.jie@example.com',
//            ),
//            ),
//            'ReplacementTemplateData' => '{ "name":"Liu", "favoriteanimal":"water buffalo" }',
//            ),
                'ReplyToAddresses' => [$sender_email],
                'Source' => $sender_email,
                'Message' => [
                    'Body' => [
                        'Html' => [
                            'Charset' => $char_set,
                            'Data' => $html_body,
                        ],
                        'Text' => [
                            'Charset' => $char_set,
                            'Data' => $plaintext_body,
                        ],
                    ],
                    'Subject' => [
                        'Charset' => $char_set,
                        'Data' => $subject,
                    ],
                ],
                    // If you aren't using a configuration set, comment or delete the
                    // following line
                    //'ConfigurationSetName' => $configuration_set,
            ]);
            $messageId = $result['MessageId'];
            echo("Email sent! Message ID: $messageId" . "\n");
        } catch (AwsException $e) {
            // output error message if fails
            echo $e->getMessage();
            echo("The email was not sent. Error message: " . $e->getAwsErrorMessage() . "\n");
            echo "\n";
        }
    }

}
