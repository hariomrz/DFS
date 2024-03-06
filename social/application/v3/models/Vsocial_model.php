<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Vsocial_model extends Common_Model {

    protected $banner_image = array('profilebanner'  =>array('1200x300'));
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Function Name: generate_thumb
     * @param temp_file,dir_name,file_name,thumb[],zc[],using_phpthumb,pathToFolder,type
     * Description: generate thumbnails of images
     */
    public function generate_thumb($temp_file, $dir_name, $file_name, $thumb = array(), $zc = array(), $using_phpthumb = 0,$pathToFolder='',$type='') {
        $name_parts = pathinfo($file_name);
        $ext = strtolower($name_parts['extension']);

        if ($using_phpthumb == 1) {
            $this->load->library('phpthumb');   

            $temp_file = file_get_contents($temp_file);
            $cnt = 1;

            foreach ($thumb as $i => $row) {
                $w = $row[0];
                $h = $row[1];
                $phpThumb = new phpThumb();
                $phpThumb->resetObject();
                $phpThumb->setSourceData($temp_file);
                if (isset($w))
                    $phpThumb->setParameter('w', $w);

                if (isset($h))
                    $phpThumb->setParameter('h', $h);
                if($dir_name==''){
                    $s3_path = $file_name;
                    $local_path = $file_name;
                } else {
                    $s3_path = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                    $local_path = DOCUMENT_ROOT .'/'. $pathToFolder . '/' . $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                }
                if ($zc[$i] == 1)
                    $phpThumb->setParameter('zc', true);

                if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!
                    if ($phpThumb->RenderToFile($local_path)) { //save file to destination
                        if (strtolower(IMAGE_SERVER) == 'remote') {
                            $s3 = new S3(array("access_key"=>AWS_ACCESS_KEY,"secret_key"=>AWS_SECRET_KEY,"region"=>BUCKET_ZONE,"use_ssl"=>false,"verify_peer"=>true));
                            $is_s3_upload = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ);
                        }
                    }
                }
                $cnt++;
            }
        } else {
            $this->load->library('image_lib');
            foreach ($thumb as $row) {
                $w = $row[0];
                $h = $row[1];
                if($dir_name==''){
                    $size = $file_name;
                } else {
                    $size = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                }
                
                $config = array();
// create thumb
                $config['image_library'] = 'GD2';
                $config['source_image'] = $temp_file;
                $config['new_image'] = $size;
                $config['create_thumb'] = false;
                $config['maintain_ratio'] = true;
                $config['width'] = $w;
                $config['height'] = $h;
                $this->image_lib->initialize($config);
                if ($this->image_lib->resize()) {
                    if (strtolower(IMAGE_SERVER) == 'remote' && $type!='wall') {
                        $s3 = new S3(array("access_key"=>AWS_ACCESS_KEY,"secret_key"=>AWS_SECRET_KEY,"region"=>BUCKET_ZONE,"use_ssl"=>false,"verify_peer"=>true));
                        $is_s3_upload = $s3->putObjectFile($size, BUCKET, $size, S3::ACL_PUBLIC_READ);
                        if (!$is_s3_upload) {
                            
                        }
                    }
                }
                $this->image_lib->clear();
            }
        }
    }

    /**
     * Function Name: save_image
     * @param ProfilePicture,img
     * Description: save image if signup by social media
     */
    public function save_image($ProfilePicture,$img){
        $fileName = FCPATH.PATH_IMG_UPLOAD_FOLDER.PATH_IMG_PROFILE.'/'.$ProfilePicture;
        write_file($fileName, $img);
        if(IMAGE_SERVER=='remote'){
            $this->upload_image(PATH_IMG_UPLOAD_FOLDER,$fileName, PATH_IMG_PROFILE.'/'.$ProfilePicture);
        }
        return $fileName;
    }

    /**
     * Function Name: removeProfileCover
     * @param UserID
     * Description: remove profile cover of user
     */
    public function removeProfileCover($UserID){
        $this->db->where('UserID',$UserID);
        $this->db->update(USERS,array('ProfileCover'=>''));
        return get_profile_cover('');
    }

    /**
     * Function Name: removeProfilePicture
     * @param UserID
     * Description: remove profile picture of user
     */
    public function removeProfilePicture($UserID){
        $this->db->where('UserID',$UserID);
        $this->db->update(USERS,array('ProfilePicture'=>''));
        $PictureUrl = get_full_path('profile_image',$UserID,'');
        $this->session->set_userdata('ProfilePicture','');
        return $PictureUrl;
    }

    /**
     * Function Name: upload_image
     * @param dir_name,temp_file,file_name,type
     * Description: upload image to S3/local folders
     */
    public function upload_image($dir_name,$temp_file,$file_name,$type=''){
        if (strtolower(IMAGE_SERVER) == 'remote') {
//if upload on s3 is enabled
//instantiate the class
            if(empty($temp_file)){
                return false;
            }
            $s3 = new S3(array("access_key"=>AWS_ACCESS_KEY,"secret_key"=>AWS_SECRET_KEY,"region"=>BUCKET_ZONE,"use_ssl"=>false,"verify_peer"=>true));

            $FilePath = $dir_name  . $file_name;
            $is_s3_upload = $s3->putObjectFile($temp_file, BUCKET, $FilePath, S3::ACL_PUBLIC_READ);

            if (!$is_s3_upload) {
                $is_file_uploaded = false;
            } else {
                $is_file_uploaded = true;
            }
        } else {
//Your upload directory, see CI user guide
            $config['upload_path'] = $dir_name;
            $config['allowed_types'] = 'gif|jpg|png|JPG|GIF|PNG|jpeg|JPEG|mp4|MP4';
            $config['max_size'] = '100000';
            $config['file_name'] = $file_name;
//Load the upload library
            $this->load->library('upload', $config);
            $success = $this->upload->do_upload('qqfile');
            if ($success) {
                $is_file_uploaded = true;
            } else {
                $is_file_uploaded = false;
            }
        }
        return $is_file_uploaded;
    }

    /**
     * Function Name: updateMediaUploadParam
     * @param data[]
     * Description: Not in use
     */
    function updateMediaUploadParam($data = array()) {
        if (!empty($data)) {
            $res = $this->db->insert(MEDIA, $data);
            $this->load->model(array('admin/media_model'));
            $this->media_model->checkMediaCounts($data, true); /* update media count */

            return $res;
        }
    }

    /**
     * Function Name: checkMediaCounts
     * @param paramArray[],updateFlag
     * Description: check and update media analytics count
     */
    function checkMediaCounts($paramArray = array(), $updateFlag = false) {
        $where = 'StatusID != 3';

        $selectFromArray = array();
        $whereFromArray = array();
        
        // Escape input data
        foreach ($paramArray as $key => $val) {
            $paramArray[$key] = $this->db->escape_str($val);
        }
        
        $select = 'COUNT(CASE WHEN IsAdminApproved = 1 then 1 ELSE NULL END) as admin_approved, COUNT(CASE WHEN IsAdminApproved = 0 then 1 ELSE NULL END) as admin_yet_to_approve';

        if (isset($paramArray['DeviceID'])) {
            $selectFromArray['selectForDeviceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND DeviceID=' . $paramArray['DeviceID'] . ') then 1 ELSE NULL END) as admin_approved_for_device, COUNT(CASE WHEN (IsAdminApproved = 0 AND DeviceID=' . $paramArray['DeviceID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_device';
            $whereFromArray['DeviceID'] = $paramArray['DeviceID'];

            /* filter by user */
            if (isset($paramArray['UserID'])) {
                $selectFromArray['filterForDeviceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND DeviceID=' . $paramArray['DeviceID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_device, COUNT(CASE WHEN (IsAdminApproved = 0 AND DeviceID=' . $paramArray['DeviceID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_device';
            }
            /* end filter by user */
        }

        if (isset($paramArray['SourceID'])) {
            $selectFromArray['selectForSourceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND SourceID=' . $paramArray['SourceID'] . ') then 1 ELSE NULL END) as admin_approved_for_source, COUNT(CASE WHEN (IsAdminApproved = 0 AND SourceID=' . $paramArray['SourceID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_source';
            $whereFromArray['SourceID'] = $paramArray['SourceID'];

            /* filter by user */
            if (isset($paramArray['UserID'])) {
                $selectFromArray['filterForSourceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND SourceID=' . $paramArray['SourceID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_source, COUNT(CASE WHEN (IsAdminApproved = 0 AND SourceID=' . $paramArray['SourceID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_source';
            }
            /* end filter by user */
        }

        if (isset($paramArray['MediaExtensionID'])) {
            $selectFromArray['selectForMediaExtensionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaExtensionID=' . $paramArray['MediaExtensionID'] . ') then 1 ELSE NULL END) as admin_approved_for_mediaext, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaExtensionID=' . $paramArray['MediaExtensionID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_mediaext';
            $whereFromArray['MediaExtensionID'] = $paramArray['MediaExtensionID'];

            /* filter by user */
            if (isset($paramArray['UserID'])) {
                $selectFromArray['filterForMediaExtensionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaExtensionID=' . $paramArray['MediaExtensionID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_mediaext, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaExtensionID=' . $paramArray['MediaExtensionID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_mediaext';
            }
            /* end filter by user */
        }

        if (isset($paramArray['MediaSectionID'])) {
            $selectFromArray['selectForMediaSectionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSectionID=' . $paramArray['MediaSectionID'] . ') then 1 ELSE NULL END) as admin_approved_for_media_sec_ref, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSectionID=' . $paramArray['MediaSectionID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_media_sec_ref';
            $whereFromArray['MediaSectionID'] = $paramArray['MediaSectionID'];

            /* filter by user */
            if (isset($paramArray['UserID'])) {
                $selectFromArray['filterForMediaSectionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSectionID=' . $paramArray['MediaSectionID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_media_sec_ref, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSectionID=' . $paramArray['MediaSectionID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_media_sec_ref';
            }
            /* end filter by user */
        }

        if (isset($paramArray['MediaSizeID'])) {
            $selectFromArray['selectForMediaSizeID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSizeID=' . $paramArray['MediaSizeID'] . ') then 1 ELSE NULL END) as admin_approved_for_media_size, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSizeID=' . $paramArray['MediaSizeID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_media_size';
            $whereFromArray['MediaSizeID'] = $paramArray['MediaSizeID'];

            /* filter by user */
            if (isset($paramArray['UserID'])) {
                $selectFromArray['filterForMediaSizeID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSizeID=' . $paramArray['MediaSizeID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_media_size, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSizeID=' . $paramArray['MediaSizeID'] . ' AND UserID=' . $paramArray['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_media_size';
            }
            /* end filter by user */
        }


        if (!empty($selectFromArray)) {
            $select = '';
            foreach ($selectFromArray as $key => $val) {
                if ($select != '')
                    $select .= ', ';

                $select .= $val;
            }

            if (!empty($whereFromArray)) {
                $tempWhere = '';
                foreach ($whereFromArray as $key => $val) {
                    if ($tempWhere != '')
                        $tempWhere .= ' OR ';

                    $tempWhere .= $key . '=' . $val;
                }
                $where .= ' AND (' . $tempWhere . ')';
            }
        }


        $this->db->select($select);
        $this->db->from(MEDIA);

        if (!empty($where)) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        $dataArray = $query->row_array();
        /* update media count */
        if ($updateFlag) {
            foreach ($paramArray as $key => $val) {
                if ($key == 'DeviceID') {/* update MediaDeviceCounts */
                    $insertUpdateArray = array(
                        'table' => MEDIADEVICECOUNTS,
                        'where' => array('colName' => 'DeviceTypeID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_device'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_device'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                } elseif ($key == 'SourceID') {/* update MediaSourceCount */
                    $insertUpdateArray = array(
                        'table' => MEDIASOURCECOUNT,
                        'where' => array('colName' => 'SourceID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_source'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_source'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                } elseif ($key == 'MediaExtensionID') {/* update MediaExtensionCount */
                    $insertUpdateArray = array(
                        'table' => MEDIAEXTENSIONCOUNT,
                        'where' => array('colName' => 'MediaExtensionID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_mediaext'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_mediaext'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                } elseif ($key == 'MediaSectionID') {/* update MediaSectionCount */
                    $insertUpdateArray = array(
                        'table' => MEDIASECTIONCOUNT,
                        'where' => array('colName' => 'MediaSectionID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_media_sec_ref'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_media_sec_ref'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                } elseif ($key == 'MediaSizeID') {/* update MediaSizeCounts */
                    $insertUpdateArray = array(
                        'table' => MEDIASIZECOUNTS,
                        'where' => array('colName' => 'MediaSizeID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_media_size'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_media_size'])
                    );
                    $this->insertUpdate($insertUpdateArray);
                }
            }
        }
    }

    /**
     * Function Name: insertUpdate
     * @param data[]
     * Description: insert/update row
     */
    function insertUpdate($data = array()) {
        $this->db->select($data['where']['colName']);
        $this->db->where(array($data['where']['colName'] => $data['where']['val']));
        $this->db->from($data['table']);
        $this->db->limit(1);
        $query = $this->db->get();
        $resArray = $query->row_array();
        if (!empty($resArray)) {/* update */
            $this->db->where(array($data['where']['colName'] => $data['where']['val']));
            $this->db->update($data['table'], $data['data']);
        } else {/* insert */
            $insertArray = array_merge($data['data'], array($data['where']['colName'] => $data['where']['val']));
            $this->db->insert($data['table'], $insertArray);
        }
    }

    // Cropping Function

    /**
     * Function Name: get_path_img_original_folder
     * @param file_name,upload_type
     * Description: get path of original image
     */
    public function get_path_img_original_folder($file_name,$upload_type='profilebanner'){
        $dir_name = IMAGE_ROOT_PATH."/".$upload_type."/".$file_name ;       
        return $dir_name ;
    }

    /**
     * Function Name: get_path_img_thumb_folder
     * @param upload_type,folder_name
     * Description: get path to image thumbnail folder
     */
    public function get_path_img_thumb_folder($upload_type,$folder_name=''){

        $dir_name = IMAGE_ROOT_PATH ;
        if($upload_type == 'profilebanner'){
            $dir_name .= "/".$upload_type."/".$folder_name."/" ;
        }
        return $dir_name ;
    }

    /**
     * Function Name: create_thumbnails
     * @param file_name,upload_type,cropped
     * Description: create thumbnail for profile banner
     */
    public function create_thumbnails($file_name, $upload_type, $is_cropped= 0) {
        
        $this->load->library('image_lib');

        

        $thumbnail_arry    = $this->get_all_thumnails_size($upload_type);
        
        if($is_cropped ==0){
            $source_image_path = $this->get_path_img_original_folder($file_name,$upload_type) ;
        } else {
            $source_image_path =  IMAGE_ROOT_PATH."/".$upload_type."/".$file_name ;
        }

        $output = array();

        

        foreach($thumbnail_arry as $val){
            $dimension                = explode('x', $val);
            
            $config['new_image']      = $this->get_path_img_thumb_folder($upload_type,$val);
            $config['image_library']  = 'gd2';
            $config['source_image']   = $source_image_path;
            $config['create_thumb']   = TRUE;
            $config['thumb_marker']   = '';
            $config['maintain_ratio'] = FALSE;
            $config['width']          = $dimension[0];
            $config['height']         = $dimension[1];
          
            $this->image_lib->initialize($config); 
            $this->image_lib->resize();
            $this->image_lib->clear();
            $fname = end(explode('/',$file_name));
            $output[$val] = $this->get_http_img_path($upload_type, $val,$fname);
            $old_img = $config['new_image'];
            $config['new_image'] = $config['new_image'].$fname;
            //echo $config['new_image'];
            //echo $file_name;
            if(IMAGE_SERVER=='remote'){
               $this->upload_image('upload/profilebanner/1200x300/',$config['new_image'] ,$fname);
            }
        }
        
        return $output;
        
    }

    /**
     * Function Name: get_http_img_path
     * @param upload_type,folder_name,file_name
     * Description: returns http path of image
     */
    public function get_http_img_path($upload_type, $folder_name,$file_name){
        $dir_name = IMAGE_HTTP_PATH;

        if($upload_type == 'profilebanner'){
            $dir_name .= "profilebanner/".$folder_name."/".$file_name ;
        }       
        return $dir_name ;

    }

    /**
     * Function Name: get_all_thumnails_size
     * @param upload_type
     * Description: return array of profile banner thumbnail size
     */
    public function get_all_thumnails_size($upload_type) {

        if($upload_type == 'profilebanner'){
            return $this->banner_image['profilebanner'] ;
        }
        
    }

    /**
     * Function Name: save_cropped_image
     * @param upload_type, unique_id, file_name,thumb_data,SourceID,DeviceID
     * Description: save cropped image in database
     */
    public function save_cropped_image($upload_type, $unique_id, $file_name,$thumb_data=array(),$SourceID=1,$DeviceID=1){
        $this->load->model('login_model');
        $this->load->model('wall_model');

        if($upload_type == 'profilebanner'){
            $UserData = $this->login_model->activeLoginAuth(array('LoginSessionKey'=>$unique_id));
            if($thumb_data){
                foreach($thumb_data as $fldr){
                    $Media['UserID'] = $UserData['Data']['UserID'];
                    $Media['MediaSectionID'] = '5';
                    $Media['ImageName'] = $file_name;
                    $Media['Size'] = getFileSize($fldr);
                    $ext = end(explode('.',$file_name));
                    $Media['MediaExtensionID'] = $this->wall_model->getExtID($ext);
                    $Media['MediaSizeID'] = $this->wall_model->getMediaSizeID($Media['Size']/1024);
                    $Media['SourceID'] = $SourceID;
                    $Media['DeviceID'] = $DeviceID;
                    $Media['AlbumID'] = getAlbumID($UserData['Data']['UserID'],'Cover Pictures',3,$UserData['Data']['UserID']);
                    $Media['MediaSectionReferenceID'] = $UserData['Data']['UserID'];
                    $Media['StatusID'] = '2';
                    $Media['MediaGUID'] = uniqid();
                    $Media['CreatedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');
                    $Media['IsAdminApproved'] = '1';
                    $this->db->insert(MEDIA,$Media);
                    $insert_id = $this->db->insert_id();
                }
            }
            $this->db->where('UserID',$UserData['Data']['UserID']);
            $this->db->update(USERS,array('ProfileCover'=>$file_name));
            $this->deleteMedia($UserData['Data']['UserID'],'3','profile_banner',$insert_id);
            $this->checkMediaCounts($Media,true);
        }
    }

    /**
     * Function Name: updateProfilePicture
     * @param UserID,ProfilePicture,SourceID,DeviceID
     * Description: update profile picture in database
     */
    function updateProfilePicture($UserID,$ProfilePicture,$SourceID=1,$DeviceID=1){

        $this->db->where('UserID',$UserID);
        $this->db->update(USERS,array('ProfilePicture'=>$ProfilePicture));

        $this->load->model(array('wall_model','vsocial_model'));

        $Media['UserID'] = $UserID;
        $Media['MediaSectionID'] = '1';
        $Media['ImageName'] = $ProfilePicture;
        $Media['Size'] = getFileSize(IMAGE_HTTP_PATH.'/profile/'.$ProfilePicture);
        $ext = end(explode('.',$ProfilePicture));
        $Media['MediaExtensionID'] = $this->wall_model->getExtID($ext);
        $Media['MediaSizeID'] = $this->wall_model->getMediaSizeID($Media['Size']/1024);
        $Media['SourceID'] = $SourceID;
        $Media['DeviceID'] = $DeviceID;
        $Media['AlbumID'] = getAlbumID($UserID,'Profile Pictures',1,$UserID);
        $Media['MediaSectionReferenceID'] = $UserID;
        $Media['StatusID'] = '2';
        $Media['MediaGUID'] = uniqid();
        $Media['CreatedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');
        $Media['IsAdminApproved'] = '1';
        $this->db->insert(MEDIA,$Media);
        $insert_id = $this->db->insert_id();
        $this->deleteMedia($UserID,'3','profile',$insert_id);
        $this->vsocial_model->checkMediaCounts($Media,true);
    }

    /**
     * Function Name: deleteMedia
     * @param UserID,StatusID,Type,CurrentMedia
     * Description: Add entry in delete media table
     */
    function deleteMedia($UserID,$StatusID,$Type,$currentMedia){

        $SectionID = $this->getSectionID($Type);
        $data = $this->db->get_where(MEDIA,array('MediaSectionID'=>$SectionID,'UserID'=>$UserID));
        if($data->num_rows()){
            $m = array();
            foreach($data->result() as $val){
                if(!$this->isAlreadyDeleted($val->MediaID) && $val->MediaID!=$currentMedia){
                    $Media['StatusID'] = $StatusID;
                    $Media['MediaID'] = $val->MediaID;
                    $Media['CreatedDate'] = $val->CreatedDate;
                    $Media['DeletedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');
                    $m[] = $Media;
                }
            }
            if($m){
                $this->db->insert_batch(DELETEDMEDIA,$m);
            }
        }
    }

    /**
     * Function Name: isAlreadyDeleted
     * @param MediaID
     * Description: check if media is already deleted or not
     */
    function isAlreadyDeleted($MediaID){
        $query = $this->db->get_where(DELETEDMEDIA,array('MediaID'=>$MediaID,'StatusID'=>'3'));
        if($query->num_rows()){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function Name: getSectionID
     * @param Type
     * Description: Returns Media Section ID
     */
    function getSectionID($Type){
        $query = $this->db->get_where(MEDIASECTIONS,array('MediaSectionAlias'=>$Type));
        if($query->num_rows()){
            return $query->row()->MediaSectionID;
        }
    }

    /**
     * Function Name: addSocialProfilePicture
     * @param Type
     * Description: add image for social signup and analytics
     */
    function addSocialProfilePicture($Data){
        $this->load->model('wall_model');
        $Data['Size'] = getFileSize(IMAGE_HTTP_PATH.PATH_IMG_PROFILE.'/'.$Data['ProfilePicture']);
        $Data['ExtID'] = $this->wall_model->getExtID(end(explode('.',$Data['ProfilePicture'])));
        $Data['SizeID'] = $this->wall_model->getMediaSizeID($Data['Size']/1024);
        $Media = array('UserID' =>  $Data['UserID'],'MediaSectionID' => '1', 'ImageName' => $Data['ProfilePicture'], 'Size' => $Data['Size'],'MediaExtensionID' => $Data['ExtID'], 'MediaSizeID' => $Data['SizeID'], 'SourceID' =>$Data['SourceID'], 'DeviceID' => $Data['DeviceTypeID'],'AlbumID' => getAlbumID($Data['UserID'],'Profile Pictures',1,$Data['UserID']), 'MediaSectionReferenceID' => $Data['UserID'], 'StatusID' => '2', 'MediaGUID' => uniqid(), 'CreatedDate' => getCurrentDate('%Y-%m-%d %H:%i:%s'), 'IsAdminApproved' => '1');
        $this->db->insert(MEDIA,$Media);
        $this->vsocial_model->checkMediaCounts($Media,true);
    }

    /**
      * Function Name : SaveWallMedia
      * @param 
      * Description: Save wall media 
      */
    function saveWallMedia($UserID,$Media){
        $this->load->model('wall_model');
        if($Media){
            $Size = getFileSize(IMAGE_HTTP_PATH.PATH_IMG_PROFILE.'/'.$Media);
            $ExtID = $this->wall_model->getExtID(end(explode('.',$Media)));
            $SizeID = $this->wall_model->getMediaSizeID($Size/1024);
            $Media = array('UserID' =>  $UserID,'MediaSectionID' => '3', 'ImageName' => $Media, 'Size' => $Size,'MediaExtensionID' => $ExtID, 'MediaSizeID' => $SizeID, 'SourceID' =>'1', 'DeviceID' => '1','AlbumID' => getAlbumID($UserID,'Timeline Photos',1,$UserID), 'MediaSectionReferenceID' => '0', 'StatusID' => '2', 'MediaGUID' => get_guid(), 'CreatedDate' => getCurrentDate('%Y-%m-%d %H:%i:%s'), 'IsAdminApproved' => '1');
            $this->db->insert(MEDIA,$Media);
            return $Media['MediaGUID'];
        }
    }
    
    /**
     * Save Cropped banner image data into media table
     * @param integer $UserID
     * @param string $ImageName
     * @param integer $advertiser_id
     * @param integer $SourceID
     * @param integer $DeviceID
     */
    function save_banner_media($UserID, $ImageName, $advertiser_id, $banner_module, $SourceID=1,$DeviceID=1,$Resolution=''){
        $this->load->model(array('upload_file_model'));

        $Media['UserID'] = $UserID;
        $Media['MediaSectionID'] = 7;
        $Media['ImageName'] = $ImageName;
        $Media['ImageUrl'] =  $ImageName;
        $Media['Size'] = @filesize(IMAGE_SERVER_PATH . 'upload/banner/'.$ImageName);
        //$Media['Size'] = getFileSize(IMAGE_SERVER_PATH . 'upload/banner/'.$ImageName);
        
        $ext = @end(explode('.',$ImageName));
        $Media['MediaExtensionID'] = $this->upload_file_model->getExtID($ext);
        $Media['MediaSizeID'] = $this->upload_file_model->getMediaSizeID($Media['Size']/1024);
        $Media['SourceID'] = $SourceID;
        $Media['DeviceID'] = $DeviceID;
        $Media['AlbumID'] = get_album_id($UserID,'Advertise Banner',18, $advertiser_id);
        $Media['MediaSectionReferenceID'] = 0;
        $Media['ModuleEntityID'] = $advertiser_id;
        $Media['StatusID'] = '2';
        $Media['MediaGUID'] = get_guid();
        $Media['CreatedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');
        $Media['IsAdminApproved'] = '1';
        $Media['Caption'] = $banner_module;
        $Media['Resolution'] = $Resolution;
        $this->db->insert(MEDIA,$Media);
        $insert_id = $this->db->insert_id();
        $this->checkMediaCounts($Media,true);
    }
    
    /**
     * Save Cropped default banner image data into media table
     * @param integer $UserID
     * @param string $ImageName
     * @param integer $advertiser_id
     * @param integer $SourceID
     * @param integer $DeviceID
     */
    function save_default_banner_media($UserID, $ImageName, $MediaSectionID, $SourceID=1,$DeviceID=1){
        $this->load->model(array('wall_model','vsocial_model'));

        $Media['UserID'] = $UserID;
        $Media['MediaSectionID'] = $MediaSectionID;
        $Media['ImageName'] = $ImageName;
        $Media['ImageUrl'] =  $ImageName;
        $Media['Size'] = getFileSize(IMAGE_SERVER_PATH . 'upload/banner/'.$ImageName);
        
        $ext = @end(explode('.',$ImageName));
        $Media['MediaExtensionID'] = $this->wall_model->getExtID($ext);
        $Media['MediaSizeID'] = $this->wall_model->getMediaSizeID($Media['Size']/1024);
        $Media['SourceID'] = $SourceID;
        $Media['DeviceID'] = $DeviceID;
        $Media['AlbumID'] = getAlbumID($UserID,'Advertise Default Banner',18, $MediaSectionID);
        $Media['MediaSectionReferenceID'] = 0;
        //$Media['ModuleEntityID'] = $advertiser_id;
        $Media['StatusID'] = '2';
        $Media['MediaGUID'] = get_guid();
        $Media['CreatedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');
        $Media['IsAdminApproved'] = '1';
        $this->db->insert(MEDIA,$Media);
        $insert_id = $this->db->insert_id();
        $this->checkMediaCounts($Media,true);
    }
    
    /**
     * Save Cropped Raceevent image data into media table
     * @param integer $UserID
     * @param string $ImageName
     * @param integer $advertiser_id
     * @param integer $SourceID
     * @param integer $DeviceID
     */
    function save_raceevent_media($UserID, $ImageName, $RaceID, $RaceEvent, $SourceID=1,$DeviceID=1){
        $this->load->model(array('wall_model','vsocial_model'));

        $Media['UserID'] = $UserID;
        $Media['MediaSectionID'] = 11;
        $Media['ImageName'] = $ImageName;
        $Media['ImageUrl'] =  $ImageName;
        $Media['Size'] = getFileSize(IMAGE_SERVER_PATH . 'upload/raceevent/'.$ImageName);
        
        $ext = end(explode('.',$ImageName));
        $Media['MediaExtensionID'] = $this->wall_model->getExtID($ext);
        $Media['MediaSizeID'] = $this->wall_model->getMediaSizeID($Media['Size']/1024);
        $Media['SourceID'] = $SourceID;
        $Media['DeviceID'] = $DeviceID;
        $Media['AlbumID'] = getAlbumID($UserID,'Race Event',30, $RaceID);
        $Media['MediaSectionReferenceID'] = 0;
        $Media['ModuleEntityID'] = $RaceID;
        $Media['StatusID'] = '2';
        $Media['MediaGUID'] = get_guid();
        $Media['CreatedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');
        $Media['IsAdminApproved'] = '1';
        $Media['Caption'] = $RaceEvent;
        $this->db->insert(MEDIA,$Media);
        $insert_id = $this->db->insert_id();
        $this->checkMediaCounts($Media,true);
    }
}

?>