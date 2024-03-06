<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Wallpost extends Common_API_Controller
{
	public $UserID='';
	function __construct()
	{
		parent::__construct();
                $this->check_module_status(2);
                $this->load->model(array('group/group_model', 'pages/page_model', 'flag_model','users/user_model', 'subscribe_model', 'notification_model','activity/activity_model'));        
	}

	public function parseLinkData_post()
	{
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

        $string = str_replace(array("\n","\r","\t",'</span>','</div>'), '', $string);

        $string = preg_replace('/(<(div|span)\s[^>]+\s?>)/',  '', $string);
        if (mb_detect_encoding($string, "UTF-8") != "UTF-8")
            $string = utf8_encode($string);

        // Parse Title
        $nodes = extract_url_tags($string, 'title');
     

        if($nodes && trim($nodes[0]['contents']!='Access Denied'))
        {        
	        $return_array['domainName'] = $domainName;
	        $return_array['title']      = trim($nodes[0]['contents']);

	        $return_array['title'] = strlen($return_array['title']) > 70 ? substr($return_array['title'],0,70).".." : $return_array['title'];

	        // Parse Description
	        $return_array['description'] = '';
	        $nodes = extract_url_tags( $string, 'meta' );
        
	        foreach($nodes as $node)
	        {
	            if (isset($node['attributes']['name']) && strtolower($node['attributes']['name']) == 'description')
				{
					//$return_array['description'] =  substr(trim($node['attributes']['content']),0,75);
					$return_array['description'] = strlen($node['attributes']['content']) > 110 ? substr($node['attributes']['content'],0,110).".." : $node['attributes']['content'];
				} 

				if(isset($node['attributes']['property']) && $node['attributes']['property']=='og:image')
				{
					$images =   $node['attributes']['content'];
				}
	        }

	        // Parse Images   
	        if(!empty($youtubeID))
	        {      

	            $time = time().generateRandomCode();  
	            $imagesArray[0] = $images =  "https://img.youtube.com/vi/".$youtubeID."/0.jpg";

	            $dest_http_path = 'upload/wall/'.$time.'.jpg';
                $dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
                file_put_contents($dest_image_path,  file_get_contents($imagesArray[0]));
                $imagesArray[0] = $dest_http_path;
                $images = $dest_http_path;
                //print_r($imagesArray);
	        }
	        elseif(!empty($vimeoThumb))
	        {   
	            $time = time().generateRandomCode();
	            $images = $vimeoThumb;
	        }
	        else
	        {  
	            $images_array = extract_url_tags( $string, 'img' );
	            //print_r($images_array);
	            $imgPng = "";                
            
	            for ($i=0;$i<=sizeof($images_array);$i++)
	            {
	                $time = time().generateRandomCode();

	                if($images=="")
	                {
	                    $img = trim(@$images_array[$i]['attributes']['src']);
	                    $ext = trim(pathinfo($img, PATHINFO_EXTENSION));

	                    if($ext=='png')
	                    {
	                        $imgPng =  $img;
	                    }

	                    if($img && ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'bmp') )
	                    {   
	                        if (substr($img,0,7) == 'http:/' || substr($img,0,7) == 'https:/' || substr($img,0,7) == 'http://' || substr($img,0,7) == 'https://');
	                        else
	                           $img = $relative_url . $img;
	                        
	
	                        $dest_http_path = 'upload/wall/'.$time.'.jpg';
	                        $dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
	                        file_put_contents($dest_image_path,  file_get_contents($img)); 
	                        
	                        if (!is_404($img)) 
	                        {
		                        if(check_url_values($img))
		                        {
		                            $images = $img;
		                        }
		                    }
		                    else
		                    {
		                        $url1 = $img;
		                        $params = explode('.', $url1);

		                        if($params[0] == 'http://www' || $params[0] == 'https://www') 
		                        {		                         
		                            $img= str_replace('www.', '', $img);
		                        }
		                        else
		                        {
		                            $img= str_replace('://', '://www.', $img);
		                        }		                        

		                        if(!is_404($img))
		                        {
		                            if(check_url_values($img))
		                            {
		                                $images = $img;
		                            }		                                          
		                        }
		                    }                    
                    	}
                	}
                
	                /*make array of images*/
	                $img = trim(@$images_array[$i]['attributes']['src']);
	                $ext = trim(pathinfo($img, PATHINFO_EXTENSION));

                	if ($img && ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'bmp')) 
                	{
                        if (substr($img,0,7) == 'http:/' || substr($img,0,7) == 'https:/' || substr($img,0,7) == 'http://' || substr($img,0,7) == 'https://');
                        else
                           $img = $relative_url . $img;
                        
                        $dest_http_path = 'upload/wall/'.$time.'.jpg';
                        $dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
                        file_put_contents($dest_image_path,  file_get_contents($img)); 

	                    if (!is_404($img)) 
	                    {
	                        if (check_url_values($img)) 
	                        {	                        
	                            $imagesArray[] = $dest_http_path;
	                        }
	                    } 
	                    else 
	                    {
	                        $url1 = $img;
	                        $params = explode('.', $url1);

	                        if ($params[0] == 'http://www' || $params[0] == 'https://www') 
	                        {
	                            $img = str_replace('www.', '', $img);
	                        } 
	                        else 
	                        {
	                            $img = str_replace('://', '://www.', $img);
	                        }

	                        if (!is_404($img)) 
	                        {
	                            if (check_url_values($img)) 
	                            {	                                   
	                                $imagesArray[] = $dest_http_path;
	                            }
	                        }
	                    }                    
                	}
                	/*end make array of images*/
                
                	if(count($imagesArray)>5) 
                	{
                    	break;
                	}    
            	}

            	if($images=="" && $imgPng!='')
            	{
                    $img= $imgPng;
                
                    if (substr($img,0,7) == 'http:/' || substr($img,0,7) == 'https:/' || substr($img,0,7) == 'http://' || substr($img,0,7) == 'https://')
                            ;
                    else if (substr($img,0,1) == '/')
                        $img = $base_url . $img;
                    else
                        $img = $relative_url . $img;

	                if (!is_404($img)) 
	                {
	                    if(check_url_values($img))
	                    {
	                        $dest_http_path = 'upload/wall/'.$time.'.jpg';
	                        $dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
	                        file_put_contents($dest_image_path,  file_get_contents($img)); 

	                        //$images = $img;
	                        $images = $dest_image_path;
	                    }
	                }
	                else
	                {
	                    $url1 = $img;
	                    $params = explode('.', $url1);

	                    if($params[0] == 'http://www' || $params[0] == 'https://www') 
	                    {	                     
	                        $img= str_replace('www.', '', $img);
	                    }
	                    else
	                    {
	                        $img= str_replace('://', '://www.', $img);
	                    }

	                    if(!is_404($img))
	                    {
                            if(check_url_values($img)){
                        
                                $dest_http_path = 'upload/wall/'.$time.'.jpg';
                                $dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
                                file_put_contents($dest_image_path,  file_get_contents($img)); 

                                //$images = $img;
                                $images = $dest_image_path;
                            }	                                      
	                    }
	                }
            	}
        	}
    	}

        $return_array['images'] = $imagesArray;
        
        if($images!='' && empty($youtubeID))
        {
			$dest_http_path = 'upload/wall/'.$time.'.jpg';
			$dest_image_path=IMAGE_ROOT_PATH.'/wall/'.$time.'.jpg';
			file_put_contents($dest_image_path,  file_get_contents($images)); 
			$images = $dest_http_path;
        }
        
        $return_array['image'] = $images;
        if(!$return_array['images'])
        {
        	$return_array['images'][0] = $return_array['image'];
        }
        if(isset($return_array['title']) && $return_array['title'] != "")
        {
        	$return_array['title'] = html_entity_decode($return_array['title']);
            $Return['ResponseCode'] = self::HTTP_OK;
            $Return['Data'] = $return_array;
        }
        else
        {
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
        }
        
        /*echo $Return['Data']['title'];
        echo '<br>';*/
        if(isset($Return['Data']['title']))
        {
        	$Return['Data']['title'] = iconv('Windows-1250', 'UTF-8', $Return['Data']['title']);
        }
        if(isset($Return['Data']['description']))
        {
        	$Return['Data']['description'] = iconv('Windows-1250', 'UTF-8', $Return['Data']['description']);
        }
        //echo $Return['Data']['title'];
        
        $this->response($Return);
    }
}