<?php

class Aws_pinpoint {

    private $error;
    private $aws_key;
    private $aws_secret;
    private $s3;
    private $pp;

    public function __construct() {
        //include_once dirname(__FILE__) . '/aws/aws-autoloader.php';
        $this->aws_key = 'AKIAXBDC73PH3KDCPMFN';
        $this->aws_secret = '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';
        $this->pp = Aws\Pinpoint\PinpointClient::factory(array(
                    'credentials' => array(
                        'key' => $this->aws_key,
                        'secret' => $this->aws_secret
                    ),
                    'region' => 'us-east-1',
                    'version' => 'latest',
                    'debug' => false, // bool|array
        ));
    }

    private function cleanPhone($phone) {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) == 10)
            return '+1' . $phone;
        elseif (strlen($phone) == 11)
            return '+' . $phone;;
        return false;
    }

    public function send($phone, $message) {
        try {

//            $result = $this->pp->send_messages(
//                    'ApplicationId'=>"xxx",
//                    'MessageRequest'=>{
//                'Addresses'=> {'+573163781068': {'ChannelType': 'SMS'}}, 
//                        'MessageConfiguration': {'SMSMessage': {'Body': 'hola desde libreria', 'MessageType': 'TRANSACTIONAL'}}
//                        
//                        });
            $phone = $this->cleanPhone($phone);
            $result = $this->pp->sendMessages([
                'ApplicationId' => 'b8bbabd828a145eda2e49dd2a573bddf', // REQUIRED
                'MessageRequest' => [// REQUIRED
                    'Addresses' => [
                        $phone => [
                            //'BodyOverride' => '<string>',
                            'ChannelType' => 'SMS',
                        //'Context' => ['<string>'],
                        // 'RawContent' => '<string>',
//                            'Substitutions' => [
//                                '<__string>' => ['<string>'],
//                            ],
                        // 'TitleOverride' => '<string>',
                        ],
                    // ...
                    ],
                    //'Context' => ['<string>'],
//                    'Endpoints' => [
//                        '<__string>' => [
//                            'BodyOverride' => '<string>',
//                            'Context' => ['<string>'],
//                            'RawContent' => '<string>',
//                            'Substitutions' => [
//                                '<__string>' => ['<string>'],
//                            // ...
//                            ],
//                            'TitleOverride' => '<string>',
//                        ],
//                    // ...
//                    ],
                    'MessageConfiguration' => [// REQUIRED
                        'SMSMessage' => [
                            'Body' => $message,
                            //'Keyword' => '<string>',
                            'MessageType' => 'TRANSACTIONAL',
                        //'OriginationNumber' => '<string>',
                        //'SenderId' => '<string>',
//                            'Substitutions' => [
//                                '<__string>' => ['<string>'],
//                            // ...
//                            ],
                        ],
                    ],
                ],
            ]);
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

    public function fetch_list($bucket = 'files.link.stream', $prefix = null) {
        try {
            $result = $this->s3->listObjects(array('Bucket' => $bucket, 'Prefix' => $prefix));
            $files = $result->getPath('Contents');
            $file_list = array();
            if (!empty($files) && is_array($files))
                foreach ($files as $file) {
//                    $ext = strtolower(pathinfo($file['Key'], PATHINFO_EXTENSION));
//                    if ($ext == 'pdf')
//                        $file_list[] = $file['Key'];
                    $file_list[] = $file['Key'];
                }
            return $file_list;
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function read_file($filename, $bucket = 'files.link.stream') {
        try {
            $handle = fopen('php://temp/', 'wb');
            $result = $this->s3->getObject(
                    array(
                        'Bucket' => $bucket,
                        'Delimiter' => '/',
                        'Key' => $filename,
                        'SaveAs' => $handle
                    )
            );
            $body = $result->get('Body');
            $content = stream_get_contents($handle);
            return $content;
        } catch (Exception $e) {
            // if ($path == 'download')
            //echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function fetch_file($filename, $path = FCPATH . 'download', $bucket = 'files.link.stream') {
        try {
            $result = $this->s3->getObject(
                    array(
                        'Bucket' => $bucket,
                        'Delimiter' => '/',
                        'Key' => $filename,
                        'SaveAs' => $path . '/' . $filename
                    )
            );
            return $result;
        } catch (Exception $e) {
            // if ($path == 'download')
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function move_file($sourceKeyname, $targetKeyname) {
        echo "Try moving $sourceKeyname TO, $targetKeyname\n";
        try {
            $result = $this->s3->copyObject(
                    array(
                        'Bucket' => 'files.link.stream',
                        'Key' => $targetKeyname,
                        'CopySource' => 'files.link.stream/' . $sourceKeyname,
                    )
            );
            return $this->delete_file($sourceKeyname);
            return $result->getMessage();
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function rename_file($sourceKeyname, $targetKeyname) {
        try {
            $result = $this->s3->copyObject(
                    array(
                        'Bucket' => 'files.link.stream',
                        'Key' => $targetKeyname,
                        'CopySource' => 'files.link.stream' . $sourceKeyname,
                    )
            );
            return $this->delete_file($sourceKeyname);
            return $result->getMessage();
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function delete_file($filename, $bucket = 'files.link.stream') {
        try {
            $result = $this->s3->deleteObject(
                    array(
                        'Bucket' => $bucket,
                        'Key' => $filename
                    )
            );
            return $result;
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function put_file($filename) {
        try {
            $result = $this->s3->putObject(
                    array(
                        'Bucket' => 'files.link.stream',
                        'Key' => $filename,
                        'SourceFile' => FCPATH . 'download/' . $filename,
                        'ContentType' => 'application/pdf',
                        'ACL' => 'public-read',
                        'StorageClass' => 'REDUCED_REDUNDANCY',
                        'Metadata' => array(
                            'param1' => 'value 1',
                            'param2' => 'value 2'
                        )
                    )
            );
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function lists() {
        echo "<pre>\n";
        error_reporting(E_ALL);
        ini_set("display_errors", 1);

        $s3 = Aws\S3\S3Client::factory(array(
                    'credentials' => array(
                        'key' => 'AKIAIZL3XSI46NFUZVEA',
                        'secret' => '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'
                    ),
                    'region' => 'us-east-1',
                    'version' => 'latest',
                    'debug' => true, // bool|array
        ));

        try {
            echo "<h1>listObjects</h1>\n";
            $result = $s3->listObjects(array(
                'Bucket' => 'files.link.stream'
            ));
            var_dump($result);
            echo "<h1>Contents</h1>\n";
            $files = $result->getPath('Contents');
            print_r($files);

            echo "<h1>getObject</h1>\n";
            $result = $s3->getObject(
                    array(
                        'Bucket' => 'files.link.stream',
                        'Key' => $files[0]['Key'],
                        'SaveAs' => FCPATH . 'download/demo2.pdf'
                    )
            );
            var_dump($result);
            echo "<h1>Body</h1>\n";
            $body = $result->get('Body');
            var_dump($body);
            $handle = fopen('php://temp', 'r');
            $content = stream_get_contents($handle);
            echo "My String length: " . strlen($content);

            echo "<h1>response</h1>\n";
            // Upload data.
            //$response = $s3->getIterator('ListObjects', array('Bucket' => 'files.link.stream', 'MaxKeys' => 1000, 'Prefix' => 'files/'));
            $response = $s3->getIterator('ListObjects', array('Bucket' => 'files.link.stream'));
            // Print the URL to the object.
            print_r($response);
            //$files = $response->getPath('Contents');
            //print_r($files);
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    public function upload() {
        putenv('HOME=' . FCPATH);
        echo getenv('HOME');
        $tmp = FCPATH . 'download/demo.pdf';
        $filename = 'mynew.pdf';

        $bucket = 'files.link.stream';
        $s3 = Aws\S3\S3Client::factory(array(
                    'credentials' => array(
                        'key' => 'AKIAIZL3XSI46NFUZVEA',
                        'secret' => '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',
                    ),
                    'bucket' => 'files.link.stream',
                    'region' => 'us-east-1',
                    'version' => 'latest',
                    'debug' => true, // bool|array
        ));

        $s3->putBucket($bucket, S3::ACL_PUBLIC_READ);
        echo "Start Upload3";
        if ($s3->putObjectFile($tmp, $bucket, $filename, S3::ACL_PUBLIC_READ)) {
            $message = "S3 Upload Successful.";
            $s3file = 'http://' . $bucket . '.s3.amazonaws.com/' . $filename;
            echo "<img src='$s3file'/>";
            echo 'S3 File URL:' . $s3file;
        } else {
            $message = "S3 Upload Fail.";
        }
        echo $message;
    }

    public function s3push($source, $destination, $bucket = 'files.link.stream') {
        //echo "Start Upload $destination\n";
        // Upload a file.
        try {
            $result = $this->s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $destination,
                'SourceFile' => $source,
                'ContentType' => 'text/plain',
                //'ACL' => 'public-read',
                'StorageClass' => 'STANDARD'
            ));
            //var_dump($result['ObjectURL']);
            //echo "Upload Complete $destination\n";
            return $result['ObjectURL'];
        } catch (Exception $e) {
            //echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function move_file_vin($sourceKeyname, $targetKeyname) {
        //echo "Try moving $sourceKeyname TO, $targetKeyname\n";
        try {
            $result = $this->s3->copyObject(
                    array(
                        'Bucket' => 'files.link.stream',
                        'Key' => $targetKeyname,
                        'CopySource' => 'files.link.stream/' . $sourceKeyname,
                    )
            );
            return $this->delete_file($sourceKeyname);
            return $result->getMessage();
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function fetch_file2($filename, $path = FCPATH . 'download', $bucket = 'files.link.stream', $folder = '') {
        try {
            $result = $this->s3->getObject(
                    array(
                        'Bucket' => $bucket,
                        'Delimiter' => '/',
                        'Key' => $folder . $filename,
                        'SaveAs' => $path . '/' . $filename
                    )
            );
            return $result;
        } catch (Exception $e) {
            //echo $e->getMessage() . "\n";
            return false;
        }
    }

    public function s3_put($bucket, $path, $filename, $data) {
        // Register the stream wrapper from an S3Client object
        $this->s3->registerStreamWrapper();
        //$data = file_get_contents('s3://files.link.stream/doc.pdf');
        //echo $data;
        if (ENV != 'live')
            $path = 'dev_' . $path;
        file_put_contents('s3://' . $bucket . (!empty($path) ? '/' . $path : '') . '/' . $filename, $data);
    }

    public function s3_upload($bucket, $path, $filename, $data) {
        // Register the stream wrapper from an S3Client object
        $this->s3->registerStreamWrapper();
        $path = (ENV == 'live') ? 'PROD/' . $path : 'DEV/' . $path;
        return file_put_contents('s3://' . $bucket . (!empty($path) ? '/' . $path : '') . '/' . $filename, $data);
    }

    public function s3_get($bucket, $path, $filename) {
        // Register the stream wrapper from an S3Client object
        $this->s3->registerStreamWrapper();
        $path = (ENV == 'live') ? 'PROD/' . $path : 'DEV/' . $path;
        return file_get_contents('s3://' . $bucket . (!empty($path) ? '/' . $path : '') . '/' . $filename);
    }

    public function s3_read($bucket, $path, $filename) {
        // Register the stream wrapper from an S3Client object
        $this->s3->registerStreamWrapper();
        //$path = (ENV == 'live')?'PROD/'.$path:'DEV/'.$path;
        return file_get_contents('s3://' . $bucket . (!empty($path) ? '/' . $path : '') . '/' . $filename);
    }

    public function s3_del($bucket, $path, $filename) {
        // Register the stream wrapper from an S3Client object
        $this->s3->registerStreamWrapper();
        $path = (ENV == 'live') ? 'PROD/' . $path : 'DEV/' . $path;
        return unlink('s3://' . $bucket . (!empty($path) ? '/' . $path : '') . '/' . $filename);
    }

    public function s3_up($bucket, $path, $filename, $sourceFile) {
        $path = (ENV == 'live') ? 'PROD/' . $path : 'DEV/' . $path;
        try {
            $result = $this->s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $path . '/' . $filename,
                'SourceFile' => $sourceFile,
                'ContentType' => 'binary/octet-stream',
                'StorageClass' => 'STANDARD'
            ));
            //var_dump($result['ObjectURL']);
            //echo "Upload Complete $destination\n";
            return $result['ObjectURL'];
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            return false;
        }
    }

}
