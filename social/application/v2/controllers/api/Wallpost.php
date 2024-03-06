<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once getcwd(). '/vendor/autoload.php';

use Embed\Embed;

class Wallpost extends Common_API_Controller {

    public $UserID = '';

    function __construct() {
        parent::__construct();
        $this->check_module_status(2);
        $this->load->model(array('group/group_model', 'pages/page_model', 'flag_model', 'users/user_model', 'subscribe_model', 'notification_model', 'activity/activity_model'));
    }
   
    public function parseLinkData_old_post() {
        $this->load->library('ParseUrl');
        $this->load->helper('parse_url');

        $Return = $this->return;
        
        $Data           		= $this->post_data;
        $Data['url'] 			=  get_url_source($Data['url']);
        $is_youtube 			= 0;
        $return_array = array();

        $return_array['url'] =  $Data['url'];

        $url = urldecode($Data['url']);

        if(substr($url, -1)!=='/') $url = $url.'/';

        $domainName = get_domain($url);
        $youtubeID = get_youtube_id_from_url($Data['url']);

        $images = "";
        $imagesArray = array();

        $vimeoThumb = get_vimeo_thumb($Data['url']);
        $url = check_url_values($url);
        
        $base_url = substr($url,0, strpos($url, "/",8));
        $relative_url = substr($url,0, strrpos($url, "/")+1);
        // Get Data
        $cc = new ParseUrl();
        $string = $cc->get($url);
        // Parse Title
        $nodes = extract_url_tags($string, 'title');
        if($nodes && trim($nodes[0]['contents']=='Access Denied')) {
            $string = $html = file_get_contents($url);
            /*$ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  # Set curl to return the data instead of printing it to the 
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12');

            $string = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($string, 0, $header_size);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);*/
        }
        
        $nodes = extract_url_tags($string, 'title');

        $string = str_replace(array("\n","\r","\t",'</span>','</div>'), '', $string);

        $string = preg_replace('/(<(div|span)\s[^>]+\s?>)/',  '', $string);
        if (mb_detect_encoding($string, "UTF-8") != "UTF-8")
            $string = utf8_encode($string);

     

        if($nodes && trim($nodes[0]['contents']!='Access Denied')) {        
            $return_array['domainName'] = $domainName;
            $return_array['title']      = trim($nodes[0]['contents']);

            $return_array['title'] = strlen($return_array['title']) > 70 ? substr($return_array['title'],0,70).".." : $return_array['title'];

            // Parse Description
            $return_array['description'] = '';
            $nodes = extract_url_tags( $string, 'meta' );
        
            foreach($nodes as $node) {
                if (isset($node['attributes']['name']) && strtolower($node['attributes']['name']) == 'description') {
                    //$return_array['description'] =  substr(trim($node['attributes']['content']),0,75);
                    $return_array['description'] = strlen($node['attributes']['content']) > 110 ? substr($node['attributes']['content'],0,110).".." : $node['attributes']['content'];
                } 

                if(isset($node['attributes']['property']) && $node['attributes']['property']=='og:image') {
                    $images =   $node['attributes']['content'];
                }
                if(isset($node['attributes']['property']) && $node['attributes']['property']=='og:title') {
                    $return_array['title'] = $node['attributes']['content'];
                }
            }

            // Parse Images   
            if(!empty($youtubeID)) {
               // $time = time().generateRandomCode();  
                $source_image = $images =  "https://img.youtube.com/vi/".$youtubeID."/0.jpg";
                $imagesArray[0] = $images = $this->put_image($source_image);

                /* $dest_http_path = 'upload/wall/'.$time.'.jpg';
                $dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
                file_put_contents($dest_image_path,  file_get_contents($source_image));
                $imagesArray[0] = $dest_http_path;
                $images = $dest_http_path;
                 * 
                 */
                //print_r($imagesArray);
            } elseif(!empty($vimeoThumb)) {   
                //$time = time().generateRandomCode();
                $images = $vimeoThumb;
            } else {  
                $images_array = extract_url_tags( $string, 'img' );
                //print_r($images_array);
                $imgPng = "";
                for ($i=0;$i<=sizeof($images_array);$i++) {
                    //$time = time().generateRandomCode();
                    if($images=="") {
                        $img = trim(@$images_array[$i]['attributes']['src']);
                        $ext = trim(pathinfo($img, PATHINFO_EXTENSION));

                        if($ext=='png') {
                            $imgPng =  $img;
                        }

                        if($img && ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'bmp') ) {   
                            if (substr($img,0,7) == 'http:/' || substr($img,0,7) == 'https:/' || substr($img,0,7) == 'http://' || substr($img,0,7) == 'https://');
                            else
                               $img = $relative_url . $img;

                            //$dest_http_path = 'upload/wall/'.$time.'.jpg';
                            //$dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';                             

                            if (!is_404($img)) {
                                $dest_http_path = $this->put_image($img);
                                //file_put_contents($dest_image_path,  file_get_contents($img));
                                if(check_url_values($img)) {
                                    $images = $img;
                                    $imagesArray[] = $dest_http_path;
                                }
                            } else {
                                $url1 = $img;
                                $params = explode('.', $url1);

                                if($params[0] == 'http://www' || $params[0] == 'https://www') {		                         
                                    $img= str_replace('www.', '', $img);
                                } else {
                                    $img= str_replace('://', '://www.', $img);
                                }		                        

                                if(!is_404($img)) {
                                    $dest_http_path = $this->put_image($img);
                                    //file_put_contents($dest_image_path,  file_get_contents($img));
                                    if(check_url_values($img)) {
                                        $images = $img;
                                        $imagesArray[] = $dest_http_path;
                                    }		                                          
                                }
                            }
                        }
                    } else {

                        /*make array of images*/
                        $img = trim(@$images_array[$i]['attributes']['src']);
                        $ext = trim(pathinfo($img, PATHINFO_EXTENSION));
                        
                        if($ext=='png') {
                            $images="";
                            $imgPng =  $img;
                        }
           
                        if ($img && ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'bmp')) {
                            if (substr($img,0,7) == 'http:/' || substr($img,0,7) == 'https:/' || substr($img,0,7) == 'http://' || substr($img,0,7) == 'https://');
                            else
                               $img = $relative_url . $img;

                            //$dest_http_path = 'upload/wall/'.$time.'.jpg';
                            //$dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';                          

                            if (!is_404($img)) {
                                $dest_http_path = $this->put_image($img);
                                //file_put_contents($dest_image_path,  file_get_contents($img)); 
                                if (check_url_values($img)) {	                        
                                    $imagesArray[] = $dest_http_path;
                                }
                            } else {
                                $url1 = $img;
                                $params = explode('.', $url1);

                                if ($params[0] == 'http://www' || $params[0] == 'https://www') {
                                    $img = str_replace('www.', '', $img);
                                } else {
                                    $img = str_replace('://', '://www.', $img);
                                }

                                if (!is_404($img)) {
                                    $dest_http_path = $this->put_image($img);
                                    //file_put_contents($dest_image_path,  file_get_contents($img)); 
                                    if (check_url_values($img)) {	                                   
                                        $imagesArray[] = $dest_http_path;
                                    }
                                }
                            }
                        }

                    }    /*end make array of images*/

                    if(count($imagesArray)>5) {
                        break;
                    }    
                }

            	if($images=="" && $imgPng!='') {
                    $img= $imgPng;
                
                    if (substr($img,0,7) == 'http:/' || substr($img,0,7) == 'https:/' || substr($img,0,7) == 'http://' || substr($img,0,7) == 'https://')
                            ;
                    else if (substr($img,0,1) == '/')
                        $img = $base_url . $img;
                    else
                        $img = $relative_url . $img;

                    if (!is_404($img))  {
                        
                        if(check_url_values($img)) {
                            //$dest_http_path = 'upload/wall/'.$time.'.jpg';
                            //$dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
                            //file_put_contents($dest_image_path,  file_get_contents($img)); 
                            $dest_http_path = $this->put_image($img);
                            //$images = $img;
                            $images = $dest_http_path;
                        }
                    } else {
                        $url1 = $img;
                        $params = explode('.', $url1);

                        if($params[0] == 'http://www' || $params[0] == 'https://www')  {	                     
                            $img= str_replace('www.', '', $img);
                        } else {
                            $img= str_replace('://', '://www.', $img);
                        }

                        if(!is_404($img)) {
                            if(check_url_values($img)) {                        
                               // $dest_http_path = 'upload/wall/'.$time.'.jpg';
                               // $dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
                               // file_put_contents($dest_image_path,  file_get_contents($img)); 
                                $dest_http_path = $this->put_image($img);
                                //$images = $img;
                                $images = $dest_image_path;
                            }	                                      
                        }
                    }
                }
            }
    	}

        $return_array['images'] = $imagesArray;
        //echo $images;die;
        
        if($images!='' && empty($youtubeID)) {
            //$dest_http_path = 'upload/wall/'.$time.'.jpg';
            //$dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
            //file_put_contents($dest_image_path,  file_get_contents($images)); 
            //$dest_http_path = $this->put_image($images);
            //$images = $dest_http_path;
        }
        
        $return_array['image'] = $images;
        if(!$return_array['images']) {
            $return_array['images'][0] = $return_array['image'];
        }
        if(isset($return_array['title']) && $return_array['title'] != "") {
            $return_array['title'] = html_entity_decode($return_array['title']);
            $Return['ResponseCode'] = self::HTTP_OK;
            $Return['Data'] = $return_array;
        } else {
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
        }
        
        /*echo $Return['Data']['title'];
        echo '<br>';*/
        if(isset($Return['Data']['title'])) {
            $Return['Data']['title'] = iconv('Windows-1250', 'UTF-8', $Return['Data']['title']);
        }
        if(isset($Return['Data']['description'])) {
            $Return['Data']['description'] = iconv('Windows-1250', 'UTF-8', $Return['Data']['description']);
        }
        //echo $Return['Data']['title'];
        
        $this->response($Return);die;
    }
    
    protected function put_image($source_image) {
        $dest_http_path = '';
        if($source_image) {
            $source_image_data = @file_get_contents($source_image);
            if($source_image_data) {
                $time               = time().generateRandomCode();  
                $dest_http_path     = 'upload/wall/'.$time.'.jpg';
                $dest_image_path    = IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
                file_put_contents($dest_http_path,  $source_image_data);
                
                if (strtolower(IMAGE_SERVER) == 'remote') {
                    $this->load->library('S3');
                    $s3_credential = array("access_key"=>AWS_ACCESS_KEY,"secret_key"=>AWS_SECRET_KEY,"region"=>BUCKET_ZONE,"use_ssl"=>false,"verify_peer"=>true);
                    $headers = array("Cache-Control" => "max-age=315360000", "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+1 years")));
                    $s3 = new S3($s3_credential);
                    $s3->putObjectFile($dest_http_path, BUCKET, $dest_http_path, S3::ACL_PUBLIC_READ,$headers);
                    @unlink($dest_http_path);
                }
                
                /*
                $this->load->model('upload_file_model');
                $image_data = array('DeviceType' => 'Native', 'ImageData' => $source_image_data, 'ImageURL' => $source_image, 'ModuleID' => '19', 'ModuleEntityGUID' => '', 'Type' => 'wall');
                $linkURL = $this->upload_file_model->saveFileFromURL($image_data);
                $dest_http_path = 'upload/wall/220x220/' . $linkURL['Data']['ImageName'];
                 * 
                 */
            
            }
        }
        return $dest_http_path;
    }


    public function parseLinkData_post()
    {
        $Return = $this->return;

        $Data                   = $this->post_data;

        $this->form_validation->set_rules('url', 'url' , 'trim|required');

        if ($this->form_validation->run() == FALSE)
        {   
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        }
        else
        {
            try{

               $Data['url'] =  addhttp($Data['url']);

                $info = Embed::create($Data['url'],[
                'min_image_width' => 50,
                'min_image_height' => 50,
                'choose_bigger_image' => false,
                'html' => [
                    'max_images' => 2,
                    'external_images' => false
                ]]);


                $domain_detail['description']   = $info->description;
                $domain_detail['domainName']    = $info->providerUrl;
                $domain_detail['title']         = $info->title;
                $domain_detail['url']           = $info->url;
                $domain_detail['images']        = array();

                if(!empty($info->images))
                {   
                    foreach ($info->images as $key => $s_img) {
                      $domain_detail['images'][]  = $s_img['url'];
                    }
                }

                if(!empty($info->image))
                {   
                    $domain_detail['image'] = $info->image;
                }

                if(empty($domain_detail['image']) && !empty($domain_detail['images']))
                {
                    $domain_detail['image'] = $domain_detail['images'][0];
                }

                if(empty($info->description) && !empty($info->title))
                {
                    $info->description = $info->title;
                }


                $Return['Data'] = $domain_detail;

            }catch(Exception $e){

            }
        }
        $this->response($Return);

    }


}
