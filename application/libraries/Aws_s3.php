<?php

class Aws_s3 {

    private $error;
    private $aws_key;
    private $aws_secret;
    private $s3;

    public function __construct() {
        //include_once dirname(__FILE__) . '/aws_sns/aws-autoloader.php';
        include_once dirname(__FILE__) . '/aws/aws-autoloader.php';
        $this->aws_key = 'AKIAXBDC73PHUL3JUCGP';
        $this->aws_secret = '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';
        $this->s3 = Aws\S3\S3Client::factory(array(
                    'credentials' => array(
                        'key' => $this->aws_key,
                        'secret' => $this->aws_secret
                    ),
                    'region' => 'us-east-2',
                    'version' => 'latest',
                    'debug' => false, // bool|array
        ));
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
        //$path = (ENV == 'live') ? 'PROD/' . $path : 'DEV/' . $path;
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
        return @file_get_contents('s3://' . $bucket . (!empty($path) ? '/' . $path : '') . '/' . $filename);
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

    public function pre_signed_url($bucket, $file_name) {

        //Creating a presigned URL
        $cmd = $this->s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $file_name,
                // Expires: 100 //time to expire in seconds
        ]);

        $request = $this->s3->createPresignedRequest($cmd, '+180 minutes');



        // Get the actual presigned-url
        $presignedUrl = (string) $request->getUri();

        //
        //$encodedCustomerKey = base64_encode($customerKey);
        //$encodedCustomerMd5Key = base64_encode($customerMd5Key);
        //var_dump($presignedUrl);
        //var_dump($encodedCustomerKey); /// DIxGD8n7eLwfAusYvqpLK+Ysql+xYL4jdbR13Z3e08=
        //var_dump($encodedCustomerMd5Key); // Hz1c31UCZ/f5CwxxKnMeAQ==
        //
        return $presignedUrl;
    }

    public function object_url($bucket) {
        $url = $this->s3->getObjectUrl($bucket, 'Dev/Coverart/ls_b010473bdb62681c47a8c1ba59198454.jpeg');
        return $url;
    }

    public function pre_signed_post($bucket, $file_name, $path) {
//        $client = new \Aws\S3\S3Client([
//            'version' => 'latest',
//            'region' => 'us-west-2',
//        ]);
        //$bucket = 'mybucket';
        // Set some defaults for form input fields
        $formInputs = ['acl' => 'public-read', 'key' => $path . $file_name];

        // Construct an array of conditions for policy
        $options = [
            ['acl' => 'public-read'],
            ['bucket' => $bucket],
            ['starts-with', '$key', $path . $file_name],
                //["content-length-range", 100, 10000000],
        ];

        // Optional: configure expiration time string
        $expires = '+30 minutes';

        $postObject = new \Aws\S3\PostObjectV4(
                $this->s3,
                $bucket,
                $formInputs,
                $options,
                $expires
        );

        // Get attributes to set on an HTML form, e.g., action, method, enctype
        $formAttributes = $postObject->getFormAttributes();
        // Get form input fields. This will include anything set as a form input in
        // the constructor, the provided JSON policy, your AWS Access Key ID, and an
        // auth signature.
        $formInputs = $postObject->getFormInputs();
        //return $formInputs;

        return ['formAttributes' => $formAttributes, 'formInputs' => $formInputs];
    }

}
