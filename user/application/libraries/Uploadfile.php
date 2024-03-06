<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Uploadfile {
    public $access_key = "";
    public $secret_key = "";
    public $bucket = "";
    public $region = "";
    public $use_ssl = "";
    public $verify_peer = "";
    public $lib_config = array();
    function __construct($config = array())
    {
        $this->access_key = BUCKET_ACCESS_KEY;
        $this->secret_key = BUCKET_SECRET_KEY;
        $this->bucket = BUCKET;
        $this->region = BUCKET_REGION;
        $this->use_ssl = BUCKET_USE_SSL;
        $this->verify_peer = BUCKET_VERIFY_PEER;

        $this->lib_config = [
            'key'=>$this->access_key,
            'secret'=>$this->secret_key,
            'region'=>$this->region,
            'bucket'=>$this->bucket
        ];
    }

    public function upload_file($post_data){
        $result = "";
        if(BUCKET_TYPE == "CJ"){
            $result = $this->minio_file_upload($post_data);
        }else if(BUCKET_TYPE == "DO"){
            $result = $this->space_file_upload($post_data);
        }else{
            $result = $this->aws_file_upload($post_data);
        }
        return $result;
    }

    public function get_file_info($post_data){
        $result = "";
        if(BUCKET_TYPE == "CJ"){
            $result = $this->minio_get_file($post_data);
        }else if(BUCKET_TYPE == "DO"){
            $result = $this->space_get_file($post_data);
        }else{
            $result = $this->aws_get_file($post_data);
        }
        return $result;
    }

    public function delete_file($post_data){
        $result = "";
        if(BUCKET_TYPE == "CJ"){
            $result = $this->minio_delete_file($post_data);
        }else if(BUCKET_TYPE == "DO"){
            $result = $this->space_delete_file($post_data);
        }else{
            $result = $this->aws_delete_file($post_data);
        }
        return $result;
    }

    public function minio_file_upload($data_arr){
        $CI =& get_instance();
        $CI->load->library('minio');
        $this->lib_config['protocol'] = HTTP_PROTOCOL;
        $minio = new Minio($this->lib_config);
        $is_do_upload = $minio->minio_upload($data_arr['file_path'],$data_arr['source_path']);
        return $is_do_upload;
    }

    public function space_file_upload($data_arr){
        $CI =& get_instance();
        $CI->load->library('space');
        $space = new Space($this->lib_config);
        $is_do_upload = $space->space_upload($data_arr['file_path'],$data_arr['source_path']);
        return $is_do_upload;
    }

    public function aws_file_upload($data_arr){
        $CI =& get_instance();
        $CI->load->library('S3');
        $s3 = new S3(array("access_key" => $this->access_key, "secret_key" => $this->secret_key, "region" => $this->region, "use_ssl" => $this->use_ssl, "verify_peer" => $this->verify_peer));
        $headers = array("Cache-Control" => "max-age=315360000", "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+1 years")));
        $is_do_upload = $s3->putObjectFile($data_arr['source_path'], $this->bucket, $data_arr['file_path'], S3::ACL_PUBLIC_READ, $headers);
        if (isset($is_do_upload['result']) && $is_do_upload['result'] == 'error') {
            return false;
        }
        return true;
    }

    public function minio_get_file($data_arr){
        $CI =& get_instance();
        $CI->load->library('minio');
        $this->lib_config['protocol'] = HTTP_PROTOCOL;
        $minio = new Minio($this->lib_config);
        $file_info = $minio->minio_getinfo($data_arr['file_path']);
        return $file_info;
    }

    public function space_get_file($data_arr){
        $CI =& get_instance();
        $CI->load->library('space');
        $space = new Space($this->lib_config);
        $file_info = $space->space_getinfo($data_arr['file_path']);
        return $file_info;
    }

    public function aws_get_file($data_arr){
        $CI =& get_instance();
        $CI->load->library('S3');
        $s3 = new S3(array("access_key" => $this->access_key, "secret_key" => $this->secret_key, "region" => $this->region, "use_ssl" => $this->use_ssl, "verify_peer" => $this->verify_peer));
        $file_info = $s3->getObjectInfo($this->bucket, $data_arr['file_path']);
        return $file_info;
    }

    public function minio_delete_file($data_arr){
        $CI =& get_instance();
        $CI->load->library('minio');
        $this->lib_config['protocol'] = HTTP_PROTOCOL;
        $minio = new Minio($this->lib_config);
        $is_deleted = $minio->minio_delete($data_arr['file_path']);
        if($is_deleted){
            return true;
        }
        return false;
    }

    public function space_delete_file($data_arr){
        $CI =& get_instance();
        $CI->load->library('space');
        $space = new Space($this->lib_config);
        $is_deleted = $space->space_delete($data_arr['file_path']);
        if($is_deleted){
            return true;
        }
        return false;
    }

    public function aws_delete_file($data_arr){
        $CI =& get_instance();
        $CI->load->library('S3');
        $s3 = new S3(array("access_key" => $this->access_key, "secret_key" => $this->secret_key, "region" => $this->region, "use_ssl" => $this->use_ssl, "verify_peer" => $this->verify_peer));
        $is_deleted = $s3->deleteObject($this->bucket, $data_arr['file_path']);
        if($is_deleted){
            return true;
        }
        return false;
    }
}
?>