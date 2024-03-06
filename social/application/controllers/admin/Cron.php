<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All Users login and signup data insert in other tables for managing analytics
 * @package    Users
 * @author     Ashwin kumar soni : 01-10-2014
 * @version    1.0
 */

class Cron extends MY_Controller {

    public $page_name = "";

    public function __construct() {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->load->model(array('admin/cron_model','admin/communication_model'));
    }

    /**
     * Function for show Login analytics page in admin section
     * Parameters : 
     * Return : Load View files
     */
    public function index() {
        
    }

    /**
     * Function for send email to users from communication table using cron jobs
     * Parameters : 
     * Return : 
     */
    public function sendemailtousers() 
    {
        $global_settings = $this->config->item("global_settings");
        
        //if(SEND_EMAIL_BY_CRON == 1)
        //{
            $email_status = array();
            
            //For select communication emails list 
            $communicationEmails = $this->communication_model->getNotSendingEmailCommunicationList();
            //echo "<pre>";print_r($communicationEmails);die;        
            if($global_settings['global_email_sending'] == 1){
                $smtp_settings = $this->config->item("smtp_settings");
                $smtpData = array();

                foreach($communicationEmails as $communication){
                    $StatusID = 1;
                    //set smtp setting data for sending email
                    if(isset($smtp_settings[$communication['EmailTypeID']])){
                        $smtpData = $smtp_settings[$communication['EmailTypeID']];
                    }else{
                        $smtpData = $smtp_settings['default'];
                    }

                    $Email = $communication['EmailTo'];
                    $Subject = $communication['Subject'];
                    $email_html = $communication['Body'];
                    $smtpData['ReplyTo'] = $communication['ReplyTo'];

                    $result = @sendMail($smtpData, $Email, $Subject, $email_html);

                    if($result){
                        //For update communication send email status
                        //$this->communication_model->updateCommunicationStatus($communication['CommunicationID']);
                        $StatusID = 2;
                        
                    }
                    $email_status[] = array('CommunicationID' => $communication['CommunicationID'], 'StatusID' => $StatusID, 'ProcessDate' => get_current_date('%Y-%m-%d %H:%i:%s'));

                }  
            }else if($global_settings['send_mail_via_mandrill'] == 1){
                foreach($communicationEmails as $communication){
                    $StatusID = 1;
                    $Email = $communication['EmailTo'];
                    $Subject = $communication['Subject'];
                    $email_html = $communication['Body'];
                    
                    $mandrillArr = array();
                    $mandrillArr['mandrill_api_key'] = ($global_settings['mandrill_api_key'])?$global_settings['mandrill_api_key']:MANDRILL_API_KEY;
                    $mandrillArr['mandrill_from_email'] = ($global_settings['mandrill_from_email'])?$global_settings['mandrill_from_email']:MANDRILL_FROM_EMAIL;
                    $mandrillArr['mandrill_from_name'] = ($global_settings['mandrill_from_name'])?$global_settings['mandrill_from_name']:MANDRILL_FROM_NAME;
                    $mandrillArr['subject'] = $Subject;
                    $mandrillArr['email_html'] = $email_html;
                    $mandrillArr['user_email'] = $Email;
                    $mandrillArr['user_name'] = '';            
                    $mandrillArr['EmailTypeID'] = $communication['EmailTypeID'];            

                    $result = @sendMandrillEmails($mandrillArr);                    
                    if($result){
                        //For update communication send email status
                        //$this->communication_model->updateCommunicationStatus($communication['CommunicationID']);
                        $StatusID = 2;
                    }
                    $email_status[] = array('CommunicationID' => $communication['CommunicationID'], 'StatusID' => $StatusID, 'ProcessDate' => get_current_date('%Y-%m-%d %H:%i:%s'));

                }
            }
            if(!empty($email_status))
            {
                $this->communication_model->updateCommunicationStatus($email_status);
            }
            
        //}
        
    }
    
    
    /**
     * Function for insert previous day all users Login data in different login analytics table
     * Parameters : 
     * Return : Load View files
     */
    public function login() {
        $Message = '';
        $fromdate = $this->uri->segment(4);
        $todate = $this->uri->segment(5);
        
        if($fromdate != ""){
            $fromDate = date("Y-m-d",strtotime($fromdate));
        }else{
            $fromDate = date("Y-m-d",strtotime("-1 day"));
        }
        if($todate != ""){
            $toDate = date("Y-m-d",strtotime($todate));
        }else{
            $toDate = date("Y-m-d");
        }
        
        $diff = strtotime($toDate) - strtotime($fromDate);
        $days = floor($diff/3600/24);
        for($i=0;$i<=$days;$i++){
            $analytic_date = date("Y-m-d",  strtotime($fromDate.' + '.$i.' days'));
            if($analytic_date != ""){
                //For select user login count analytic data
                $this->cron_model->getLoginCountAnalytics($analytic_date);

                //For user first time login data
                $this->cron_model->getFirstLoginAnalytics($analytic_date);

                //For user login geo data
                $this->cron_model->getLoginGeoAnalytics($analytic_date);
                
                $Message = "Analytic generate successfully..";
            }
        }
        echo $Message;
            
    }
    
    /**
     * Function for insert previous day all registered users data in different signup analytics table
     * Parameters : 
     * Return : Load View files
     */
    public function signup() {
        $Message = '';
        $fromdate = $this->uri->segment(4);
        $todate = $this->uri->segment(5);
        
        if($fromdate != ""){
            $fromDate = date("Y-m-d",strtotime($fromdate));
        }else{
            $fromDate = date("Y-m-d",strtotime("-1 day"));
        }
        if($todate != ""){
            $toDate = date("Y-m-d",strtotime($todate));
        }else{
            $toDate = date("Y-m-d");
        }
        
        $diff = strtotime($toDate) - strtotime($fromDate);
        $days = floor($diff/3600/24);
        for($i=0;$i<=$days;$i++){
            $analytic_date = date("Y-m-d",  strtotime($fromDate.' + '.$i.' days'));
            if($analytic_date != ""){
                //For register user log count analytic data select and insert
                $this->cron_model->getSignupAnalyticsLogCount($analytic_date);

                //For register user geo data
                $this->cron_model->getSignupGeoAnalytics($analytic_date);

                //For register user visit count data
                $this->cron_model->getSignupVisitCountAnalytics($analytic_date);

                //For user time taken on signup
                $this->cron_model->getSignupTimeTakenRangeAnalytic($analytic_date);
                
                $Message = "Analytic generate successfully..";
            }
        }
        echo $Message;
               
    }

    function check_profile_images() 
    {
        echo "media_section_id = ".$media_section_id = $this->uri->segment(4);
        echo "<br>module_id = ".$module_id = $this->uri->segment(5);
        
        if($media_section_id == 1)
        {
            $folder_name = "profile";            
            if($module_id == 1)
            {
                $sql = " AND M.ImageName=G.GroupImage";
            }
            if($module_id == 3)
            {
                $sql = " AND M.ImageName=U.ProfilePicture";
            }
            if($module_id == 14)
            {
                $sql = " AND M.MediaID=E.ProfileImageID";
            }
            if($module_id == 18)
            {
                $sql = " AND M.ImageName=P.ProfilePicture";
            }
        }
        if($media_section_id == 5)
        {
            $folder_name = "profilebanner";            
            if($module_id == 1)
            {
                $sql = " AND M.ImageName=G.GroupCoverImage";
            }
            if($module_id == 3)
            {
                $sql = " AND M.ImageName=U.ProfileCover";
            }
            if($module_id == 14)
            {
                $sql = " AND M.MediaID=E.ProfileBannerID";
            }
            if($module_id == 18)
            {
                $sql = " AND M.ImageName=P.CoverPicture";
            }
        }

        if($module_id == 1)
        {
            $this->db->select('M.MediaID, M.StatusID, M.ImageName, G.GroupID as module_entity_id, G.CreatedBy as UserID, G.GroupImage');
            $this->db->from(GROUPS.' G');
            $this->db->join(MEDIA.' M','M.MediaSectionReferenceID=G.GroupID AND M.ModuleID='.$module_id.' AND M.MediaSectionID='.$media_section_id.$sql);
            $this->db->where('G.StatusID','2');
        }

        if($module_id == 3)
        {
            $this->db->select('M.MediaID, M.StatusID, M.ImageName, U.UserID, U.UserID as module_entity_id, U.ProfilePicture');
            $this->db->from(USERS.' U');
            $this->db->join(MEDIA.' M','M.MediaSectionReferenceID=U.UserID AND M.ModuleID='.$module_id.' AND M.MediaSectionID='.$media_section_id.$sql);
            $this->db->where('U.StatusID','2');
        }

        if($module_id == 14)
        {
            $this->db->select('M.MediaID, M.StatusID, M.ImageName, E.EventID as module_entity_id, E.CreatedBy as UserID');
            $this->db->from(EVENTS.' E');
            $this->db->join(MEDIA.' M','M.MediaSectionReferenceID=E.EventID AND M.ModuleID='.$module_id.' AND M.MediaSectionID='.$media_section_id.$sql);
            $this->db->where('E.IsDeleted',0);
        }       

        if($module_id == 18)
        {
            $this->db->select('M.MediaID, M.StatusID, M.ImageName, P.PageID as module_entity_id, P.UserID, P.ProfilePicture');
            $this->db->from(PAGES.' P');
            $this->db->join(MEDIA.' M','M.MediaSectionReferenceID=P.PageID AND M.ModuleID='.$module_id.' AND M.MediaSectionID='.$media_section_id.$sql);
            $this->db->where('P.StatusID','2');
        }

        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if($query->num_rows())
        {
            $s3         = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY);
            foreach($query->result_array() as $img) 
            {
                $exist       = FALSE; 
                
                $module_entity_id   = $img['module_entity_id'];
                $UserID             = $img['UserID'];                
                $MediaID            = $img['MediaID'];
                $MediaStatusID      = $img['StatusID'];

                if(!empty($img)) 
                {
                    if (strtolower(IMAGE_SERVER) == 'remote' && $s3->getObjectInfo(BUCKET, PATH_IMG_UPLOAD_FOLDER.$folder_name.'/'.$img['ImageName'])) {              
                        $exist = TRUE;
                    } else if(file_exists(IMAGE_ROOT_PATH.$folder_name.'/'.$img['ImageName'])){
                        $exist = TRUE;    
                    }
                }

                $album_id = get_album_id($UserID, DEFAULT_PROFILE_ALBUM, $module_id, $module_entity_id);
                $coverMediaID = $MediaID;
                $set_field = "MediaCount";
                if(!$exist)
                {
                    if($module_id == 1)
                    {
                        $this->db->where(array("GroupID" => $module_entity_id));
                        if($media_section_id == 5)
                        {
                            $this->db->set("GroupCoverImage", "");       
                        } 
                        else
                        {
                            $this->db->set("GroupImage", ""); 
                        }      
                        $this->db->update(GROUPS);     
                    }

                    if($module_id == 3)
                    {
                        $this->db->where(array("UserID" => $UserID));
                        if($media_section_id == 5)
                        {
                            $this->db->set("ProfileCover", "");       
                        } 
                        else
                        {
                            $this->db->set("ProfilePicture", ""); 
                        }
                        
                        $this->db->update(USERS);    
                    }

                    if($module_id == 14)
                    {
                        $this->db->where(array("EventID" => $module_entity_id));
                        if($media_section_id == 5)
                        {
                            $this->db->set("ProfileBannerID", "0");       
                        } 
                        else
                        {
                            $this->db->set("ProfileImageID", "0"); 
                        }      
                        $this->db->update(EVENTS);     
                    }

                    if($module_id == 18)
                    {
                        $this->db->where(array("PageID" => $module_entity_id));
                        if($media_section_id == 5)
                        {
                            $this->db->set("CoverPicture", "");       
                        } 
                        else
                        {
                            $this->db->set("ProfilePicture", ""); 
                        }      
                        $this->db->update(PAGES);     
                    }
                    
                    
                    $this->db->where(array("MediaID" => $MediaID));
                    $this->db->set("StatusID", 3);       
                    $this->db->update(MEDIA); 
                    
                    $coverMediaID = NULL;                   
                }   

                $this->db->select('M.MediaID');
                $this->db->from(MEDIA.' M');
                $this->db->where('M.StatusID','2');
                $this->db->where('M.MediaSectionID',$media_section_id);
                $this->db->where('M.ModuleID',$module_id);
                $this->db->where('M.MediaSectionReferenceID',$module_entity_id);
                $query = $this->db->get();
                $Count = $query->num_rows();

                //echo "<br>".$album_id." -- ".$Count."<br>";
                if(empty($coverMediaID) || $coverMediaID=="") 
                {
                    $coverMediaID = NULL;
                }

                $this->db->set($set_field, $Count, FALSE);
                $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->set("MediaID", $coverMediaID);
                $this->db->where(array("AlbumID" => $album_id));
                $this->db->update(ALBUMS);
            }
        }
    }    

    function check_update_images_status() 
    {
        
        $this->db->select('M.MediaID, M.ImageName, MT.Name as MediaType, M.MediaSectionID, MS.MediaSectionAlias', FALSE);        
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID=M.MediaSectionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        //$this->db->where('M.StatusID','2');
        $query  = $this->db->get(MEDIA . ' M');

       // echo $this->db->last_query();die;
        if($query->num_rows())
        {
            $s3         = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY);
            foreach($query->result_array() as $img) 
            {
                $exist              = FALSE;                
                $MediaID            = $img['MediaID'];
                $MediaType          = $img['MediaType'];
                $MediaSectionID     = $img['MediaSectionID'];
                $MediaSectionAlias  = $img['MediaSectionAlias'];
                $SubDir             = "/";
                //echo "<br>".$MediaSectionAlias.$SubDir.$img['ImageName'];
                
                if(!empty($img) && $MediaType != 'Youtube')
                {                    
                    if($MediaType == 'Video') 
                    {
                        $SubDir = "/video/";
                    }
                    if (strtolower(IMAGE_SERVER) == 'remote' && $s3->getObjectInfo(BUCKET, PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.$SubDir.$img['ImageName'])) 
                    {              
                        $exist = TRUE;
                    } else if(file_exists(IMAGE_ROOT_PATH.$MediaSectionAlias.$SubDir.$img['ImageName']))
                    {
                        $exist = TRUE;    
                    }
                }
                if(!$exist)
                {
                    $this->db->where(array("MediaID" => $MediaID));
                    $this->db->set("StatusID", 3);       
                    $this->db->update(MEDIA); 
                }
                else
                {
                    $this->db->where(array("MediaID" => $MediaID));
                    $this->db->set("StatusID", 2);       
                    $this->db->update(MEDIA);
                }               
            }
        }
    }

    function default_album_creation() 
    {
        // for Group
        $this->db->select('FC.CreatedBy, FC.ForumCategoryID');
        $this->db->from(FORUMCATEGORY.' FC');
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if($query->num_rows())
        {
            foreach($query->result_array() as $group) 
            {
                $user_id            = $group['CreatedBy'];
                $module_entity_id   = $group['ForumCategoryID'];
                $module_id          = 34;

                $album_id = get_album_id($user_id, DEFAULT_PROFILE_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 1, $module_id, DEFAULT_PROFILE_ALBUM, $module_entity_id);               

                $album_id = get_album_id($user_id, DEFAULT_PROFILECOVER_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 5, $module_id, DEFAULT_PROFILECOVER_ALBUM, $module_entity_id);

                $album_id = get_album_id($user_id, DEFAULT_WALL_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 3, $module_id, DEFAULT_WALL_ALBUM, $module_entity_id);
            }
        }

die();
        $this->db->select('U.UserID');
        $this->db->from(USERS.' U');
        $this->db->where('U.StatusID','2');
        $query = $this->db->get();
        if($query->num_rows())
        {
            foreach($query->result_array() as $user) 
            {
                $user_id     = $user['UserID'];

                $album_id = get_album_id($user_id, DEFAULT_PROFILE_ALBUM, 3, $user_id);
                $this->update_album_media_count($album_id, $user_id, 1, 3);               

                $album_id = get_album_id($user_id, DEFAULT_PROFILECOVER_ALBUM, 3, $user_id);
                $this->update_album_media_count($album_id, $user_id, 5, 3);

                $album_id = get_album_id($user_id, DEFAULT_WALL_ALBUM, 3, $user_id);
                $this->update_album_media_count($album_id, $user_id, 3, 3, DEFAULT_WALL_ALBUM);
            }
        }

        // for Group
        $this->db->select('G.CreatedBy, G.GroupID');
        $this->db->from(GROUPS.' G');
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if($query->num_rows())
        {
            foreach($query->result_array() as $group) 
            {
                $user_id            = $group['CreatedBy'];
                $module_entity_id   = $group['GroupID'];
                $module_id          = 1;

                $album_id = get_album_id($user_id, DEFAULT_PROFILE_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 1, $module_id, DEFAULT_PROFILE_ALBUM, $module_entity_id);               

                $album_id = get_album_id($user_id, DEFAULT_PROFILECOVER_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 5, $module_id, DEFAULT_PROFILECOVER_ALBUM, $module_entity_id);

                $album_id = get_album_id($user_id, DEFAULT_WALL_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 3, $module_id, DEFAULT_WALL_ALBUM, $module_entity_id);
            }
        }

        // for Page
        $this->db->select('P.UserID, P.PageID');
        $this->db->from(PAGES.' P');
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if($query->num_rows())
        {
            foreach($query->result_array() as $page) 
            {
                $user_id            = $page['UserID'];
                $module_entity_id   = $page['PageID'];
                $module_id          = 18;

                $album_id = get_album_id($user_id, DEFAULT_PROFILE_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 1, $module_id, DEFAULT_PROFILE_ALBUM, $module_entity_id);               

                $album_id = get_album_id($user_id, DEFAULT_PROFILECOVER_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 5, $module_id, DEFAULT_PROFILECOVER_ALBUM, $module_entity_id);

                $album_id = get_album_id($user_id, DEFAULT_WALL_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 3, $module_id, DEFAULT_WALL_ALBUM, $module_entity_id);
            }
        }

        // for event
        $this->db->select('E.CreatedBy, E.EventID');
        $this->db->from(EVENTS.' E');
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if($query->num_rows())
        {
            foreach($query->result_array() as $event) 
            {
                $user_id            = $event['CreatedBy'];
                $module_entity_id   = $event['EventID'];
                $module_id          = 14;

                $album_id = get_album_id($user_id, DEFAULT_PROFILE_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 1, $module_id, DEFAULT_PROFILE_ALBUM, $module_entity_id);               

                $album_id = get_album_id($user_id, DEFAULT_PROFILECOVER_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 5, $module_id, DEFAULT_PROFILECOVER_ALBUM, $module_entity_id);

                $album_id = get_album_id($user_id, DEFAULT_WALL_ALBUM, $module_id, $module_entity_id);
                $this->update_album_media_count($album_id, $user_id, 3, $module_id, DEFAULT_WALL_ALBUM, $module_entity_id);
            }
        }
    }

    function update_album_media_count($album_id, $user_id, $media_section_id=1, $module_id=3, $album_name="", $module_entity_id="")
    {
        if($album_name == DEFAULT_WALL_ALBUM) 
        {
            $this->db->select('M.MediaID, A.ActivityID, A.ModuleEntityID');
            $this->db->from(MEDIA.' M');
            $this->db->join(ACTIVITY.' A','A.ActivityID=M.MediaSectionReferenceID');
            $this->db->where('A.ModuleID', $module_id);
            if($module_id == 3)
            {
                $this->db->where('A.ModuleEntityID', $user_id);    
            }
            else 
            {
               $this->db->where('A.ModuleEntityID', $module_entity_id); 
            }
        }
        else
        {
            $this->db->select('M.MediaID');
            $this->db->from(MEDIA.' M');
            if($module_id == 3)
            {
                $this->db->where('M.MediaSectionReferenceID', $user_id);    
            }
            else 
            {
               $this->db->where('M.MediaSectionReferenceID', $module_entity_id); 
            }

            
            $this->db->where('M.ModuleID', $module_id);
        }


        
        $this->db->where('M.StatusID','2');
        $this->db->where('M.MediaSectionID', $media_section_id);
        $this->db->order_by("MediaID",'ASC');
        $query = $this->db->get();
    //echo $this->db->last_query();die;
        $Count = $query->num_rows();
        $media_id = '';
        if($Count > 0)
        {
            foreach($query->result_array() as $media) 
            {
                $this->db->set("AlbumID", $album_id, FALSE);
                //$this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->where(array("MediaID" => $media['MediaID']));
                $this->db->update(MEDIA);
                $media_id = $media['MediaID'];
            }
        }  


        //echo "<br>".$media_id." -- ".$Count."<br>";
        if($media_id)
        {
            $this->db->set("MediaID", $media_id, FALSE);    
        }
        else
        {
            $this->db->set("MediaID", NULL);
        }
        
        $this->db->set("MediaCount", $Count, FALSE);
        $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->where(array("AlbumID" => $album_id));
        $this->db->update(ALBUMS);


        $this->load->model('album/album_model');
        $activity_id = $this->album_model->get_album_activity_id($album_id,TRUE);

        $this->db->set('IsCommentable',1);
        $this->db->where('ActivityID',$activity_id);
        $this->db->update(ACTIVITY);
    }

}

//End of file cron.php