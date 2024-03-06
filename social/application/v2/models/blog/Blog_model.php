<?php
/**
 * This model is used for getting and storing sports related information
 * @package    blog_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Blog_model extends Common_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }
	
	/**
	* @Function - add blog
	* @Input 	- insert_data(array)
	* @Output 	- int/boolean
	*/
	public function add($insert_data=array())
	{
		if(!empty($insert_data))
		{
			$insert_data['BlogGUID']   		 = get_guid();
            $insert_data['CreatedDate']      = get_current_date('%Y-%m-%d %H:%i:%s');
            $insert_data['ModifiedDate']     = get_current_date('%Y-%m-%d %H:%i:%s');
			$this->db->insert(BLOG,$insert_data);
			return $this->db->insert_id();	
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* @Function - edit blog
	* @Input 	- update_data(array),blog_guid(string)
	* @Output 	- int/boolean
	*/
	public function edit($update_data=array(),$blog_id)
	{ 
		if(!empty($update_data))
		{
			$update_data['ModifiedDate']     = get_current_date('%Y-%m-%d %H:%i:%s');
                                if('DRAFT'==$update_data['Status']){
                                    $update_data['CreatedDate']     = get_current_date('%Y-%m-%d %H:%i:%s');
                                }
			
			$this->db->where('BlogID',$blog_id);
			$this->db->update(BLOG,$update_data);
			return TRUE;	
		}
		else
		{
			return FALSE;
		}
	}


	/**
	* @Function - update media 
	* @Input 	- media(array),blog_id(int)
	* @Output 	- boolean
	*/
	public function update_media($media=array(),$blog_id=0)
	{
		if(!empty($media))
		{
			$this->db->where('MediaSectionReferenceID',$blog_id);
			$this->db->update(MEDIA,array('StatusID'=>3));

			foreach ($media as $key => $value) 
			{
				if(!empty($value['MediaGUID']))
				{
					$this->db->where('MediaGUID',$value['MediaGUID']);
					$this->db->update(MEDIA,array('StatusID'=>2,'MediaSectionReferenceID'=>$blog_id));
					if($value['IsCoverMedia'])
					{
						$media_id = get_detail_by_guid($value['MediaGUID'],21);	
					}
				}
				else if(strtoupper($value['MediaType'])=="YOUTUBE")
				{
					$media_arr = array(
	                    'UserID' => $this->UserID,
	                    'ImageUrl' => isset($value['Url'])?$value['Url']:"",
	                    'ImageName' => isset($value['Url'])?$value['Url']:"",
	                    'AlbumID' => 0,
	                    'MediaGUID' => get_guid(),
	                    'Caption' => isset($value['Caption'])?$value['Caption']:"",
	                    'SportsID' => isset($value['SportsID'])?$value['SportsID']:0,
	                    'ModuleEntityID' => $blog_id,
	                    'StatusID' => 2,
	                    'Description' => isset($value['Description'])?$value['Description']:"",
	                    'MediaSectionID' => 9,
	                    'DeviceID' => isset($value['DeviceID']) ? $value['DeviceID'] : 1,
	                    'MediaExtensionID' => 9,
	                    'MediaSectionReferenceID' => $blog_id,
	                    'ModuleID' => 23,
	                    'CreatedDate' => gmdate('Y-m-d H:i:s'),
	                    'SourceID'          => 1,
	                    'Size'              => '', //The file size in kilobytes
	                    'MediaSizeID'       => 1,
	                    'IsAdminApproved'   => 1,
	                    'VideoLength'       => isset($value['VideoLength'])?$value['VideoLength']:0,
	                    'ConversionStatus'  => 'Finished'
	                        //'ModifiedDate' => gmdate('Y-m-d H:i:s'),
	                );
					$this->db->insert(MEDIA,$media_arr);
					if($value['IsCoverMedia'])
					{
						$media_id = $this->db->last_query();	
					}
				}	
			}
			if(!empty($media_id))
			{
				$this->db->set('MediaID',$media_id);
			}

			$this->db->set('IsMediaExists',1);
			$this->db->where('BlogID',$blog_id);
			$this->db->update(BLOG);
			
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* @Function - delete blog 
	* @Input 	- blog_id(int)
	* @Output 	- boolean
	*/
	public function delete($blog_id)
	{
		$update_data = array(
			"Status"=>"DELETED",
			"ModifiedDate"=>get_current_date('%Y-%m-%d %H:%i:%s')
			);
		$this->db->where('BlogID',$blog_id);
		$this->db->update(BLOG,$update_data);
		return TRUE;
	}


	/**
    * @Function - list BLOG
    * @Input    - search_keyword(string), count_flag(bool), page_no(int), page_size(int), sort_by(string), order_by(string)
    * @Output   - ARRAY/INT depend of count_flag
    **/
    public function blog_list($search_keyword = "", $count_flag = FALSE, $page_no = 1, $page_size = PAGE_SIZE, $sort_by = "CreatedDate", $order_by = "DESC",$list_type='',$entity_type=1)
    {
        $this->db->select('B.BlogID,B.Status, B.BlogGUID, IFNULL(B.Title,"") AS Title, IFNULL(B.Description,"") AS Description, B.IsMediaExists, B.EntityType, B.MediaID, B.UserID, B.NoOfLikes, B.NoOfComments, B.CreatedDate, B.ModifiedDate',FALSE);
        $this->db->select('IFNULL(CONCAT(U.FirstName," ",U.LastName),"") AS Author',FALSE);
        
        $this->db->from(BLOG.' AS B');
        $this->db->join(USERS.' AS U','U.UserID = B.UserID','inner');
        
        if($entity_type==1){
            $this->db->where('B.EntityType',$entity_type);
        }else{
            $this->db->where_in('B.EntityType',$entity_type);
        }
        
        $this->db->where('B.Status !=','DELETED');
        if(empty($list_type))
        {
        	$this->db->where('B.Status','PUBLISHED');	
        }
        
        if(!empty($search_keyword)) 
        {
			$search_keyword = strtolower($search_keyword);
            
            $SearchStr = "( LOWER(B.Title) like '".$this->db->escape_like_str($search_keyword)."%'";            
            $SearchStr.= " or LOWER(B.Description) like '%".$this->db->escape_like_str($search_keyword)."%'";
            $SearchStr .= ')';            
            $this->db->where($SearchStr);           
		}
        
        if($count_flag) 
        {
        	$res = $this->db->get();
        	return $res->num_rows();
        } 
        
        $this->db->order_by('B.'.$sort_by, $order_by);
    	if(!empty($page_size))
        {
        	if(empty($page_no)) {
        		$page_no = 1;
        	}
        	$offset = ($page_no-1)*$page_size;
            $this->db->limit($page_size, $offset);   
        }	        
        $res = $this->db->get();   
        //echo $this->db->last_query();die;
        $res = $res->result_array();
        $return_array = array();
        if(!empty($res))
        {
            foreach ($res as $key => $value)
            {
                $value['CoverMedia'] = array();
                $value['Media']      = array();
                if(!empty($value['IsMediaExists']))
                {
	                $cover_media = $value['MediaID'];
	                if(!empty($cover_media))
	                {
	                    //GET Cover Media Details
	                    $value['CoverMedia'] = $this->get_media($value['BlogID'], $cover_media, TRUE);
	                }                
                    //GET Media Details
                    $value['Media'] = $this->get_media($value['BlogID'], $cover_media, FALSE);
                }
                
                unset($value['IsMediaExists']);
                unset($value['MediaID']);    
                unset($value['BlogID']);               
                unset($value['UserID']);  
                $return_array[]     = $value;
            }
        }
        return $return_array;        
    }

    /**
     * [get_media get media for blog]
     * @param  [int]  $blog_id          	[Blog ID]
     * @param  [string]  $media_id         	[Media ID, IT IS COVER MEDIA ID]
     * @param  [boolean] $only_cover_media 	[Flag to return only cover media or all ther media except cover media]
     * @return [array]                    	[array]
     */
    public function get_media($blog_id, $media_id="", $only_cover_media=FALSE)
    {
    	$this->db->select('M.MediaGUID, IFNULL(M.ImageName,"") ImageName, M.Caption, IFNULL(M.CreatedDate,"") CreatedDate, MT.Name as MediaType', FALSE);
        $this->db->select('IFNULL(M.ConversionStatus,"") AS ConversionStatus, IFNULL(M.VideoLength,"") AS VideoLength', FALSE);        
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');       
        
        $this->db->where('M.MediaSectionReferenceID', $blog_id);
        $this->db->where('M.MediaSectionID', 9);
        $this->db->where('M.StatusID', 2);
        if($media_id) 
        {
	        if($only_cover_media)
	        {
	        	$this->db->where('M.MediaID', $media_id);
	        } 
	        else 
	        {
	        	$this->db->where('M.MediaID != ', $media_id);	
	        }
        }

        $query 	= $this->db->get(MEDIA . ' M');
        
        if($only_cover_media)
	    {
        	$res 	= $query->row_array();
	    } 
	    else
	    {
	    	$res = array();
	    	$result = $query->result_array();
	    	if(!empty($result))
	        {
	            foreach ($result as $key => $value)
	            {
	            	if($value['MediaType']=='Video')
	            	{
	            		$ImageName = str_replace(".mp4", ".jpg", $value['ImageName']);
                    	$value['ImageName'] = $ImageName;
	            	}
	            	$res[] = $value;
	            }
	        }
	    }
        
        /*if($only_cover_media)
	    {
	    	return $res;
	    }
        $return_array = array();
        if(!empty($res))
        {
            foreach ($res as $key => $value)
            {
            	$return_array[] = $value;
            }
        }*/
        return $res;
    }

    /**
    * @Function - function to get detail of evaluation
    * @Input    - BlogGUID(STRING)
    * @Output   - JSON
    */
    function details($blog_guid)
    {
        $this->db->select('B.BlogID,B.Status, B.BlogGUID, IFNULL(B.Title,"") AS Title, IFNULL(B.Description,"") AS Description, B.IsMediaExists, B.MediaID, B.UserID, B.NoOfLikes, B.NoOfComments, B.CreatedDate, B.ModifiedDate',FALSE);
        $this->db->select('IFNULL(CONCAT(U.FirstName," ",U.LastName),"") AS Author',FALSE);
        
        $this->db->from(BLOG.' AS B');
        $this->db->join(USERS.' AS U','U.UserID = B.UserID','inner');
        //$this->db->where('B.Status','PUBLISHED');
        $this->db->where('B.BlogGUID',$blog_guid);
        $this->db->where('B.Status !=','DELETED');
        $res = $this->db->get();   
        
        $res = $res->result_array();
        $return_array = array();
        if(!empty($res))
        {
        	$this->load->model('activity/activity_model');
            foreach ($res as $key => $value)
            {
                $value['CoverMedia'] = array();
                $value['Media']      = array();
                if(!empty($value['IsMediaExists']))
                {
	                $cover_media = $value['MediaID'];
	                if(!empty($cover_media))
	                {
	                    //GET Cover Media Details
	                    $value['CoverMedia'] = $this->get_media($value['BlogID'], $cover_media, TRUE);
	                }                
                    //GET Media Details
                    $value['Media'] 	= $this->get_media($value['BlogID'], $cover_media, FALSE);
                }
                $value['IsLike'] 	= $this->activity_model->checkLike($value['BlogGUID'], 'BLOG', $this->UserID);
                unset($value['IsMediaExists']);
                unset($value['MediaID']);    
                unset($value['BlogID']);  
                unset($value['UserID']);               
                $return_array[]     = $value;
            }
        }
        return $return_array;
    }
}