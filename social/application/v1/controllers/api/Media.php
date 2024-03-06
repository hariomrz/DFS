<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Media extends Common_API_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->load->model(array('media/media_model','users/user_model'));
    }

    function get_entity_media_post()
    {
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;
        $config = array(
            array(
                'field' => 'ModuleID',
                'label' => 'module id',
                'rules' => 'trim|required|numeric'
            ),
            array(
                'field' => 'ModuleEntityGUID',
                'label' => 'module entity guid',
                'rules' => 'trim|required|validate_guid[ModuleID]'
            )
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $module_id          = $data['ModuleID'];
            $module_entity_guid = $data['ModuleEntityGUID'];
            $module_entity_id   = get_detail_by_guid($module_entity_guid,$module_id);
            $page_no        = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size      = isset($data['PageSize']) ? $data['PageSize'] : 4;
            $return['Data']     = $this->media_model->get_entity_media($module_id, $module_entity_id, $user_id, $page_no, $page_size);
        }
        $this->response($return);
    }

    function get_event_media_post()
    {
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;
        $config = array(
            array(
                'field' => 'ModuleID',
                'label' => 'module id',
                'rules' => 'trim|required|numeric'
            ),
            array(
                'field' => 'ModuleEntityGUID',
                'label' => 'module entity guid',
                'rules' => 'trim|required|validate_guid[ModuleID]'
            )
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $module_id          = $data['ModuleID'];
            $module_entity_guid = $data['ModuleEntityGUID'];
            $module_entity_id   = get_detail_by_guid($module_entity_guid,$module_id);
            $page_no        = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size      = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $return['Data']     = $this->media_model->get_event_media($module_id,$module_entity_id,$user_id, $page_no, $page_size);
        }
        $this->response($return);
    }

    function get_category_media_post(){
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;
        $config = array(
            array(
                'field' => 'ModuleID',
                'label' => 'module id',
                'rules' => 'trim|required|numeric'
            ),
            array(
                'field' => 'ModuleEntityGUID',
                'label' => 'module entity guid',
                'rules' => 'trim|required|validate_guid[ModuleID]'
            )
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $module_id          = $data['ModuleID'];
            $module_entity_guid = $data['ModuleEntityGUID'];
            $module_entity_id   = get_detail_by_guid($module_entity_guid,$module_id);
            $page_no        = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size      = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $return['Data']     = $this->media_model->get_category_media($module_id,$module_entity_id,$user_id, $page_no, $page_size);
        }
        $this->response($return);
    }

    /**
     * [details_post Get the media details]
     * @return [json] [media details]
     */
    function details_all_post()
    {
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
        $this->user_model->set_user_profile_url($user_id);
        $config = array(
            array(
                'field' => 'MediaGUID',
                'label' => 'media guid',
                'rules' => 'trim|required'
            )
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            }
            $type = isset($data['Type']) ? $data['Type'] : 'Any' ;
            $paging = isset($data['Paging']) ? $data['Paging'] : '' ;
            $show_all = isset($data['ShowAll']) ? $data['ShowAll'] : 0 ;
            if($show_all) {
                $activity_id = 0;
            } else {
                $activity_id = $this->media_model->get_activity_id_by_media_guid($media_guid);
            }
            $return['Data'] = $this->media_model->get_all_media_details($user_id,$media_guid,$paging,'Any',$activity_id);
        }
        $this->response($return);
    }

    /**
     * [details_post Get the media details]
     * @return [json] [media details]
     */
    function details_post()
    {
    	$return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID; 
        $this->user_model->set_user_profile_url($user_id);
        $config = array(
            array(
                'field' => 'MediaGUID',
                'label' => 'media guid',
                'rules' => 'trim|required'
            )
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            }
            $type = isset($data['Type']) ? $data['Type'] : 'Any' ;
            $paging = isset($data['Paging']) ? $data['Paging'] : '' ;
            $show_all = isset($data['ShowAll']) ? $data['ShowAll'] : 0 ;
            if($show_all){
                $activity_id = 0;
            } else {
                $activity_id = $this->media_model->get_activity_id_by_media_guid($media_guid);
            }
            $return['Data'] = $this->media_model->get_media_details($user_id,$media_guid,$paging,'Any',$activity_id);
        }
        $this->response($return);
    }

    function details_by_name_post()
    {
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
        $this->user_model->set_user_profile_url($user_id);
        $image_name = $data['ImageName'];
        $media_guid = $this->media_model->get_media_guid($image_name);
        $check_permission = $this->media_model->check_permission($user_id,$media_guid);
        if(!$check_permission){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('media_not_exists');
            $this->response($return);
        }
        $type = isset($data['Type']) ? $data['Type'] : 'Any' ;
        $paging = isset($data['Paging']) ? $data['Paging'] : '' ;
        $return['Data'] = $this->media_model->get_media_details($user_id,$media_guid,$paging);
        
        $this->response($return);
    }

    function get_media_guid_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;        
        $image_name = $data['ImageName'];
        $return['Data'] = $this->media_model->get_media_guid($image_name);
        $this->response($return);
    }

    /**
     * [add_comment_post Used ot post comment for media]
     * @return [json] [Success message]
     */
    function add_comment_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if($this->form_validation->run('api/media/add_comment') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $media_guid = $data['MediaGUID'];
            $comment = $data['Comment'];
            $media = isset($data['Media']) ? $data['Media'] : array() ;
            $return['Data'] = $this->media_model->add_comment($user_id,$media_guid,$comment,$media);
        }
        $this->response($return);
    }

    /**
     * [toggle_like_post Used to like/Unlike media]
     * @return [json] [Success message]
     */
    function toggle_like_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if($this->form_validation->run('api/media/toggle_like') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            }
            $return['Data'] = $this->media_model->toggle_like($user_id,$media_guid);

            /*added by gautam*/
            /* if($this->IsApp == 1){ // For Mobile 
                $this->load->model(array('activity/activity_model', 'users/friend_model'));
                $row = $this->media_model->get_media_details($user_id,$media_guid);
                $media = array(
                'MediaFolder'=>$row['MediaFolder'],
                'MediaGUID'=>$row['MediaGUID'],
                'ImageName'=>$row['ImageName'],
                'Caption'=>$row['Caption'],
                'MediaType'=>$row['MediaType'],
                'VideoLength'=>'',
                'ConversionStatus'=>'',
                'NoOfComments'=>$row['NoOfComments'],
                'NoOfLikes'=>$row['NoOfLikes'],
                'NoOfShares'=>$row['NoOfShares'],
                'LikeName'=> $return['Data']['LikeName'],
                'LikeList'=>$this->activity_model->getLikeDetails($media_guid, 'MEDIA', array(),0, 1,FALSE,$user_id),  
                'IsLike'=>$this->activity_model->checkLike($media_guid, 'MEDIA', $this->UserID)
                );

                $return['Data'] = $media;
            }
             * */            
        }
        $this->response($return);
    }

    /**
     * [flag_post Used to flag media]
     * @return [json] [Success message]
     */
    function flag_post(){
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if($this->form_validation->run('api/media/flag') == FALSE){
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            }
            $flag_reason = $data['FlagReason'];
            $result = $this->media_model->flag($user_id,$media_guid,$flag_reason);
            $return['ResponseCode']  = $result['ResponseCode'];
            $return['Message']       = $result['Message'];
        }
        $this->response($return);
    }

    /**
     * [comments_post Used to post comment on media]
     * @return [json] [Success message]
     */
    function comments_post(){
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if($this->form_validation->run('api/media/comments') == FALSE){
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            }
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1 ;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE ;
            $return['TotalRecords'] = 0;
            if($page_no == 1) {
                $return['TotalRecords'] = $this->media_model->comments($user_id,$media_guid,$page_no,$page_size, TRUE);
            }
            $return['Data'] = $this->media_model->comments($user_id,$media_guid,$page_no,$page_size);
        }
        $this->response($return);
    }

    /**
     * [like_details_post Used to get media like user details]
     * @return [json] [Like user list]
     */
    function like_details_post(){
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if($this->form_validation->run('api/media/like_details') == FALSE){
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            }
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1 ;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE ;
            $result = $this->media_model->like_details($user_id,$media_guid,$page_no,$page_size);
            $return['Data'] = $result['data'];
            $return['TotalRecords'] = $result['total_records'];
        }
        $this->response($return);
    }

    /**
     * [toggle_subscribe_post Used to subscibe/unsubscribe for media notification]
     * @return [json] [Success message]
     */
    function toggle_subscribe_post(){
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if($this->form_validation->run('api/media/toggle_subscribe') == FALSE){
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            }
            $return['Data']['IsSubscribed'] = $this->media_model->toggle_subscribe($user_id,$media_guid);
        }
        $this->response($return);
    }

    /**
     * [delete_post Used to delete media]
     * @return [json] [Success message]
     */
    function delete_post(){
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if($this->form_validation->run('api/media/delete') == FALSE){
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            } else if($check_permission==2){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $album_id = $this->media_model->delete($user_id,$media_guid);
            $return['Data'] = array();
            //set privacy on  media count zero    
            $album_data = get_detail_by_id($album_id, 13, "MediaCount,ActivityID,MediaID,AlbumGUID", 2);
            if($album_data['MediaCount']==0){
                $visibility = 4;
                $this->album_model->set_album_visibility($album_id,$visibility);
                $this->album_model->set_activity_visibility($album_data['ActivityID'],$visibility);
                $this->album_model->remove_album_cover($album_id);
            }
            $media_data = get_detail_by_guid($media_guid, 21, 'MediaID,MediaSectionID,ModuleID,ImageName,MediaSectionReferenceID', 2);
            $media_id = $media_data['MediaID'];
            if($album_data['MediaID'] == $media_id && $album_data['MediaCount']!=0){
                $last_media_data = $this->album_model->get_album_last_media($album_id);
                $media = array(
                        'MediaID' => $last_media_data['MediaID'],
                        'AlbumGUID' => $album_data['AlbumGUID']
                );
                $this->album_model->save_album($media);
            }
            //remove profile pic/cover pic if deleted picture is profile pic or cover pic
            $this->media_model->remove_profile_pic($media_data);
            $activity_id=$media_data['MediaSectionReferenceID'];
           initiate_worker_job('activity_cache', array('ActivityID'=>$activity_id )); 
        }
        $this->response($return);
    }

    /**
     * [privacy_post Used to update privacy for media parent entity]
     * @return [json] [Success message]
     */
    function privacy_post(){
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if($this->form_validation->run('api/media/privacy') == FALSE){
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            } else if($check_permission==2){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $privacy = $data['Visibility'];
            $return['Data'] = $this->media_model->privacy($user_id,$media_guid,$privacy);
        }
        $this->response($return);
    }

    /**
     * [share_details_post Used to get media details for share]
     * @return [json] [Success message]
     */
    function share_details_post(){
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if($this->form_validation->run('api/media/share_details') == FALSE){
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $media_guid = $data['MediaGUID'];
            $check_permission = $this->media_model->check_permission($user_id,$media_guid);
            if(!$check_permission){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_not_exists');
                $this->response($return);
            }
            $return['Data'] = $this->media_model->share_details($media_guid);
        }
        $this->response($return);
    }


    /**
     * [use to get all media of user]
     * @return [json] [Media List]
     */
    function list_post(){
        $return = $this->return;
        $data = $this->post_data;
        $UserID = $this->UserID;

        if(empty($data['ModuleID'])){
            $data['ModuleID'] =3;
        }  

        if(isset($data['EntityGUID']) && $data['EntityGUID']!=''){
            if($data['ModuleID']==3){
                $UserID = get_detail_by_guid($data['EntityGUID'],3,'UserID');
                 $records = $this->album_model->get_row('AlbumID', ALBUMS, "AlbumName='Wall Media' and   ModuleID='3' and ModuleEntityID='$UserID' ");
                 $EntityID = $records['AlbumID'];
             }
             elseif($data['ModuleID']==18){
                $EntityID = get_detail_by_guid($data['EntityGUID'],18,'PageID');
                 $records = $this->album_model->get_row('AlbumID', ALBUMS, "AlbumName='Wall Media' and   ModuleID='18' and ModuleEntityID='$EntityID' ");
                 $EntityID = $records['AlbumID'];
             }
             elseif($data['ModuleID']==1){
                $EntityID = get_detail_by_guid($data['EntityGUID'],1,'GroupID');
                 $records = $this->album_model->get_row('AlbumID', ALBUMS, "AlbumName='Wall Media' and   ModuleID='1' and ModuleEntityID='$EntityID' ");
                 $EntityID = $records['AlbumID'];
             }
        }
        $temp_count=$this->media_model->get_all_media($EntityID, $data, $UserID,TRUE);
        if($temp_count)
        {
            $Data = $this->media_model->get_all_media($EntityID, $data, $UserID,FALSE);
            if(!empty($Data)){
                $return['Data'] = $Data;
                $return['TotalRecords'] = $temp_count;
            }
        }
        $this->response($return);
    }








    
}