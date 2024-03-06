<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '../../vendor/autoload.php';
use Aws\S3\S3Client;

class Space {

    private static $access_key = NULL; // AWS Access key
    private static $secret_key = NULL; // AWS Secret key
    private static $region = '';
    private static $bucket = '';
    private static $endpoing = '';
	function __construct($config = array())
    {
        if ( ! empty($config))
        {
            self::$access_key = $config['key'];
            self::$secret_key = $config['secret'];
            self::$region = $config['region'];
            self::$bucket = $config['bucket'];
            self::$endpoing = "https://".self::$region.".digitaloceanspaces.com";
        }

        log_message('debug', 'DO Class Initialized');
    }

	//DELETE USING SPACE
    public function space_delete($keyname){
        try{
        $client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => self::$region,
            'endpoint' => "https://".self::$region.".digitaloceanspaces.com",
            'credentials' => [
                'key'    => self::$access_key,
                'secret' => self::$secret_key,
            ],
        ]);
            $client->deleteObject([
                'Bucket' => self::$bucket,
                'Key'    => $keyname
            ]);
            return true;
        }catch(Exception $e){
            return false;
        }
        exit;
    }
    //geting image 
    public function space_getinfo($keyname){
        try{
            $client = new Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => self::$region,
                'endpoint' => "https://".self::$region.".digitaloceanspaces.com",
                'credentials' => [
                    'key'    => self::$access_key,
                    'secret' => self::$secret_key,
                ],
            ]);
            $result = $client->getObject([
                'Bucket' => self::$bucket,
                'Key'    => $keyname
            ]);
            //header("Content-Type: {$result['ContentType']}");
            if(!empty($result['Body'])){
                return true;
            }
        }catch(Exception $e){
            return false;
        }
    }
// Configure a client using Spaces
    public function space_upload($filePath,$temp_file){
        try{
        $client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => self::$region,
            'endpoint' => self::$endpoing,
            'credentials' => [
                'key'    => self::$access_key,
                'secret' => self::$secret_key,
            ],
        ]);
    }catch(Exception $c){
        echo 'Caught exception: ',  $c->getMessage(), "\n";exit;
    }
        try {
         $metaHeaders                            = array();
         $requestHeaders['Content-Type']         = 'application/csv';
         $requestHeaders['Content-Disposition']  = 'attachment; filename="'.$temp_file.'";';
         // Upload a file to the Space
         $insert = $client->putObject([
            'ACL'          =>  'public-read',
            'Bucket'       =>  self::$bucket,
            'Key'          =>  $filePath,
            'SourceFile'   =>  $temp_file
        ]);
         return $insert['ObjectURL'] . PHP_EOL;
     }catch (Exception $e) {
        return false;
        //return 'Caught exception: '.$e->getMessage()."\n";
    }
    exit;
}

public static function inputFile($file, $md5sum = true)
    {
        if (!file_exists($file) || !is_file($file) || !is_readable($file))
        {
            trigger_error('S3::inputFile(): Unable to open input file: ' . $file, E_USER_WARNING);
            return false;
        }
        return array('file' => $file, 'size' => filesize($file),
            'md5sum' => $md5sum !== false ? (is_string($md5sum) ? $md5sum : base64_encode(md5_file($file, true))) : '', 
            'sha256sum' => hash_file('sha256', $file));
    }
}
?>
