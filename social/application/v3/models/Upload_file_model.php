<?php
/**
 * This model is used for uploading file
 * @package    Upload_file_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Upload_file_model extends Common_Model {

    protected $pathToFolder;  
    protected $headers = array();  
    /* both variable folder and thmb size need to decide */        
    protected $profilebanner_folder         = array("profilebanner", "profilebanner/1200x300", "profilebanner/220x220");
    protected $profilebanner_thumb_size     = array(array('1200','300'), array("220", "220"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $profilebanner_zoom_crop_array= array(1,1,1);

    protected $profile_folder               = array("profile", "profile/36x36", "profile/220x220");
    protected $profile_thumb_size           = array(array("36", "36"), array("220", "220"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $profile_zoom_crop_array      = array(1, 1, 1);

    protected $link_folder               = array("link", "link/36x36", "link/220x220");
    protected $link_thumb_size           = array(array("36", "36"), array("220", "220"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $link_zoom_crop_array      = array(1, 1, 1);

    protected $blog_folder               = array("blog", "blog/220x220", "blog/750x260");
    protected $blog_thumb_size           = array(array("220", "220"), array("750", "260"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $blog_zoom_crop_array      = array(1, 1, 1);

    protected $album_folder               = array("album", "album/220x220", "album/750x500" );
    protected $album_thumb_size           = array(array("220", "220"), array("750", "500"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $album_zoom_crop_array      = array(1, 1, 1);

    protected $gallery_folder               = array("gallery", "gallery/220x220", "gallery/750x500" );
    protected $gallery_thumb_size           = array(array("220", "220"), array("750", "500"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $gallery_zoom_crop_array      = array(1, 1, 1);

    protected $group_folder                 = array("group", "group/36x36", "group/220x220", "group/750x500");
    protected $group_thumb_size             = array(array("36", "36"), array("220", "220"), array("750", "500"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $group_zoom_crop_array        = array(1, 1, 1, 1);

    protected $popup_folder                  = array("popup", "popup/220x220", "popup/750x500");
    protected $popup_thumb_size              = array(array("220", "220"), array("750", "500"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $popup_zoom_crop_array      = array(1, 1, 1);

    protected $wall_folder                  = array("wall", "wall/220x220", "wall/750x500");
    protected $wall_thumb_size              = array(array("220", "220"), array("750", "500"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $wall_zoom_crop_array         = array(1, 1, 1);

    protected $comments_folder              = array("comments", "comments/220x220", "comments/533x300");
    protected $comments_thumb_size          = array(array("220", "220"), array("533", "300"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $comments_zoom_crop_array     = array(1, 1, 1);

    protected $temp_folder                  = array("temp", "temp/192x191", "temp/36x36");
    protected $temp_thumb_size              = array(array("192", "191"), array("36", "36"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $temp_zoom_crop_array         = array(1, 1, 1);

    protected $messages_folder                  = array("messages", "messages/220x220");
    protected $messages_thumb_size              = array(array("220","220"));
    protected $messages_zoom_crop_array         = array(1, 1, 1);

    protected $ratings_folder                  = array("ratings", "ratings/220x220");
    protected $ratings_thumb_size              = array(array("220","220"));
    protected $ratings_zoom_crop_array         = array(1, 1, 1);

    protected $category_folder                  = array("category", "category/220x220");
    protected $category_thumb_size              = array(array("220","220"));
    protected $category_zoom_crop_array         = array(1, 1, 1);
    
    protected $skill_folder                  = array("skill", "skill/220x220");
    protected $skill_thumb_size              = array(array("220","220"));
    protected $skill_zoom_crop_array         = array(1, 1, 1);

    protected $poll_folder                      = array("poll", "poll/220x220");
    protected $poll_thumb_size                  = array(array("220", "220"), array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $poll_zoom_crop_array             = array(1, 1);

    protected $quiz_folder               = array("quiz");
    protected $quiz_thumb_size           = array(array(ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT));
    protected $quiz_zoom_crop_array      = array(1);

    protected $allowed_image_types          = 'gif|jpg|png|JPG|GIF|PNG|jpeg|JPEG|bmp|BMP';
    protected $allowed_image_max_size       = '16096'; //KB
    protected $allowed_image_max_width      = '1024';
    protected $allowed_image_max_height     = '768';

    protected $allowed_video_types          = 'mp4|MP4|3gp|3GP|webm|WEBM|ogg|OGG|mov|MOV|flv|FLV|mpeg|MPEG|mpg|MPG|wmv|WMV|swf|SWF|asf|ASF|avi|AVI';
    protected $allowed_video_max_size       = '51200'; //51200 MB

    protected $allowed_message_types        = 'pdf|PDF|pptx|PPTX|doc|DOC|dot|DOT|dotx|dotx|docb|DOCB|wpd|WPD|wps|WPS|docm|DOCM|docx|DOCX|txt|TXT|ppt|PPT|xls|XLS|xlsx|XLSX|odt|ODT|gif|jpg|png|JPG|GIF|PNG|jpeg|JPEG|bmp|BMP|docm|DOCM|pps|PPS|ppsx|PPSX|ods|ODS|odp|ODP|csv|CSV|rtf|RTF|m4a|M4A|m4p|M4P|mmf|MMF|mp3|MP3|ra|RA|rm|RM|wav|WAV|wma|WMA|webm|WEBM|ogg|OGG|MP4|mp4|3gp|3GP|mov|MOV|flv|FLV|mpeg|MPEG|mpg|MPG|wmv|WMV|swf|SWF|asf|ASF|xml|XML|otp|OTP|odg|ODG|xlt|XLT|xlm|XLM|xlsm|XLSM|xltx|XLTX|xltm|XLTM|sxc|SXC|xlw|XLW|pot|POT|pps|PPS|pptm|PPTM|potx|POTX|potm|POTM|ppsx|PPSX|sldx|SLDX|sldm|SLDM';
    protected $allowed_message_max_size     = '16096'; //MB

    /* both variable folder and thmb size need to decide */
    protected $name_skip_chars      = array(
        "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "-", "+", "=", "{", "}",
        "[", "]", "|", "\\", ":", ";", "\"", "'", "<", ",", ">", ".", "?", "/", " "
    );

    protected $VideoExt = array('mp4','ogg','webm');

    protected $MediaSectionIDArray = array(
                                        "profile"=>1,
                                        "group"=>2,
                                        "wall"=>3,
                                        "album"=>4,
                                        "profilebanner"=>5,
                                        "comments"=>6,
                                        "ratings"=>8,
                                        "blog"=>9,
                                        "category"=>10,
                                        "link"=>11,
                                        "poll"=>12,
                                        "gallery"=>14,
                                        "quiz"=>15,
                                    );

    protected $DeviceTypeIDArray = array(
                                        "native"=>1,
                                        "iphone"=>2,
                                        "androidphone"=>3,
                                        "ipad"=>4,
                                        "androidtablet"=>5,
                                        "othermobiledevice "=>6
                                    );

    protected $MediaExtensionIDArray = array(
                                        "jpg"=>1,
                                        "jpeg"=>2,
                                        "png"=>3,
                                        "gif"=>4,
                                        "mp4"=>5,
                                        "mov"=>6,
                                        "3gp"=>7,
                                        "avi"=>8,
                                        "doc"=>10,
                                        "docx"=>11,
                                        "txt"=>12,
                                        "ppt"=>13,
                                        "xls"=>14,
                                        "odt"=>15,
                                        "xlsx"=>16,
                                        "pptx"=>17,
                                        "pdf"=>18,
                                        "bmp"=>19,
                                        "docm"=>20,
                                        "pps"=>21,
                                        "ppsx"=>22,
                                        "ods"=>23,
                                        "odp"=>24,
                                        "csv"=>25,
                                        "rtf"=>26,
                                        "m4a"=>27,
                                        "m4p"=>28,
                                        "mmf"=>29,
                                        "mp3"=>30,
                                        "ra"=>31,
                                        "rm"=>32,
                                        "wav"=>33,
                                        "wma"=>34,
                                        "webm"=>35,
                                        "flv"=>36,
                                        "mpeg"=>37,
                                        "mpg"=>38,
                                        "wmv"=>39,
                                        "swf"=>40,
                                        "asf"=>41
                                    );
    public $image_server_path = SITE_HOST . ROOT_FOLDER . '/';
    public $s3_credential = array("access_key"=>AWS_ACCESS_KEY,"secret_key"=>AWS_SECRET_KEY,"region"=>BUCKET_ZONE,"use_ssl"=>false,"verify_peer"=>true);
    function __construct() {
        parent::__construct();    
        $admin_thumb_profile                = "profile/".ADMIN_THUMB_WIDTH .'x'. ADMIN_THUMB_HEIGHT;
        $admin_thumb_group                  = "group/".ADMIN_THUMB_WIDTH .'x'. ADMIN_THUMB_HEIGHT;
        $admin_thumb_wall                   = "wall/".ADMIN_THUMB_WIDTH .'x'. ADMIN_THUMB_HEIGHT;
        $admin_thumb_album                  = "album/".ADMIN_THUMB_WIDTH .'x'. ADMIN_THUMB_HEIGHT;
        $admin_thumb_comments               = "comments/".ADMIN_THUMB_WIDTH .'x'. ADMIN_THUMB_HEIGHT;
        $admin_thumb_profilebanner_folder   = "profilebanner/".ADMIN_THUMB_WIDTH .'x'. ADMIN_THUMB_HEIGHT;
        $admin_thumb_gallery                  = "gallery/".ADMIN_THUMB_WIDTH .'x'. ADMIN_THUMB_HEIGHT;
        $admin_thumb_quiz                  = "quiz/".ADMIN_THUMB_WIDTH .'x'. ADMIN_THUMB_HEIGHT;

        $this->profile_folder[]         = $admin_thumb_profile;
        $this->group_folder[]           = $admin_thumb_group;
        $this->wall_folder[]            = $admin_thumb_wall;
        $this->album_folder[]           = $admin_thumb_album;
        $this->comments_folder[]        = $admin_thumb_comments;
        $this->profilebanner_folder[]   = $admin_thumb_profilebanner_folder;
        $this->gallery_folder[]         = $admin_thumb_gallery;
        $this->quiz_folder[]            = $admin_thumb_quiz;

        if (strtolower(IMAGE_SERVER) == 'remote') { //if upload on s3 is enabled
            $this->load->library('S3');
            $this->image_server_path = 'https://' . BUCKET . '.s3-'.BUCKET_ZONE.'.amazonaws.com/';
        }
        $this->pathToFolder = ROOT_FOLDER;
        $this->headers = array("Cache-Control" => "max-age=315360000", "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+1 years")));
    }

    public function uploadProfilePicture($Data,$user_id,$from_admin=false){
        if($Data['MediaGUID']) {
            $this->db->select('M.ImageName,MS.MediaSectionAlias,MS.MediaSectionID');
            $this->db->from(MEDIASECTIONS.' MS');
            $this->db->join(MEDIA.' M','M.MediaSectionID=MS.MediaSectionID','left');
            $this->db->where('M.MediaGUID',$Data['MediaGUID']);
            $this->db->limit(1);
            $media_query = $this->db->get();
            $media_details = $media_query->row_array();
            $MediaSectionID = $media_details['MediaSectionID'];
            if($MediaSectionID==1) {
                $Data['IsMediaExisted'] = 0;
            }
        }
        if($Data['IsMediaExisted']){
            $d = $Data;
            $d['ImageUrl'] = $Data['FilePath'].$Data['CropSize'].'/'.$Data['ImageName'];
            $Data['DeviceType'] = 'Web';
            $get_details = $this->saveFileFromUrl($Data);
            $Data['ImageName'] = $get_details['Data']['ImageName'];
            $Data['MediaGUID'] = $get_details['Data']['MediaGUID'];

            if(isset($media_details))
            {
                if($media_details['MediaSectionID']!=1)
                {
                    $file_full_path = PATH_IMG_UPLOAD_FOLDER.$media_details['MediaSectionAlias'].'/'.$media_details['ImageName'];
                    $file_full_path2 = PATH_IMG_UPLOAD_FOLDER.$media_details['MediaSectionAlias'].'/org_'.$media_details['ImageName'];
                    $destinationFile = PATH_IMG_UPLOAD_FOLDER.'profile/'.$Data['ImageName'];
                    $destinationFile2 = PATH_IMG_UPLOAD_FOLDER.'profile/org_'.$Data['ImageName'];
                    if(strtolower(IMAGE_SERVER) == 'remote')
                    {
                        $s3 = new S3($this->s3_credential);
                        $s3->copyObject(BUCKET, $file_full_path, BUCKET, $destinationFile, S3::ACL_PUBLIC_READ);
                        $s3->copyObject(BUCKET, $file_full_path2, BUCKET, $destinationFile2, S3::ACL_PUBLIC_READ);
                    }
                    else
                    {
                        copy($this->image_server_path.$file_full_path,$destinationFile);
                        copy($this->image_server_path.$file_full_path2,$destinationFile2);
                    }
                }
            }
        }
        $SourceImage = $Data['FilePath'].$Data['CropSize'].'/'.$Data['ImageName'];
        @file_put_contents($SourceImage,base64_decode($Data['ImageData']));
        
        $upload_flag = FALSE;
        if(strtolower(IMAGE_SERVER) == 'remote'){
            $s3_path        = $SourceImage;
            $s3             = new S3($this->s3_credential);
            $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);            
        }
        
        $thumb_size = $Data['Type'].'_thumb_size';
        $totalSize = 0;
        $size = @filesize($Data['FilePath'].$Data['ImageName']);
        if(isset($size) && !empty($size)) {
            $totalSize = $size;
        }
        foreach($this->$thumb_size as $i=>$val){
            $NewImage = $Data['FilePath'].$val[0].'x'.$val[1].'/'.$Data['ImageName'];
            $totalSize = $totalSize + $this->resizeImage($NewImage,$SourceImage,$val[0],$val[1],TRUE);
        }
        if($upload_flag) {
            @unlink($SourceImage);
        }
        $this->load->model('media/media_model');
        $this->db->set('Size',$totalSize);
        $this->db->set('MediaSizeID',$this->media_model->getMediaSizeID($totalSize));
        $this->db->where('MediaGUID',$Data['MediaGUID']);
        $this->db->update(MEDIA);
        
        $Data['ModuleID'] = isset($Data['ModuleID']) ? $Data['ModuleID'] : 3 ;
        $Data['ModuleEntityGUID'] = isset($Data['ModuleEntityGUID']) ? $Data['ModuleEntityGUID'] : 0 ;

        $this->updateProfilePicture($Data['MediaGUID'],$Data['ImageName'],$user_id,$Data['ModuleID'],$Data['ModuleEntityGUID'],$from_admin);
    }

    /**
     * [uploadTempFile Used to upload file temporary]
     * @param [type] $Data [input data for upload file Request]
     * @return [Object] [json object]
     */
    function uploadTempFile($Data,$Return){

        $chk_folder = PATH_IMG_UPLOAD_FOLDER;

        $this->check_folder_exist($chk_folder, array($Data['Type']));

        $config['upload_path']      = PATH_IMG_UPLOAD_FOLDER . $Data['Type']."/";
        $config['allowed_types']    = $this->allowed_image_types;
        $config['max_size']         = $this->allowed_image_max_size;
        $config['min_width']        = 800;
        $config['min_height']       = 300;
        $config['encrypt_name']     = TRUE;

        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload('qqfile')){
            $Return['ResponseCode'] = 412;
            $Errors = $this->upload->error_msg;
            if(!empty($Errors)){
                $Return['Message'] =  $Errors['0']; // first message
            }else{
                $Return['Message'] =  "Unable to fetch error code."; // first message
            }
            return $Return;
            //Shows all error messages as a string              
        } else {
            $data = $this->upload->data();
            $Return['Data']['FilePath'] = IMAGE_SERVER_PATH.PATH_IMG_UPLOAD_FOLDER.$Data['Type'].'/'.$data['file_name'];
            return $Return;
        }
    }

    /**
     * [uploadImage Used to upload file]
     * @param [type] $Data [input data for upload file Request]
     * @return [Object] [json object]
     */
    function uploadImage($Data,$generateThumb=1){     
        $Return['Message']      = lang('success');
        $Return['ResponseCode'] = 200;
        $Return['Data']         =array();

        $type       = strtolower($Data['Type']);
        $DeviceType = strtolower($Data['DeviceType']);

        $module_id   = isset($Data['ModuleID']) ? $Data['ModuleID'] : 0 ;
        $entity_guid = isset($Data['ModuleEntityGUID']) ? $Data['ModuleEntityGUID'] : 0 ;
        //Check request is from Frontend or not
        $is_added_from_frontend = (isset($Data['IsFrontEnd']) && $Data['IsFrontEnd']==0) ? 0 : 1;
        $entity_id   = 0;
        if($entity_guid && $module_id){
            $entity_id   = get_detail_by_guid($entity_guid, $module_id);
        }
        
        $file_input = 'qqfile';
        if(isset($Data['FileInput']))
        {
            $file_input = $Data['FileInput'];
        }

        $folder_arr = $type . '_folder';
        $thumb_arr  = $type . '_thumb_size';
        $zc_arr     = $type . '_zoom_crop_array';
 
        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
        $chk_folder = PATH_IMG_UPLOAD_FOLDER;

        $this->check_folder_exist($chk_folder, $this->$folder_arr);

        $config['upload_path']      = $dir_name . "/";
        $config['allowed_types']    = $this->allowed_image_types;
        $config['max_size']         = $this->allowed_image_max_size;
        if($type == 'messages'){
            $config['allowed_types']    = $this->allowed_message_types;
            $config['max_size']         = $this->allowed_message_max_size;
        }
        /*$config['max_width']        = $this->allowed_image_max_width;
        $config['max_height']       = $this->allowed_image_max_height;*/
        $config['encrypt_name']     = TRUE;
       
        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload($file_input)){
            $Return['ResponseCode'] = 412;
            $Errors = $this->upload->error_msg;
            if(!empty($Errors)){
                $Return['Message'] =  $Errors['0']; // first message
            }else{
                $Return['Message'] =  "Unable to fetch error code."; // first message
            }
            return $Return;
            //Shows all error messages as a string              
        } else {
            $upload_data = $this->upload->data();
            $file_name = $upload_data['file_name'];
                        
            rename(
                $upload_data['full_path'],
                $upload_data['file_path'].'/'.'org_'.$upload_data['file_name']
            );
            $upload_data['full_path'] = $upload_data['file_path'].'/'.'org_'.$upload_data['file_name'];
            
            // Copy original image and reduce its quality to 75%
            $this->generate_thumb($upload_data['full_path'], $dir_name, $upload_data['file_name'], array(array($upload_data['image_width'],$upload_data['image_height'])), $this->$zc_arr, 1,$this->pathToFolder,$type,1);
            $totalSize = 0;
            //thumb code

            $file_name_ext  = explode('.', $upload_data['file_name']);
            $ext            = strtolower(end($file_name_ext));
            if(in_array($ext,$this->VideoExt)){
                //$this->create_video_thumb($upload_data);
            } else {
                if($generateThumb){
                    $totalSize = $this->generate_thumb(
                        $upload_data['full_path'], 
                        $dir_name, 
                        $upload_data['file_name'], 
                        $this->$thumb_arr, 
                        $this->$zc_arr,
                        1,
                        $this->pathToFolder,
                        $type
                    );
                }
            }
            
            $upload_data['file_name'] = 'org_'.$upload_data['file_name'];  
            if (strtolower(IMAGE_SERVER) == 'remote') {
                $s3_path        = $dir_name .'/' . $upload_data['file_name'];
                $s3 = new S3($this->s3_credential);
                $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ,$this->headers);
                if($upload_flag) {
                    @unlink($s3_path);
                }
            }
            $image_type         = $ext;
            $MediaSectionID     = isset($this->MediaSectionIDArray[$type]) ? $this->MediaSectionIDArray[$type] : 1;
            $DeviceID           = $this->DeviceTypeID;
            if(empty($DeviceID)) {
                $DeviceID           = isset($this->DeviceTypeIDArray[$DeviceType]) ? $this->DeviceTypeIDArray[$DeviceType] : 1;
            }

            $MediaExtensionID   = isset($this->MediaExtensionIDArray[$image_type]) ? $this->MediaExtensionIDArray[$image_type] : 1;
            $totalSize = $upload_data['file_size']+$totalSize;
           

            $source_id = ($this->SourceID) ? $this->SourceID : 1 ;

            $Media = array(
                'MediaGUID'         => get_guid(),
                'UserID'            => $this->UserID,
                'OriginalName'      => $upload_data['orig_name'],
                'MediaSectionID'    => $MediaSectionID,
                'ModuleID'          => $module_id,
                'ModuleEntityID'    => $entity_id,
                'ImageName'         => $file_name,
                'ImageUrl'          => $file_name,
                'Size'              => $totalSize, //The file size in kilobytes
                'DeviceID'          => $DeviceID,
                'SourceID'          => $source_id,
                'MediaExtensionID'  => $MediaExtensionID,
                'MediaSectionReferenceID'=>0,
                'CreatedDate'       => get_current_date('%Y-%m-%d %H:%i:%s'),
                'StatusID'          => 1, // default pending
                'IsAdminApproved'   => 1,
                'AbuseCount'        => 0,
                'AlbumID'           => 0,
                'Caption'           => "" ,
                'MediaSizeID'       => $this->getMediaSizeID($totalSize),
                'AddedBy' => $is_added_from_frontend,
                'Resolution'           => $upload_data['image_width'].'x'.$upload_data['image_height']         
            ); 

            $this->save_media($Media,$this->UserID);
            //insert in to media table with status pending which means in 24 hours this file will be deleted from server.
            //MediaGUID, MediaName
            $Return['Data']=array(
                "MediaGUID"=>$Media['MediaGUID'],
                "ImageName"=>$Media['ImageName'],
                "ImageServerPath"=>IMAGE_SERVER_PATH.PATH_IMG_UPLOAD_FOLDER.$type,
                "OriginalName"=>$upload_data['orig_name'],
                "MediaType"=>'PHOTO',
                "MediaExtension"=>$ext,
                "MediaSectionAlias"=>($MediaSectionID==4?'album':''),
                "Resolution"       => $upload_data['image_width'].'x'.$upload_data['image_height']
            );
        }
        return $Return;
    }
    
/**
     * [uploadImage Used to upload file]
     * @param [type] $Data [input data for upload file Request]
     * @return [Object] [json object]
     */
    function upload_image_without_thumb($Data){
         
        $Return['Message']      = lang('success');
        $Return['ResponseCode'] = 200;
        $Return['Data']         = array();
        $is_added_from_frontend = (isset($Data['IsFrontEnd']) && $Data['IsFrontEnd']==0) ? 0 : 1;
        $Data['DeviceType'] = isset($Data['DeviceType'])?$Data['DeviceType']:'native';
        $type       = strtolower($Data['Type']);
        $DeviceType = strtolower($Data['DeviceType']);

        $module_id   = isset($Data['ModuleID']) ? $Data['ModuleID'] : 0 ;
        $entity_guid = isset($Data['ModuleEntityGUID']) ? $Data['ModuleEntityGUID'] : 0 ;
        $entity_id   = 0;
        if($entity_guid && $module_id){
            $entity_id   = get_detail_by_guid($entity_guid, $module_id);
        }
        
        $folder_arr = $type . '_folder';
        $zc_arr     = $type . '_zoom_crop_array';
 
        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
        $chk_folder = PATH_IMG_UPLOAD_FOLDER;

        $this->check_folder_exist($chk_folder, $this->$folder_arr);

        $config['upload_path']      = $dir_name . "/";
        $config['allowed_types']    = $this->allowed_image_types;
        $config['max_size']         = $this->allowed_image_max_size;
        if($type == 'messages'){
            $config['allowed_types']    = $this->allowed_message_types;
            $config['max_size']         = $this->allowed_message_max_size;
        }
        /*$config['max_width']        = $this->allowed_image_max_width;
        $config['max_height']       = $this->allowed_image_max_height;*/
        $config['encrypt_name']     = TRUE;
        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload('qqfile')){
            $Return['ResponseCode'] = 412;
            $Errors = $this->upload->error_msg;
            if(!empty($Errors)){
                $Return['Message'] =  $Errors['0']; // first message
            }else{
                $Return['Message'] =  "Unable to fetch error code."; // first message
            }
            return $Return;
            //Shows all error messages as a string              
        } else {
            $upload_data = $this->upload->data();
            $file_name = $upload_data['file_name'];
                                    
            rename(
                $upload_data['full_path'],
                $upload_data['file_path'].'/'.'org_'.$upload_data['file_name']
            );
            $upload_data['full_path'] = $upload_data['file_path'].'/'.'org_'.$upload_data['file_name'];
            // Copy original image and reduce its quality to 75%
            $this->generate_thumb($upload_data['full_path'], $dir_name, $upload_data['file_name'], array(array($upload_data['image_width'],$upload_data['image_height'])), $this->$zc_arr, 1,$this->pathToFolder,$type,1);
            $totalSize = 0;
            //thumb code

            $file_name_ext  = explode('.', $upload_data['file_name']);
            $ext            = strtolower(end($file_name_ext));
            
            if(in_array($ext,$this->VideoExt)){
                $this->create_video_thumb($upload_data);
            }            
                       
            $image_type         = $ext;
            $MediaSectionID     = isset($this->MediaSectionIDArray[$type]) ? $this->MediaSectionIDArray[$type] : 1;
            $DeviceID           = $this->DeviceTypeID;
            if(empty($DeviceID)) {
                $DeviceID           = isset($this->DeviceTypeIDArray[$DeviceType]) ? $this->DeviceTypeIDArray[$DeviceType] : 1;
            }

            $MediaExtensionID   = isset($this->MediaExtensionIDArray[$image_type]) ? $this->MediaExtensionIDArray[$image_type] : 1;
            $totalSize = $upload_data['file_size']+$totalSize;
            
            $source_id = ($this->SourceID) ? $this->SourceID : 1 ;
            $Media = array(
                'MediaGUID'         => get_guid(),
                'UserID'            => $this->UserID,
                'OriginalName'      => $upload_data['orig_name'],
                'MediaSectionID'    => $MediaSectionID,
                'ModuleID'          => $module_id,
                'ModuleEntityID'    => $entity_id,
                'ImageName'         => $file_name,
                'ImageUrl'          => $file_name,
                'Size'              => $totalSize, //The file size in kilobytes
                'DeviceID'          => $DeviceID,
                'SourceID'          => $source_id,
                'MediaExtensionID'  => $MediaExtensionID,
                'MediaSectionReferenceID'=>0,
                'CreatedDate'       => get_current_date('%Y-%m-%d %H:%i:%s'),
                'StatusID'          => 1, // default pending
                'IsAdminApproved'   => 1,
                'AbuseCount'        => 0,
                'AlbumID'           => 0,
                'Caption'           => "" ,
                'MediaSizeID'       => $this->getMediaSizeID($totalSize),
                'AddedBy' => $is_added_from_frontend,
                'Resolution'           => $upload_data['image_width'].'x'.$upload_data['image_height']
            ); 

            $this->save_media($Media,$this->UserID);
            //insert in to media table with status pending which means in 24 hours this file will be deleted from server.
            //MediaGUID, MediaName
            $Return['Data']=array(
                "MediaGUID"=>$Media['MediaGUID'],
                "ImageName"=>$Media['ImageName'],
                "ImageServerPath"=>IMAGE_SERVER_PATH.PATH_IMG_UPLOAD_FOLDER.$type,
                "OriginalName"=>$upload_data['orig_name'],
                "MediaType"=>'PHOTO',
                "MediaExtension"=>$ext,
                "MediaSectionID"=>$MediaSectionID,
                "MediaSectionAlias"=>($MediaSectionID==4?'album':''),
                "TotalSize" =>$totalSize,
                'full_path' => $upload_data['full_path'],
                'Resolution'           => $upload_data['image_width'].'x'.$upload_data['image_height']
            );
                        
        }
        return $Return;
    }

    /**
     * [uploadImage Used to upload file]
     * @param [type] $Data [input data for upload file Request]
     * @return [Object] [json object]
     */
    function uploadImageInBg($data) {
        //log_message("error", "uploadImageInBg");
        if(!empty($data)) {
            $Type       = $data['Type'];
            $file_name   = $data['ImageName'];
            $media_guid  = $data['MediaGUID'];
            $Size       = $data['TotalSize'];
            $media_section_id  = isset($data['MediaSectionID']) ? $data['MediaSectionID'] : 0;

            $type       = strtolower($Type);
            
            $thumb_arr  = $type . '_thumb_size';
            $zc_arr     = $type . '_zoom_crop_array';
            //Copy original image and reduce its quality to 75%

            $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
            
            $full_path = isset($data['full_path']) ? $data['full_path'] : '';
            $document_root = isset($data['document_root']) ? $data['document_root'] : '';           
            
            $totalSize = $this->generate_thumb(
                $full_path, 
                $dir_name, 
                $file_name, 
                $this->$thumb_arr, 
                $this->$zc_arr,
                1,
                $this->pathToFolder,
                $type,
                0,
                $document_root    
            );
            
            
            $totalSize = $Size+$totalSize;
            
            if(!empty($media_guid) && $totalSize) {
                $update_media =  array('Size' => $totalSize,'MediaSizeID'=>$this->getMediaSizeID($totalSize));
                if(!in_array($media_section_id, array(3,4,6,7,9,14,15))) {
                    $update_media['StatusID'] = 2;
                }
                $this->db->where('MediaGUID',$media_guid);
                $this->db->update(MEDIA,$update_media);
            }
            
            $file_name = 'org_'.$file_name; 
            if (strtolower(IMAGE_SERVER) == 'remote') {
            
                $s3_path        = $dir_name .'/' . $file_name;
                $s3             = $s3 = new S3($this->s3_credential);
                $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);
                
                if(!empty($data['keep_original']) && $upload_flag) {
                    @unlink($s3_path);
                }                
            }
        }
    }

    function get_media_section_album_id($media_section_type, $user_id, $module_id, $module_entity_id, $extension_id="")
    {
        $album_name = "";
        $album_id   = 0;
        if($media_section_type == "profile") 
        {
            $album_name = DEFAULT_PROFILE_ALBUM;
        }
        if($media_section_type == "profilebanner") 
        {
            $album_name = DEFAULT_PROFILECOVER_ALBUM;
        }

        
        if(!empty($album_name) && !empty($module_id) && !empty($module_entity_id))
        {
            $album_id = get_album_id($user_id, $album_name, $module_id, $module_entity_id);   
        }
        return $album_id; 
    }
    /**
     * [saveFileFromUrl Used to upload file from URL]
     * @param [type] $Data [input data for upload file Request]
     * @return [Object] [json object]
     */
    function saveFileFromUrl($Data) 
    {
        $Return['Message']      = lang('success');
        $Return['ResponseCode'] = 200;
        $Return['Data']         =array();

        $FileName = substr(md5(uniqid(mt_rand(), true)), 0, 8) . time().'.jpg';
            
        $this->load->library('curl');
        $this->load->helper('file');

        $type       = strtolower($Data['Type']);
        $DeviceType = strtolower($Data['DeviceType']);
        $ImageData  = $Data['ImageData'];
        $module_id   = isset($Data['ModuleID']) ? $Data['ModuleID'] : 0 ;
        $entity_guid = isset($Data['ModuleEntityGUID']) ? $Data['ModuleEntityGUID'] : 0 ;
        $entity_id = 0;
        if($entity_guid) {
            $entity_id   = get_detail_by_guid($entity_guid, $module_id);
        }
        
        
        $folder_arr = $type . '_folder';
        $thumb_arr  = $type . '_thumb_size';
        $zc_arr     = $type . '_zoom_crop_array';

        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
        $chk_folder = PATH_IMG_UPLOAD_FOLDER;

        $this->check_folder_exist($chk_folder, $this->$folder_arr);
        $totalSize = 0;
        $Resolution = '';
        if(isset($Data['CanCrop']))
        {
            $Resolution = $Data['ImageWidth'].'x'.$Data['ImageHeight'];
            $FilePath = IMAGE_ROOT_PATH.$type.'/';
            if(isset($Data['ImageUrl']) && !$Data['ImageUrl']){
                @file_put_contents($FilePath.$FileName,base64_decode($ImageData));
            } else {
                write_file($dir_name . "/". $FileName, $ImageData);
            }
            $size = @filesize($FilePath.$FileName);
            if(isset($size) && !empty($size)) {
                $totalSize = $totalSize + ($size); //1024

            }
            if($this->$thumb_arr)
            {
                foreach($this->$thumb_arr as $folder)
                {
                    $w = $folder[0];
                    $h = $folder[1];
                    if($w==1200 && $h==300)
                    {
                        $totalSize = $totalSize + $this->cropFile($FilePath, $FileName, $Data['ImageWidth'], $Data['ImageHeight'], 1920, $Data['CropYAxis'], $folder[0].'x'.$folder[1].'/', $dir_name, $Data['CropXAxis']);
                    }
                    else
                    {
                        $totalSize = $totalSize + $this->create_cover_thumb($FileName,$w,$h);
                    }
                }
            }
            
            if (strtolower(IMAGE_SERVER) == 'remote') {
                $s3_path = $dir_name . "/". $FileName;
                $s3 = new S3($this->s3_credential);
                $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);
                if($upload_flag) {
                    @unlink($s3_path);
                }
            }
        
        } else {
            
            write_file($dir_name . "/". $FileName, $ImageData);
            
            rename(
                $dir_name . "/". $FileName,
                $dir_name.'/'.'org_'.$FileName
            );
            
            
            list($width, $height) = @getimagesize($dir_name . "/org_". $FileName);
            $Resolution = $width.'x'.$height;
            
            $this->generate_thumb($dir_name . "/org_". $FileName, $dir_name, $FileName, array(array($width,$height)), $this->$zc_arr, 1,$this->pathToFolder,$type,1);

            //thumb code
            $totalSize = $this->generate_thumb(
                                    $dir_name . "/org_". $FileName, 
                                    $dir_name, 
                                    $FileName, 
                                    $this->$thumb_arr, 
                                    $this->$zc_arr,
                                    1,
                                    $this->pathToFolder,
                                    $type
                                );
            
            if (strtolower(IMAGE_SERVER) == 'remote') {
                $s3_path = $dir_name . "/org_". $FileName;
                $s3 = new S3($this->s3_credential);
                $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);
                if($upload_flag) {
                    @unlink($s3_path);
                }
            }
        }

        
        $MediaSectionID     = isset($this->MediaSectionIDArray[$type]) ? $this->MediaSectionIDArray[$type] : 1;
        $DeviceID           = isset($this->DeviceTypeID) ? $this->DeviceTypeID : '';
        if(empty($DeviceID)) {
            $DeviceID           = isset($this->DeviceTypeIDArray[$DeviceType]) ? $this->DeviceTypeIDArray[$DeviceType] : 1;
        }
        if(isset($Data['SourceID'])) {
            $this->SourceID = $Data['SourceID'];
        }
        if(isset($Data['UserID'])) {
            $this->UserID = $Data['UserID'];
        }
        $MediaExtensionID   = 1;

        $source_id = ($this->SourceID) ? $this->SourceID : 1 ;

        $Media = array(
            'MediaGUID'         => get_guid(),
            'UserID'            => $this->UserID,
            'MediaSectionID'    => $MediaSectionID,
            'ModuleID'          => $module_id,
            'ModuleEntityID'    => $entity_id,
            'ImageName'         => $FileName,
            'ImageUrl'          => $FileName,
            'Size'              => $totalSize, //The file size in kilobytes
            'DeviceID'          => $DeviceID,
            'SourceID'          => $source_id,
            'MediaExtensionID'  => $MediaExtensionID,
            'MediaSectionReferenceID'=>0,
            'CreatedDate'       => get_current_date('%Y-%m-%d %H:%i:%s'),
            'StatusID'          => 1, // default pending
            'IsAdminApproved'   => 1,
            'AbuseCount'        => 0,
            'AlbumID'           => 0,
            'Caption'           => "",
            'MediaSizeID'       => $this->getMediaSizeID($totalSize),
            'Resolution'           => $Resolution
        ); 
        $MediaID = $this->save_media($Media,$this->UserID);
        //insert in to media table with status pending which means in 24 hours this file will be deleted from server.
        //MediaGUID, MediaName
        $Return['Data']=array(
            "MediaID"   => $MediaID,
            "MediaGUID" => $Media['MediaGUID'],
            "ImageName" => $Media['ImageName'],
            "ImageServerPath"=>IMAGE_SERVER_PATH.PATH_IMG_UPLOAD_FOLDER.$type
        );
        
        return $Return;
    }

    function apply_default_theme($data)
    {
        $return['Message']      = lang('success');
        $return['ResponseCode'] = 200;
        $return['Data']         =array();

        $file_name = substr(md5(uniqid(mt_rand(), true)), 0, 8) . time().'.jpg';

        $cover_theme   = $data['CoverTheme'];
        $image_path     = ROOT_PATH."/assets/img/bannerTheme/".$cover_theme.".jpg"; 
            
        $this->load->helper('file');

        $type        = strtolower($data['Type']);
        $device_type = strtolower($data['DeviceType']);
        $module_id   = $data['ModuleID'];
        $entity_guid = $data['ModuleEntityGUID'];
        $entity_id   = get_detail_by_guid($entity_guid, $module_id);
        
        $folder_arr = $type . '_folder';
        $thumb_arr  = $type . '_thumb_size';
        $zc_arr     = $type . '_zoom_crop_array';

        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
        $chk_folder = PATH_IMG_UPLOAD_FOLDER;

        $this->check_folder_exist($chk_folder, $this->$folder_arr);
        $total_size = 0;

        $file_full_path = $dir_name.'/'.$file_name;


        copy($image_path, $file_full_path);
        copy(ROOT_PATH.'/'.$file_full_path, $dir_name . "/org_". $file_name);

        $size = @filesize($file_full_path);
        if(isset($size) && !empty($size)) 
        {
            $total_size = $total_size + ($size); //1024

            $total_size = 2 * $total_size; //for org image
        }
        
        if (strtolower(IMAGE_SERVER) == 'remote') 
        {
            $s3 = new S3($this->s3_credential);
            $s3_path = $dir_name . "/". $file_name;            
            $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);

            $s3_path = $dir_name . "/org_". $file_name;
            $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);
        }

        if($this->$thumb_arr)
        {
            foreach($this->$thumb_arr as $folder)
            {
                $w = $folder[0];
                $h = $folder[1];
                if($w==1200 && $h==300)
                {                    
                    $destination_path = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                    if (strtolower(IMAGE_SERVER) == 'remote') 
                    {
                        $res = $s3->copyObject(BUCKET, $file_full_path, BUCKET, $destination_path, S3::ACL_PUBLIC_READ);
                        $total_size = $total_size + ($size);                        
                    }
                    else
                    {
                        copy(ROOT_PATH.'/'.$file_full_path, $destination_path);
                        $total_size = $total_size + ($size);                        
                    }
                }
                else
                {
                    $image_path     = ROOT_PATH."/assets/img/bannerTheme/".$cover_theme.$cover_theme.".jpg";
                    $s3_path   = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                    copy($image_path, $s3_path);
                    if (strtolower(IMAGE_SERVER) == 'remote') 
                    {                      
                        $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);

                        $size1 = @filesize($image_path);
                        if(isset($size1) && !empty($size1)) {
                            $total_size = $total_size + $size1;
                        }
                        if($upload_flag) {
                            @unlink($s3_path);
                        } 
                    }
                }
            }
        }

        if (strtolower(IMAGE_SERVER) == 'remote') 
        {
            @unlink($file_full_path);
            @unlink($dir_name . "/org_". $file_name);
        }

        $media_section_id     = isset($this->MediaSectionIDArray[$type]) ? $this->MediaSectionIDArray[$type] : 1;
        $device_id           = isset($this->DeviceTypeID) ? $this->DeviceTypeID : '';
        if(empty($device_id)) {
            $device_id           = isset($this->DeviceTypeIDArray[$device_type]) ? $this->DeviceTypeIDArray[$device_type] : 1;
        }
        if(isset($Data['SourceID'])) {
            $this->SourceID = $Data['SourceID'];
        }
        if(isset($Data['UserID'])) {
            $this->UserID = $Data['UserID'];
        }
        $media_extension_id   = 1;

        $source_id = ($this->SourceID) ? $this->SourceID : 1 ;

        $media = array(
            'MediaGUID'         => get_guid(),
            'UserID'            => $this->UserID,
            'MediaSectionID'    => $media_section_id,
            'ModuleID'          => $module_id,
            'ModuleEntityID'    => $entity_id,
            'ImageName'         => $file_name,
            'ImageUrl'          => $file_name,
            'Size'              => $total_size, //The file size in kilobytes
            'DeviceID'          => $device_id,
            'SourceID'          => $source_id,
            'MediaExtensionID'  => $media_extension_id,
            'MediaSectionReferenceID'=>0,
            'CreatedDate'       => get_current_date('%Y-%m-%d %H:%i:%s'),
            'StatusID'          => 1, // default pending
            'IsAdminApproved'   => 1,
            'AbuseCount'        => 0,
            'AlbumID'           => 0,
            'Caption'           => "",
            'MediaSizeID'       => $this->getMediaSizeID($total_size)                    
        ); 
        $this->save_media($media, $this->UserID);
        //insert in to media table with status pending which means in 24 hours this file will be deleted from server.
        //MediaGUID, MediaName

        $updateData = array("MediaGUID" => $media['MediaGUID'], "ModuleID" => $module_id, "ModuleEntityGUID" => $entity_guid, 'ImageName' => $media['ImageName']);

        $return['Data']['ProfileCover'] = $this->updateProfileCover($updateData,$this->UserID);
        if($module_id==3){
            if (CACHE_ENABLE) 
            {
                $this->cache->delete('user_profile_'.$this->UserID);
            }
        }
        return $return;        
    }
    /**
     * Function Name : cropFile
     * Descript : This function will crop uploaded file
     */
    public function cropFile($FilePath,$FileName,$width,$height,$originalWidth=1920,$cropAxis=0,$resizePath,$dir_name='uploads',$axis_x=0,$CanResize=1,$CropHeight=300,$CropWidth=false,$originalHeight='auto',$r_height=0,$r_width=0){
        
        $totalSize = 0;
        $this->load->library('image_lib');
        if($CanResize=='1' || $CanResize=='3'){
            $config['new_image']      = $FilePath.$resizePath;
            $config['image_library']  = 'gd2';
            $config['source_image']   = $FilePath.$FileName;
            $config['create_thumb']   = TRUE;
            $config['thumb_marker']   = '';
            $config['maintain_ratio'] = TRUE;
            $config['width']          = $originalWidth;
            $config['height']         = $height;
            $config['overwrite']       = TRUE;
            $this->image_lib->initialize($config); 
            $this->image_lib->resize();
        }

        $config = array();

        if($originalWidth<320 || $originalHeight<320 && $CanResize=='2'){
            $lpath = DOCUMENT_ROOT.SUBDIR.'/'.$FilePath.$width.'x'.$height.'/'.$FileName;
            copy($this->image_server_path.$FilePath.$FileName,$lpath);
            $size = @filesize($lpath);
            if(isset($size) && !empty($size)) {
                $totalSize = $size;

            }
            return $totalSize;
        }

        $config['new_image']      = $FilePath.$resizePath;
        $config['image_library']  = 'gd2';
        if($CanResize=='1' || $CanResize=='3'){
            $config['source_image']   = $FilePath.$resizePath.$FileName;
        } else {
            $config['source_image']   = $FilePath.'/'.$FileName;   
        }
        $config['create_thumb']   = TRUE;
        $config['thumb_marker']   = '';
        $config['maintain_ratio'] = FALSE;
        if($CropHeight){
            $config['height']         = $CropHeight;
        }
        if($CropWidth){
            $config['width']         = $CropWidth;
        }
        $config['x_axis']         = $axis_x;
        $config['y_axis']         = $cropAxis;
        $this->image_lib->initialize($config); 
        if(!$this->image_lib->crop()){
            echo $this->image_lib->display_errors();
        }
        $this->image_lib->clear();

        if($CanResize == 1){
            $totalSize = $totalSize + $this->resizeImage($FilePath.ADMIN_THUMB_HEIGHT.'x'.ADMIN_THUMB_WIDTH.'/'.$FileName,$FilePath.$resizePath.$FileName,ADMIN_THUMB_HEIGHT,ADMIN_THUMB_WIDTH,FALSE);
        }

        $s3_path = $dir_name . "/".$resizePath.$FileName;
        if (strtolower(IMAGE_SERVER) == 'remote') {
            $s3 = new S3($this->s3_credential);
            $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);
        }
        $local_path = $FilePath.$resizePath.$FileName;
        $size = @filesize($local_path);
        if(isset($size) && !empty($size)) {
            $totalSize = $totalSize + ($size);

        }   
        if($CanResize == 2){
            sleep(1);
            $this->resizeImage($FilePath.$resizePath,$FilePath.$resizePath.$FileName,$height,$width);
        }

        if($CanResize == 3){
            sleep(1);
            $this->resizeImage($FilePath.$FileName,$FilePath.$resizePath.$FileName,$r_height,$r_width,FALSE);
        }

        return $totalSize;
    }

    public function cropProfilePicture($Data, $CanResize=2, $cropContainerWidth=320, $cropContainerHeight=320) {

        $Return['Message']      = lang('success');
        $Return['ResponseCode'] = 200;
        $Return['Data']         =array();

        $type               = strtolower($Data['Type']);
        

        $ProfilePicture     = $Data['ProfilePicture'];
        $MediaGUID          = $Data['MediaGUID'];

        /*$DeviceType         = strtolower($Data['DeviceType']);
        $module_id           = $Data['ModuleID'];
        $entity_guid         = $Data['ModuleEntityGUID'];
        $entity_id           = get_detail_by_guid($entity_guid, $module_id);*/

        $Top                = $Data['Top'];
        $Left               = $Data['Left'];

        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
        $chk_folder = PATH_IMG_UPLOAD_FOLDER;

        $folder_arr = $type . '_folder';
        $thumb_arr  = $type . '_thumb_size';
        $this->check_folder_exist($chk_folder, $this->$folder_arr);

        $FileDetails        = getimagesize($this->image_server_path.$dir_name.'/'.$ProfilePicture);       
        
        $originalWidth      = $FileDetails[0];
        $originalHeight     = $FileDetails[1];
        
        $Left = $Left*-1;
        $Top = $Top*-1;

        $FilePath = $dir_name.'/';          
        
        $totalSize = 0;
        foreach ($this->$thumb_arr as $i => $row) {
            $w = $row[0];
            $h = $row[1];
            $totalSize = $totalSize + $this->cropFile($FilePath, $ProfilePicture, $w, $h, $originalWidth, $Top, $w.'x'.$h.'/', $dir_name, $Left, $CanResize,  $cropContainerWidth, $cropContainerHeight, $originalHeight);                
        }
        $this->db->set('Size','Size+'.$totalSize,FALSE);
        $this->db->where('MediaGUID',$MediaGUID);
        $this->db->update(MEDIA);
    }

    function resizeImage($NewImage,$SourceImage,$height,$width,$MaintainRatio=TRUE){
        $this->load->library('image_lib');
        $config['new_image']      = $NewImage;
        $config['image_library']  = 'gd2';
        $config['source_image']   = $SourceImage;
        $config['create_thumb']   = TRUE;
        $config['thumb_marker']   = '';
        $config['maintain_ratio'] = $MaintainRatio;
        $config['width']          = $height;
        $config['height']         = $width;
        $config['overwrite']       = TRUE;
        $this->image_lib->initialize($config); 
        $this->image_lib->resize();
        $this->image_lib->clear();
        
        $totalSize = 0;
        $size = @filesize($NewImage);
        if(isset($size) && !empty($size)) {
            $totalSize = $size;
        }
        if (strtolower(IMAGE_SERVER) == 'remote') {
            $s3_path = $NewImage;
            $s3 = new S3($this->s3_credential);
            $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);
            if($NewImage !== $SourceImage && $upload_flag)
            {
                @unlink($s3_path);    
            }            
        }

        return $totalSize; //size in bytes
    }

    /**
     * @Summary: check if folder exists otherwise create new
     * @create_date: 3 apr, 2013
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    /**
     * [check_folder_exist check if folder exists otherwise create new]
     * @param  [string] $dir_name [Directroy name]
     * @param  array  $folder   [folder array]
     */
    public function check_folder_exist($dir_name, $folder = array()) {
        $d = ROOT_PATH . '/' . PATH_IMG_UPLOAD_FOLDER;
        if (!is_dir($d))
            mkdir($d, 0777);
        $d1 = ROOT_PATH . '/' . $dir_name;
        if (!is_dir($dir_name))
            mkdir($dir_name, 0777);
        if(is_array($folder)) {
            foreach ($folder as $row) {
                if (!is_dir($dir_name . $row))
                    mkdir($dir_name . $row, 0777);
            }
        }
    }

    /**
     * [generate_thumb used to generate thumb]
     * @param  [string]  $temp_file      [Uploaded file temp path (full_path)]
     * @param  [string]  $dir_name       [Directroy name]
     * @param  [string]  $file_name      [Uploaded file name]
     * @param  array   $thumb          [Thumb Size array]
     * @param  array   $zc             [Array of zoom cropping option]
     * @param  integer $using_phpthumb [Use phpthumb library to generate thumb or not]
     * @param  string  $pathToFolder   [Upload folder path]
     * @param  string  $type           [section type on which file being uploading]
     */
    public function generate_thumb($temp_file, $dir_name, $file_name, $thumb = array(), $zc = array(), $using_phpthumb = 0,$pathToFolder='',$type='',$is_original=0,$document_root='') {        
        $name_parts = pathinfo($file_name);
        $ext = strtolower($name_parts['extension']);
        $totalSize = 0;
        
        if(empty($document_root)) {
            $document_root = DOCUMENT_ROOT;
        }
       
        if($using_phpthumb==1)
        {
            $this->load->library('phpthumb');

            list($width, $height) = @getimagesize($temp_file);            
            
            $temp_file = file_get_contents($temp_file);
            
            $filePath = $document_root . $pathToFolder . '/' . $dir_name . '/org_' . $file_name;
            $thumb_path = '';
            $is_real = true;

            foreach ($thumb as $i => $row) 
            {
                $w = $row[0];
                $h = $row[1];

                if($dir_name=='')
                {
                    $s3_path = $file_name;
                    $local_path = $file_name;
                } 
                else 
                {
                    if($is_original)
                    {
                        $s3_path = $dir_name . '/' . $file_name;
                        $local_path = $document_root . $pathToFolder . '/' . $dir_name . '/' . $file_name;
                        $thumb_path = $local_path;
                        $is_real = true;
                    } 
                    else 
                    {
                        $s3_path = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                        $local_path = $document_root . $pathToFolder . '/' . $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                        $thumb_path = $document_root . $pathToFolder . '/' . $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                        $is_real = false;
                    }
                }              
              
                    
                    $phpThumb = new phpThumb();
                    $phpThumb->resetObject();
                    $phpThumb->setSourceData($temp_file);
                    $phpThumb->setParameter('ar','x');
                    if($ext == 'png')
                    {
                        $phpThumb->setParameter('f','png');
                    }
                    if (isset($w))
                    {
                        $phpThumb->setParameter('w', $w);
                    }

                    if (isset($h))
                    {
                        //$phpThumb->setParameter('h', $h);
                    }                

                    if ($zc[$i] == 1)
                    {
                        //$phpThumb->setParameter('zc', true);
                    }
                    $size = @filesize($local_path);
                    if(isset($size) && !empty($size)) {
                        $totalSize = $totalSize + ($size);
                    }

                    $phpThumb->setParameter('far', 1);                    
                    if ($phpThumb->GenerateThumbnail($filePath,$thumb_path,$h,$w,$is_real)) { // this line is VERY important, do not remove it!
                        if ($phpThumb->RenderToFile($local_path)) { //save file to destination
                            if (strtolower(IMAGE_SERVER) == 'remote') {
                                $s3 = new S3($this->s3_credential);
                                $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);

                                if($upload_flag) {
                                    @unlink($local_path);
                                }
                                
                            }
                        }
                    }                    
                                
            }
            // Generate thumb for original
            
        } else {
            $this->load->library('image_lib');
            foreach ($thumb as $row) {
                $w = $row[0];
                $h = $row[1];

                if($dir_name==''){
                    $s3_path = $file_name;
                    $local_path = $file_name;
                } else {
                    $s3_path = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                    $local_path = $document_root . $pathToFolder . '/' . $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                }
                $config = array();
                // create thumb
                $config['image_library'] = 'GD2';
                $config['source_image'] = $temp_file;
                $config['new_image'] = $s3_path;
                $config['create_thumb'] = false;
                $config['maintain_ratio'] = true;
                $config['width'] = $w;
                $config['height'] = $h;
                $this->image_lib->initialize($config);
                if ($this->image_lib->resize()) {
                    if (strtolower(IMAGE_SERVER) == 'remote') {
                        $s3 = new S3($this->s3_credential);
                        $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ);                        
                    }
                }
                $this->image_lib->clear();

                $size = @filesize($local_path);
                if(isset($size) && !empty($size)) {
                    $totalSize = $totalSize + ($size);

                }
            }
        }
        return $totalSize;
    
    }
    
    /**
     * [save_media used to insert media]
     * @param  [array] $media [Media details]
     * @return [Integer] [Media ID]
     */
    function save_media($media,$user_id=''){
        if($user_id==''){
            if($this->session->userdata('AdminUserID')) {
                $user_id = $this->session->userdata('AdminUserID');
                $media['UserID'] = $user_id;
            }
        }
        if(!$user_id){return;}
                
        $this->db->insert(MEDIA,$media);
        $media_id = $this->db->insert_id();
        if($user_id){
            $this->load->model(array('subscribe_model'));
            $this->subscribe_model->toggle_subscribe($user_id,'Media',$media_id); 
        }
        
        if ((extension_loaded("gearman") && JOBSERVER == "Gearman") || (extension_loaded('redis') && JOBSERVER == "Rabbitmq"))  {
            initiate_worker_job('update_media_analytics', $media, '', 'process_image');
        } else {
            $this->check_media_counts($media,true);
        }
        
        return $media_id;    
    }
    
    /**
     * Function Name : getFileSize
     * @param
     * Description returns size of file in bytes
     */    
    function getFileSize($url){
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }
    /**
     * [updateProfileCover used to update the profile cover image details for an entity]
     * @param  [array] $Data   [Image info]
     * @param  [int] $user_id [loggedin User ID]
     */
    function updateProfileCover($Data,$user_id){

        $module_id       = $Data['ModuleID'];
        $entity_guid     = $Data['ModuleEntityGUID'];
        $entity_id       = get_detail_by_guid($entity_guid, $module_id);
        $table_name     = USERS;
        $set_field      = "ProfileCover"; 
        $set_value      = $Data['ImageName'];
        $condition      = array("UserID" => $user_id);
        switch ($module_id) {  
            case '1':
                $table_name = GROUPS;
                $set_field  = "GroupCoverImage"; 
                $set_value  = $Data['ImageName'];
                $condition  = array("GroupID" => $entity_id);
                if (CACHE_ENABLE) 
                {
                    $this->cache->delete('group_cache_'.$entity_id);
                }
                break;
            case '3':
                $table_name = USERS;
                $set_field  = "ProfileCover"; 
                $set_value  = $Data['ImageName'];
                $condition  = array("UserID" => $entity_id);
                if (CACHE_ENABLE) 
                {
                    $this->cache->delete('user_profile_'.$entity_id);
                }
                break;    
            case '18':
                $table_name = PAGES;
                $set_field  = "CoverPicture"; 
                $set_value  = $Data['ImageName'];
                $condition  = array("PageID" => $entity_id);
                if (CACHE_ENABLE) 
                {
                    $this->cache->delete('page_'.$entity_id);
                }
                break;
            case '14':
                $table_name = EVENTS;
                $set_field  = "ProfileBannerID";
                $MediaID    = 0;
                $this->db->select("MediaID");
                $this->db->from(MEDIA);
                $this->db->where(array("MediaGUID" => $Data['MediaGUID']));
                $query = $this->db->get();
                if($query->num_rows()>0){
                    $result = $query->row_array();
                    $MediaID = $result['MediaID'];
                }
                $set_value  = $MediaID;
                $condition  = array("EventID" => $entity_id);
                break;      
            default:
                return FALSE;
                break;
        }

        $this->db->where($condition);
        $this->db->set($set_field, $set_value);       
        $this->db->update($table_name); 

        $param = array('MediaGUID'=>$Data['MediaGUID']);
        $this->load->model('activity/activity_model');
        $this->activity_model->addActivity($module_id, $entity_id, 24, $user_id, 0, '', 1, '', $param,'1');

        $Media[0]['MediaGUID'] = $Data['MediaGUID'];
        $Media[0]['Caption']   = '';
        $this->load->model('media/media_model');

        $AlbumID = get_album_id($user_id, DEFAULT_PROFILECOVER_ALBUM, $module_id, $entity_id);

        $this->media_model->updateMedia($Media, $entity_id, $user_id, $AlbumID);

        return IMAGE_SERVER_PATH.PATH_IMG_UPLOAD_FOLDER.'profilebanner'.THUMB_profilebanner.$Data['ImageName'];
    }
    /**
     * [updateProfilePicture USED to update profile image info for an entity]
     * @param  [string] $MediaGUID        [MediaGUID]
     * @param  [string] $ProfilePicture   [Profile Picture Name]
     * @param  [int] $user_id              [Logged in user ID]
     * @param  [int] $module_id            [ModuleID]
     * @param  [string] $ModuleEntityGUID [Module Entity GUID]
     */
    function updateProfilePicture($MediaGUID,$ProfilePicture,$user_id,$module_id,$ModuleEntityGUID,$from_admin=false){
        $entity_id = get_detail_by_guid($ModuleEntityGUID,$module_id);
        switch ($module_id) {  
            case '1':
                $table_name = GROUPS;
                $set_field  = "GroupImage"; 
                $set_value  = $ProfilePicture;
                $condition  = array("GroupID" => $entity_id);
                if (CACHE_ENABLE) 
                {
                    $this->cache->delete('group_cache_'.$entity_id);
                }
                break;
            case '3':
                $table_name = USERS;
                if($from_admin) {
                    $condition  = array("UserID" => $entity_id);
                    $set_field  = "AdminProfilePicture"; 
                    $set_value  = $ProfilePicture;
                    $set_field2  = "ProfilePicture"; 
                    $set_value2  = $ProfilePicture;
                    if (CACHE_ENABLE) {
                        $this->cache->delete('user_profile_'.$entity_id);
                    }
                } else {                    
                    $condition  = array("UserID" => $entity_id);
                    $set_field  = "ProfilePicture"; 
                    $set_value  = $ProfilePicture;
                    $set_field2  = "AdminProfilePicture"; 
                    $set_value2  = $ProfilePicture;
                    $this->session->set_userdata('ProfilePicture',$ProfilePicture);
                    
                    if (CACHE_ENABLE) {
                        $this->cache->delete('user_profile_'.$entity_id);
                    }
                }
                $this->delete_mongo_db_record('active_user_login', array("UserID" => $entity_id));
                break;    
            case '18':
                $table_name = PAGES;
                $set_field  = "ProfilePicture"; 
                $set_value  = $ProfilePicture;
                $condition  = array("PageID" => $entity_id);
                if (CACHE_ENABLE) 
                {
                    $this->cache->delete('page_'.$entity_id);
                }
                break;
            case '14':
                $table_name = EVENTS;
                $set_field  = "ProfileImageID";
                $MediaID    = 0;
                $this->db->select("MediaID");
                $this->db->from(MEDIA);
                $this->db->where(array("MediaGUID" => $MediaGUID));
                $query = $this->db->get();
                if($query->num_rows()>0){
                    $result = $query->row_array();
                    $MediaID = $result['MediaID'];
                }
                $set_value  = $MediaID;
                $condition  = array("EventID" => $entity_id);
                break;      
            default:
                return FALSE;
                break;
        }


        $this->db->where($condition);
        $this->db->set($set_field, $set_value); 
        if($module_id == 3 && isset($set_field2, $set_value2))
        {
            $this->db->set($set_field2, $set_value2); 
        }
        $this->db->update($table_name); 
        
        $param = array('MediaGUID'=>$MediaGUID);

        $visibility = $this->get_profile_picture_visibility($user_id);

        if($module_id == '1' || $module_id == '14' || $module_id == '18')
        {
            $visibility = '1';
        }

        if(!$from_admin)
        {
            $this->load->model('activity/activity_model');
            $this->activity_model->addActivity($module_id, $entity_id, 23, $user_id, 0, '', 1, $visibility, $param,'1'); 
        }

        $Media[0]['MediaGUID'] = $MediaGUID;
        $Media[0]['Caption']   = '';
        $this->load->model('media/media_model');

        $AlbumID = get_album_id($user_id, DEFAULT_PROFILE_ALBUM, $module_id, $entity_id);
        $this->media_model->updateMedia($Media, $entity_id, $user_id, $AlbumID);
        
        return $ProfilePicture;
    }


    function get_profile_picture_visibility($user_id)
    {
        $this->db->select('Value');
        $this->db->from(USERPRIVACY);
        $this->db->where('PrivacyLabelKey','view_profile_picture');
        $this->db->where('UserID',$user_id);
        $query = $this->db->get();
        if($query->num_rows())
        {
            $value = $query->row()->Value;
            if($value == 'everyone')
            {
                return 1;
            }
            elseif($value == 'network')
            {
                return 2;
            }
            elseif($value == 'friend')
            {
                return 3;
            }
            elseif($value == 'self')
            {
                return 4;
            }
        }
    }

    // Return MEdia Size ID
    function getMediaSizeID($size){
        $size = $size/1024; //convert byte into KB
        $query = $this->db->query("SELECT MediaSizeID FROM MediaSizes WHERE ".$this->db->escape($size)." BETWEEN MinSize and MaxSize");
        if($query->num_rows()){
            return $query->row()->MediaSizeID;
        } else {
            return '1';
        }
    }
    
    function getExtID($ext){
        return  isset($this->MediaExtensionIDArray[$ext]) ? $this->MediaExtensionIDArray[$ext] : 1;
            
    }

    public function removeProfileCover($user_id,$module_id,$ModuleEntityGUID){
       $entity_id = get_detail_by_guid($ModuleEntityGUID,$module_id);
       switch ($module_id) {  
            case '1':
                $table_name = GROUPS;
                $set_field  = "GroupCoverImage"; 
                $set_value  = "";
                $condition  = array("GroupID" => $entity_id);
                break;
            case '3':
                $table_name = USERS;
                $set_field  = "ProfileCover"; 
                $set_value  = "";
                $condition  = array("UserID" => $entity_id);
                if (CACHE_ENABLE) 
                {
                    $this->cache->delete('user_profile_'.$entity_id);
                }
                break;    
            case '18':
                $table_name = PAGES;
                $set_field  = "CoverPicture"; 
                $set_value  = "";
                $condition  = array("PageID" => $entity_id);
                break;
            case '14':
                $table_name = EVENTS;
                $set_field  = "ProfileBannerID";
                $set_value  = 0;
                $condition  = array("EventID" => $entity_id);
                break;      
            default:
                return FALSE;
                break;
        }

        $this->db->where($condition);
        $this->db->set($set_field, $set_value);       
        $this->db->update($table_name); 
        return get_profile_cover('');
    }

    /**
     * [remove_profile_picture remove current profile picture]
     * @param  [type] $user_id           [User ID]
     * @param  [type] $module_id         [Module ID]
     * @param  [type] $module_entity_guid [Module Entity GUID]
     * @return [type]                   [description]
     */
    function remove_profile_picture($user_id, $module_id, $module_entity_guid){

       $entity_id = get_detail_by_guid($module_entity_guid,$module_id);
       switch ($module_id) {  
            case '1':
                $table_name = GROUPS;
                $set_field  = "GroupImage"; 
                $set_value  = "";
                $condition  = array("GroupID" => $entity_id);
                break;
            case '3':
                $table_name = USERS;
                $set_field  = "ProfilePicture"; 
                $set_value  = "";
                $condition  = array("UserID" => $entity_id);
                if (CACHE_ENABLE) 
                {
                $this->cache->delete('user_profile_'.$user_id);
                }
                break;    
            case '18':
                $table_name = PAGES;
                $set_field  = "ProfilePicture"; 
                $set_value  = "";
                $condition  = array("PageID" => $entity_id);
                break;
            case '14':
                $table_name = EVENTS;
                $set_field  = "ProfileImageID";
                $set_value  = 0;
                $condition  = array("EventID" => $entity_id);
                $compare    = "MediaID";
                break;      
            default:
                return FALSE;
                break;
        }

        $this->db->where($condition);
        $this->db->set($set_field, $set_value);    
        if($module_id == 3) {
            $this->db->set('AdminProfilePicture', $set_value);
        }   
        $this->db->update($table_name); 
        return '';
    }
    /**
     * [delete_media_file used to delete media files and all its thumbnail]
     * @param  [string] $file_name [file name]
     * @param  [string] $type      [section type like profile, profile banner]
     */
    function delete_media_file($file_name, $type) {
        
        $thumb_arr  = $type . '_thumb_size';
        $dir_name   = IMAGE_ROOT_PATH . $type;
    
        if(!empty($file_name) && file_exists($dir_name.'/'.$file_name)) {
           
            unlink($dir_name.'/'.$file_name);

            foreach ($this->$thumb_arr as $i => $row) {
                $w = $row[0];
                $h = $row[1];
                $thumb_file_name = $w.'x'.$h.'/'.$file_name;
                if(file_exists($dir_name.'/'.$thumb_file_name)) {
                    unlink($dir_name.'/'.$thumb_file_name);
                }
            }
        }
    }

    /**
     * [delete_media description]
     * @param  [int]  $MediaID [Media ID]
     * @param  boolean $Flag    [false means hard delete, true means soft delete]
     */
    function delete_media($MediaID, $Flag=false) {
        $this->db->select('M.ImageName', FALSE);
        $this->db->select('M.StatusID', FALSE);
        $this->db->select('M.MediaSectionID', FALSE);
        $this->db->select('MS.MediaSectionAlias', FALSE);

        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID', 'right');
        $this->db->from(MEDIA . " AS M");
        $this->db->where('M.MediaID', $MediaID);
        $query = $this->db->get();        
        if($query->num_rows()) {
            $row                = $query->row();
            $ImageName     = $row->ImageName;
            $MediaSectionAlias  = $row->MediaSectionAlias;
            $this->db->where('MediaID',$MediaID);
            if($Flag) {
                $this->db->set('StatusID', '3',FALSE);
                $this->db->update(MEDIA);
            } else {
                $this->db->delete(MEDIA);
                if(!empty($ImageName) && !empty($MediaSectionAlias)) {
                    $this->delete_media_file($ImageName, $MediaSectionAlias);
                }                      
            }
        }
    }

    /**
     * Function Name: check_media_counts
     * @param $param_array[],update_flag
     * Description: check and update media analytics count
     */
    function check_media_counts($param_array = array(), $update_flag = false) {
        $where = 'StatusID != 3';

        $select_from_array = array();
        $where_from_array = array();
        
        // Escape input data
        foreach ($param_array as $key => $val) {
            $param_array[$key] = $this->db->escape_str($val);
        }

        $select = 'COUNT(CASE WHEN IsAdminApproved = 1 then 1 ELSE NULL END) as admin_approved, COUNT(CASE WHEN IsAdminApproved = 0 then 1 ELSE NULL END) as admin_yet_to_approve';

        if (isset($param_array['DeviceID'])) {
            $select_from_array['selectForDeviceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND DeviceID=' . $param_array['DeviceID'] . ') then 1 ELSE NULL END) as admin_approved_for_device, COUNT(CASE WHEN (IsAdminApproved = 0 AND DeviceID=' . $param_array['DeviceID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_device';
            $where_from_array['DeviceID'] = $param_array['DeviceID'];

            /* filter by user */
            if (isset($param_array['UserID'])) {
                $select_from_array['filterForDeviceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND DeviceID=' . $param_array['DeviceID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_device, COUNT(CASE WHEN (IsAdminApproved = 0 AND DeviceID=' . $param_array['DeviceID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_device';
            }
            /* end filter by user */
        }

        if (isset($param_array['SourceID'])) {
            $select_from_array['selectForSourceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND SourceID=' . $param_array['SourceID'] . ') then 1 ELSE NULL END) as admin_approved_for_source, COUNT(CASE WHEN (IsAdminApproved = 0 AND SourceID=' . $param_array['SourceID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_source';
            $where_from_array['SourceID'] = $param_array['SourceID'];

            /* filter by user */
            if (isset($param_array['UserID'])) {
                $select_from_array['filterForSourceID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND SourceID=' . $param_array['SourceID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_source, COUNT(CASE WHEN (IsAdminApproved = 0 AND SourceID=' . $param_array['SourceID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_source';
            }
            /* end filter by user */
        }

        if (isset($param_array['MediaExtensionID'])) {
            $select_from_array['selectForMediaExtensionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaExtensionID=' . $param_array['MediaExtensionID'] . ') then 1 ELSE NULL END) as admin_approved_for_mediaext, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaExtensionID=' . $param_array['MediaExtensionID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_mediaext';
            $where_from_array['MediaExtensionID'] = $param_array['MediaExtensionID'];

            /* filter by user */
            if (isset($param_array['UserID'])) {
                $select_from_array['filterForMediaExtensionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaExtensionID=' . $param_array['MediaExtensionID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_mediaext, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaExtensionID=' . $param_array['MediaExtensionID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_mediaext';
            }
            /* end filter by user */
        }

        if (isset($param_array['MediaSectionID'])) {
            $select_from_array['selectForMediaSectionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSectionID=' . $param_array['MediaSectionID'] . ') then 1 ELSE NULL END) as admin_approved_for_media_sec_ref, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSectionID=' . $param_array['MediaSectionID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_media_sec_ref';
            $where_from_array['MediaSectionID'] = $param_array['MediaSectionID'];

            /* filter by user */
            if (isset($param_array['UserID'])) {
                $select_from_array['filterForMediaSectionID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSectionID=' . $param_array['MediaSectionID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_media_sec_ref, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSectionID=' . $param_array['MediaSectionID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_media_sec_ref';
            }
            /* end filter by user */
        }

        if (isset($param_array['MediaSizeID'])) {
            $select_from_array['selectForMediaSizeID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSizeID=' . $param_array['MediaSizeID'] . ') then 1 ELSE NULL END) as admin_approved_for_media_size, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSizeID=' . $param_array['MediaSizeID'] . ') then 1 ELSE NULL END) as admin_yet_to_approve_for_media_size';
            $where_from_array['MediaSizeID'] = $param_array['MediaSizeID'];

            /* filter by user */
            if (isset($param_array['UserID'])) {
                $select_from_array['filterForMediaSizeID'] = 'COUNT(CASE WHEN (IsAdminApproved = 1 AND MediaSizeID=' . $param_array['MediaSizeID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_approved_for_media_size, COUNT(CASE WHEN (IsAdminApproved = 0 AND MediaSizeID=' . $param_array['MediaSizeID'] . ' AND UserID=' . $param_array['UserID'] . ') then 1 ELSE NULL END) as filter_admin_yet_to_approve_for_media_size';
            }
            /* end filter by user */
        }


        if (!empty($select_from_array)) {
            $select = '';
            foreach ($select_from_array as $key => $val) {
                if ($select != '')
                    $select .= ', ';

                $select .= $val;
            }

            if (!empty($where_from_array)) {
                $temp_where = '';
                foreach ($where_from_array as $key => $val) {
                    if ($temp_where != '')
                        $temp_where .= ' OR ';

                    $temp_where .= $key . '=' . $val;
                }
                $where .= ' AND (' . $temp_where . ')';
            }
        }

        $this->db->select($select);
        $this->db->from(MEDIA);
        if (!empty($where)) {
            $this->db->_protect_identifiers = FALSE;
            $this->db->where($where);
            $this->db->_protect_identifiers = TRUE;
        }

        $query = $this->db->get();
        $dataArray = $query->row_array();
        /* update media count */
        if ($update_flag) {
            foreach ($param_array as $key => $val) {
                if ($key == 'DeviceID') {/* update MediaDeviceCounts */
                    $insert_update_array = array(
                        'table' => MEDIADEVICECOUNTS,
                        'where' => array('colName' => 'DeviceTypeID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_device'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_device'])
                    );
                    $this->insert_update($insert_update_array);
                } elseif ($key == 'SourceID') {/* update MediaSourceCount */
                    $dataArray['admin_approved_for_source'] = isset($dataArray['admin_approved_for_source']) ? $dataArray['admin_approved_for_source'] : 0 ;
                    $dataArray['admin_yet_to_approve_for_source'] = isset($dataArray['admin_yet_to_approve_for_source']) ? $dataArray['admin_yet_to_approve_for_source'] : 0 ;
                    $insert_update_array = array(
                        'table' => MEDIASOURCECOUNT,
                        'where' => array('colName' => 'SourceID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_source'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_source'])
                    );
                    $this->insert_update($insert_update_array);
                } elseif ($key == 'MediaExtensionID') {/* update MediaExtensionCount */
                    $insert_update_array = array(
                        'table' => MEDIAEXTENSIONCOUNT,
                        'where' => array('colName' => 'MediaExtensionID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_mediaext'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_mediaext'])
                    );
                    $this->insert_update($insert_update_array);
                } elseif ($key == 'MediaSectionID') {/* update MediaSectionCount */
                    $insert_update_array = array(
                        'table' => MEDIASECTIONCOUNT,
                        'where' => array('colName' => 'MediaSectionID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_media_sec_ref'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_media_sec_ref'])
                    );
                    $this->insert_update($insert_update_array);
                } elseif ($key == 'MediaSizeID') {/* update MediaSizeCounts */
                    $insert_update_array = array(
                        'table' => MEDIASIZECOUNTS,
                        'where' => array('colName' => 'MediaSizeID', 'val' => $val),
                        'data' => array('ApproveCount' => $dataArray['admin_approved_for_media_size'], 'YetToApproveCount' => $dataArray['admin_yet_to_approve_for_media_size'])
                    );
                    $this->insert_update($insert_update_array);
                }
            }
        }
    }

    /**
     * Function Name: insert_update
     * @param data[]
     * Description: insert/update row
     */
    function insert_update($data = array()) {
        $this->db->select($data['where']['colName']);
        $this->db->where(array($data['where']['colName'] => $data['where']['val']));
        $this->db->from($data['table']);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        if (!empty($result)) {/* update */
            $this->db->where(array($data['where']['colName'] => $data['where']['val']));
            $this->db->update($data['table'], $data['data']);
        } else {/* insert */
            $insert_array = array_merge($data['data'], array($data['where']['colName'] => $data['where']['val']));
            $this->db->insert($data['table'], $insert_array);
        }
    }


    
    /**
     * [uploadVideo Used to upload video file]
     * @param [type] $Data [input data for upload file Request]
     * @return [Object] [json object]
     */
    function uploadVideo($Data, $generateThumb=1)
    {

        $Return['Message'] = lang('success');
        $Return['ResponseCode'] = 200;
        $Return['Data'] = array();

        $type = strtolower($Data['Type']);
        $DeviceType = strtolower($Data['DeviceType']);

        $module_id = isset($Data['ModuleID']) ? $Data['ModuleID'] : 0;
        $entity_guid = isset($Data['ModuleEntityGUID']) ? $Data['ModuleEntityGUID'] : 0;
        $entity_id = 0;
        if ($entity_guid && $module_id)
        {
            $entity_id = get_detail_by_guid($entity_guid, $module_id);
        }

        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;

        $thumb_arr  = $type . '_thumb_size';   

        $this->check_video_folder_exist($dir_name);

        $config['upload_path'] = $dir_name . "/";
        $config['allowed_types'] = $this->allowed_video_types;
        $config['max_size'] = $this->allowed_video_max_size;
        $config['encrypt_name'] = TRUE;
        
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('qqfile')){ 
            $Return['ResponseCode'] = 412;            
            $Errors = $this->upload->error_msg;
            if (!empty($Errors)) {
                $err = trim($Errors['0']);
                if($err == 'The file you are attempting to upload is larger than the permitted size.' || $err == 'The uploaded file exceeds the maximum allowed size in your PHP configuration file.') {
                    $err = 'Video you tried posting should be less than 50 MB';
                }
                $Return['Message'] = $err; // first message
            } else
            {
                $Return['Message'] = "Unable to fetch error code."; // first message
            }
            return $Return;
            //Shows all error messages as a string              
        } else
        {  
            $UploadData = $this->upload->data();

            $totalSize = 0;
            $JobID = NULL;
            if (strtolower(IMAGE_SERVER) == 'remote')
            {
                $s3_path = $dir_name . '/' . $UploadData['file_name'];
                $s3 = new S3($this->s3_credential);
                $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ);
                
                //$JobID = $this->zencoder_create_job($s3_path, $this->$thumb_arr);
                if ($upload_flag) {
                    @unlink($s3_path);
                }
            }else{
                $this->create_video_thumb($UploadData);
            }

            $UploadthumbData = [];
            if(!empty($_FILES['qqthumb']['name'])) {

               
                $config1['upload_path'] = $dir_name . "/";
                $config1['allowed_types'] = 'jpg|gif|png|jpeg|JPG|PNG';
                $config1['max_size'] = $this->allowed_image_max_size;
                $config1['encrypt_name'] = TRUE;
                $this->upload->initialize($config1);
                //echo '<pre>';print_r($config1);die;
                $this->load->library('upload', $config1);
                if (!$this->upload->do_upload('qqthumb')){ 
                     $Return['ResponseCode'] = 412;     
                     $Return['Message'] = $this->upload->error_msg;
                }else{
                   $UploadthumbData =$this->upload->data();
                     $s3_path = $dir_name . '/' . $UploadthumbData['file_name'];
                    $s3 = new S3($this->s3_credential);
                    $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ);
                
                    //$JobID = $this->zencoder_create_job($s3_path, $this->$thumb_arr);
                    if ($upload_flag) {
                        @unlink($s3_path);
                    }
                }
            }
            
            $image_type = strtolower($UploadData['file_ext']);
            $image_type = trim($image_type, '.');
            $MediaSectionID = isset($this->MediaSectionIDArray[$type]) ? $this->MediaSectionIDArray[$type] : 1;
            $DeviceID = $this->DeviceTypeID;
            if (empty($DeviceID))
            {
                $DeviceID = isset($this->DeviceTypeIDArray[$DeviceType]) ? $this->DeviceTypeIDArray[$DeviceType] : 1;
            }
            $MediaExtensionID = isset($this->MediaExtensionIDArray[$image_type]) ? $this->MediaExtensionIDArray[$image_type] : 1;
            
            $totalSize = $UploadData['file_size'] + $totalSize;
            
            $source_id = ($this->SourceID) ? $this->SourceID : 1 ;
            $resolution = isset($Data['Resolution']) ? $Data['Resolution'] : '';
            $Media = array(
                'MediaGUID' => get_guid(),
                'UserID' => $this->UserID,
                'MediaSectionID' => $MediaSectionID,
                'OriginalName'      => $UploadData['orig_name'],
                'ModuleID' => $module_id,
                'ModuleEntityID' => $entity_id,
                'ImageName' => $UploadData['file_name'],
                'ImageUrl' => $UploadData['file_name'],
                'Size' => $totalSize, //The file size in kilobytes
                'DeviceID' => $DeviceID,
                'SourceID' => $source_id,
                'MediaExtensionID' => $MediaExtensionID,
                'MediaSectionReferenceID' => 0,
                'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                'StatusID' => 1, // default pending
                'IsAdminApproved' => 1,
                'AbuseCount' => 0,
                'AlbumID' => 0,
                'Caption' => "",
                'MediaSizeID' => $this->getMediaSizeID($totalSize),
                'ConversionStatus' => 'Pending',
                'JobID' => $JobID,
                'Resolution' => $resolution,
                'ImageThumbName'=>isset($UploadthumbData['file_name'])?$UploadthumbData['file_name']:'default_thumb.jpg'
            );

            $this->save_media($Media,$this->UserID);
            //insert in to media table with status pending which means in 24 hours this file will be deleted from server.
            //MediaGUID, MediaName
            $Return['Data'] = array(
                "MediaGUID" => $Media['MediaGUID'],
                "ImageName" => $Media['ImageName'],
                "FileName"  => pathinfo($Media['ImageName'], PATHINFO_FILENAME),
                'ImageThumbName'=>$Media['ImageThumbName'],
                "OriginalName" => $Media['OriginalName'],
                "ImageServerPath"=>IMAGE_SERVER_PATH.PATH_IMG_UPLOAD_FOLDER.$type,
                "MediaType"=>'VIDEO',
                "MediaSectionAlias"=>($MediaSectionID==4?'album':''),
                'Resolution'           => $Media['Resolution']
            );

            
        }
        return $Return;
    }

    /**
     * @Summary: check if folder exists otherwise create new
     * @create_date: 3 apr, 2013
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */

    /**
     * [check_folder_exist check if folder exists otherwise create new]
     * @param  [string] $dir_name [Directroy name]
     * @param  array  $folder   [folder array]
     */
    public function check_video_folder_exist($dir_name)
    {
        $d = ROOT_PATH . '/' . $dir_name;
        //$d =  '/var/www/html/framework/social/' . $dir_name;
        
       // echo $d;die;
        if (!is_dir($d))
        {
            mkdir($d, 0777, TRUE);
        }
    }

    public function create_video_thumb($video) {
        // where ffmpeg is located  
        $ffmpeg = '/usr/bin/ffmpeg';
        //video dir  
        $videofile = $video['full_path'];
                
        foreach ($thumb_arr as $i => $row) {
            //screenshot size 
            $size = $row[0].'x'.$row[1];
                        
            //where to save the image  
            if (!is_dir($video['file_path'].$size.'/')) {
                mkdir($video['file_path'].$size.'/', 0777, TRUE);
            }
            $image = $video['file_path'].$size.'/'.pathinfo($video['file_name'], PATHINFO_FILENAME).'.jpg';
            
            //ffmpeg command
            $cmd = "$ffmpeg -itsoffset -4 -i $videofile -vcodec png -vframes 1 -an -f rawvideo -s $size -y $image";
            
            shell_exec($cmd);
        }        
    }


    public function zencoder_create_job($video_url = '', $thumb_arr='')
    {
        if($video_url==''){
            return FALSE;
        }
        
        $this->load->library('Services_Zencoder'); // load library
        $outputs = $thumbnails = array();
        $video_types = array('mp4');// 'ogv','webm', allowed video conversions
       
        $name_parts = pathinfo($video_url);
        
        $notify_url = base_url('/cron/zencoder_notification') ;
        $base_url   = $name_parts['dirname'];
        
        $thumbnails[] = array(
                            "label" => "posterorg",
                            "base_url" => "s3://".BUCKET."/" . $name_parts['dirname'].'/',
                            "public" => true,
                            "number" => 1,
                            "start_at_first_frame" => true,
                            "filename" => $name_parts['filename'],
                            "format" => "jpg"
                        );
        
        if(!empty($thumb_arr)) {            
            //create thumnail array for zencoder for given values of thumbsize
            foreach ($thumb_arr as $i => $row) {
                $size = $row[0].'x'.$row[1];                                
                $thumbnails[] = array(
                                    "label" => "poster".$size,
                                    "base_url" => "s3://".BUCKET."/" . $name_parts['dirname'].'/'. $size . "/",
                                    "public" => true,
                                    "size" => $size,
                                    "number" => 1,
                                    "start_at_first_frame" => true,
                                    "filename" => $name_parts['filename'],
                                    "format" => "jpg"
                                );                
            }
        }
        else
        {   //in case thumb is not given we can use default thumb for comments 
            $imgb_url_size = array('220x220','750x500','533x300',ADMIN_THUMB_WIDTH.'x'.ADMIN_THUMB_HEIGHT);            
            foreach ($imgb_url_size as $size) {
                $thumbnails[] = array(
                            "label" => "poster".$size,
                            "base_url" => "s3://".BUCKET."/" . $name_parts['dirname'].'/'. $size . "/",
                            "public" => true,
                            "number" => 1,
                            "start_at_first_frame" => true,
                            "filename" => $name_parts['filename'],
                            "format" => "jpg"
                        );
            }            
        }    
            
        //Create Videos array for zencoder 
        $k = 0;
        foreach ($video_types as $vtype) {
            $new_url    = $base_url . '/' . $name_parts['filename'] . '.'.$vtype;
            $output_url = "s3://".BUCKET."/" . $new_url;
            //create video output array
            $output = array   (
                "label" => $vtype,
                "url" => $output_url,
                "public" => true
            );
            ++$k;
            if($k==1) {
                $output["thumbnails"] = $thumbnails;
            }
            $outputs[] = $output;
        }
        $input_url  = "s3://".BUCKET."/" . $video_url;
 
        try {
 
            // Initialize the Services_Zencoder class
            $zencoder     = new Services_Zencoder(ZENCODER_API_KEY);
            // New Encoding Job
            $encoding_job = $zencoder->jobs->create(array(
                "input" => $input_url,
                "notifications"=>array($notify_url),
                "outputs" => $outputs                    
            ));
            return $encoding_job->id;
            // Success if we got here
            // Store Job/Output IDs to update their status when notified or to check their progress.
        }
        catch (Services_Zencoder_Exception $e) {
            $data = array(
                'error' => 'Zencode API ERROR',
                'result' => 'error',
                'file_path' => $video_url,
                'file_name' => $name_parts['filename'],
                'reason' => serialize($e->getErrors())
            );
            return FALSE;
        }
        // Catch notification
    }
    
    public function zencoder_notification()
    {
        $data = file_get_contents('php://input');
        $job = json_decode($data, TRUE);

        if (isset($job['job']['id']) && !empty($job['job']['id']))
        {
            $job_id = $job['job']['id'];
             
            $duration_in_ms   = !empty($job['input']['duration_in_ms'])?$job['input']['duration_in_ms']:0;
            $input_file_size   = !empty($job['input']['file_size_in_bytes'])?$job['input']['file_size_in_bytes']:0;
            $height   = !empty($job['input']['height'])?$job['input']['height']:'';
            $width   = !empty($job['input']['width'])?$job['input']['width']:'';
            $state      = !empty($job['job']['state'])?ucfirst($job['job']['state']):'';
            $resolution = '';
            if($width && $height) {
                $resolution = $width.'x'.$height;
            }
            
            if(!empty($duration_in_ms) && $state=='Finished')
            {
                $this->db->select('MediaID,ImageName,MediaSectionID,MediaSectionReferenceID, M.StatusID');
                $this->db->from(MEDIA.' AS M');
                $this->db->JOIN(MEDIAEXTENSIONS.' AS ME','M.MediaExtensionID=ME.MediaExtensionID');
                $this->db->JOIN(MEDIATYPES.' AS MT','MT.MediaTypeID=ME.MediaTypeID');
                $this->db->where('MT.MediaTypeID',2);
                $this->db->where('M.ConversionStatus','Pending');
                $this->db->where('M.JobID',$job_id);    
                $query = $this->db->get();

                $output_file_size = 0;
                $same_ext = 0;
                if($query->num_rows())
                {
                    $row = $query->row_array();
                    $image_name = $row['ImageName'];
                    $extension = explode('.', $image_name);
                    $extension = end($extension);
                    $extension = strtolower($extension);

                    if (isset($job['outputs']) && !empty($job['outputs']))
                    {
                        $outputs = $job['outputs']; 
                        foreach($outputs as $output)
                        {
                            $label = isset($output['label'])?strtolower($output['label']):'';
                            $output_file_size = $output_file_size + isset($output['file_size_in_bytes'])?$output['file_size_in_bytes']:0;
                            if($extension==$label)
                            {
                                $same_ext = 1;
                            }
                        }
                    }                    
                    if(empty($same_ext))
                    {
                        $output_file_size = $output_file_size + $input_file_size;    
                    }
                    $MediaSectionReferenceID = $row['MediaSectionReferenceID'];
                    
                    if($row['MediaSectionID'] == 3 && $state == 'Finished' && $row['MediaSectionReferenceID'] && $row['StatusID'] != 3)
                    {
                        $activity_data = get_detail_by_id($row['MediaSectionReferenceID'],0,'StatusID, LocalityID',2);
                        $activity_status = $activity_data['StatusID'];
                        $locality_id = $activity_data['LocalityID'];
                        if($activity_status != 3) {
                            $this->load->model('activity/activity_model');
                            $row = $this->activity_model->activate_activity($row['MediaSectionReferenceID']);
                            if(CACHE_ENABLE) {
                                sleep(5);
                                $this->cache->clean();
                            }
                            $this->notification_model->add_notification(82,$row['UserID'],array($row['UserID']),$row['ActivityID'],array(),true,1, array(), $locality_id);
                        }
                    } else if($row['MediaSectionID'] == 6 && $state == 'Finished' && $row['MediaSectionReferenceID'] && $row['StatusID'] != 3) {
                        $comment_status = get_detail_by_id($row['MediaSectionReferenceID'],20,'PostCommentID,EntityID,EntityType,UserID,StatusID',2);
                        $locality_id = 0;
                        if(isset($comment_status) && $comment_status['StatusID'] != 3 ) {
                            if($comment_status['EntityType'] == 'ACTIVITY') {
                                $locality_id = get_detail_by_id($comment_status['EntityID'],0,'LocalityID',1);
                            }
                            $this->notification_model->add_notification(82,$comment_status['UserID'],array($comment_status['UserID']),$comment_status['EntityID'],array(),true,1,array(), $locality_id);
                        }
                    }
                }
                
                $this->db->where('JobID',$job_id);
                $this->db->update(MEDIA,array('VideoLength'=>$duration_in_ms, 'ConversionStatus'=>$state, 'Size'=>$output_file_size, 'Resolution' => $resolution));
                
                if(!empty($MediaSectionReferenceID) && CACHE_ENABLE) {
                    $this->cache->delete('activity_'.$MediaSectionReferenceID);
                }
            }
            echo "Success!\n";
        }elseif ($state == "cancelled") {
           echo "Cancelled!\n";
        } else {
           echo "Fail!\n";
        }
    }

    function create_cover_thumb($file_name,$width=200,$height=200)
    {
        $s3         = new S3($this->s3_credential);
        $this->load->library('phpthumb'); 

        $w = $width;
        $h = $height;

        $MediaSectionAlias = 'profilebanner';

        $file_full_path = $this->image_server_path.PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.THUMB_profilebanner.$file_name;

        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $MediaSectionAlias;

        $folder_arr = array($MediaSectionAlias."/".$w."x".$h);

        $this->check_folder_exist(PATH_IMG_UPLOAD_FOLDER, $folder_arr);

        $name_parts = pathinfo($file_name);
        $ext = strtolower($name_parts['extension']);
        $totalSize = 0;
        
        $temp_file = file_get_contents($file_full_path);

        $phpThumb = new phpThumb();
        $phpThumb->resetObject();
        $phpThumb->setSourceData($temp_file);
        $phpThumb->setParameter('ar','x');
        if($ext == 'png')
        {
            $phpThumb->setParameter('f','png');
        }
        if (isset($w))
        {
            $phpThumb->setParameter('w', $w);
        }
        if (isset($h))
        {
            $phpThumb->setParameter('h', $h);
        }
        
        $s3_path = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
        $local_path = DOCUMENT_ROOT . $this->pathToFolder . '/' . $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
        
        $size = @filesize($local_path);
        if(isset($size) && !empty($size)) {
            $totalSize = $size;
        }

        $phpThumb->setParameter('zc', true);

        if ($phpThumb->GenerateThumbnail()) 
        { // this line is VERY important, do not remove it!
            if ($phpThumb->RenderToFile($local_path)) 
            { //save file to destination
                if (strtolower(IMAGE_SERVER) == 'remote') 
                {
                    $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);
                    if ($upload_flag) {
                        @unlink($local_path);
                    }
                }
            }
        }
        
        return $totalSize;
    }

    function create_image_thumb($media_section_id, $page_no=1, $page_size=30) 
    {
        
        $this->db->select('M.MediaID, M.ImageName, MT.Name as MediaType, M.MediaSectionID, MS.MediaSectionAlias', FALSE);        
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID=M.MediaSectionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->where('M.MediaSectionID', $media_section_id);
        $this->db->where('ME.MediaTypeID', 1);
        $this->db->order_by('M.MediaID','DESC');
        
        $offset = ($page_no-1)*$page_size;    
        $this->db->limit($page_size, $offset);

        $query  = $this->db->get(MEDIA . ' M');

        if($query->num_rows())
        {
            $s3         = new S3($this->s3_credential);
            $this->load->library('phpthumb'); 
            $i = 0;
            
            foreach($query->result_array() as $img) 
            {
              
                ++$i;
                $exist              = FALSE;                
                $MediaID            = $img['MediaID'];
                $MediaType          = $img['MediaType'];
                $MediaSectionID     = $img['MediaSectionID'];
                $MediaSectionAlias  = $img['MediaSectionAlias'];
                $file_name          = $img['ImageName'];          
                $SubDir             = "/";
                $file_full_path     = "";
                                
                if(!empty($img))
                {
                    $file_full_path = PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.$SubDir.$file_name;
                    if(strtolower(IMAGE_SERVER) == 'remote')
                    {
                        if($s3->getObjectInfo(BUCKET, $file_full_path))
                        {
                           $exist = TRUE; 
                           $file_full_path = $this->image_server_path.PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.$SubDir.'org_'.$file_name;
                        }
                        elseif($s3->getObjectInfo(BUCKET, PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.$SubDir.$file_name))
                        {
                            $exist = TRUE; 
                            $file_full_path = $this->image_server_path.PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.$SubDir.$file_name;        
                        }
                    } 
                    else 
                    {
                        $file_full_path = IMAGE_ROOT_PATH.$MediaSectionAlias.$SubDir.'org_'.$file_name; 
                        if(file_exists($file_full_path))
                        {                            
                            $exist = TRUE;    
                        }
                        else if(file_exists(IMAGE_ROOT_PATH.$MediaSectionAlias.$SubDir.$file_name))
                        {
                            $file_full_path = IMAGE_ROOT_PATH.$MediaSectionAlias.$SubDir.$file_name;
                            $exist = TRUE;    
                        } 
                    }                            
                }
                echo "<br> $i. ".$file_full_path;
                if($exist)
                {
                    $dir_name   = PATH_IMG_UPLOAD_FOLDER . $MediaSectionAlias;

                    $folder_arr = array($MediaSectionAlias."/1200x300");

                    $this->check_folder_exist(PATH_IMG_UPLOAD_FOLDER, $folder_arr);

                    $name_parts = pathinfo($file_name);
                    $ext = strtolower($name_parts['extension']);
                    $totalSize = 0;
                    
                    $temp_file = file_get_contents($file_full_path);
            
                    $w = 1200;
                    $h = 300;
                    $phpThumb = new phpThumb();
                    $phpThumb->resetObject();
                    $phpThumb->setSourceData($temp_file);
                    $phpThumb->setParameter('ar','x');
                    if($ext == 'png')
                    {
                        $phpThumb->setParameter('f','png');
                    }
                    if (isset($w))
                    {
                        $phpThumb->setParameter('w', $w);
                    }
                    if (isset($h))
                    {
                        $phpThumb->setParameter('h', $h);
                    }
                    
                    $s3_path = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                    $local_path = DOCUMENT_ROOT . $this->pathToFolder . '/' . $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;

                    $phpThumb->setParameter('zc', true);

                    if ($phpThumb->GenerateThumbnail()) 
                    { // this line is VERY important, do not remove it!
                        if ($phpThumb->RenderToFile($local_path)) 
                        { //save file to destination
                            if (strtolower(IMAGE_SERVER) == 'remote') 
                            {
                                $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ, $this->headers);
                                if ($upload_flag) {
                                    @unlink($local_path);
                                }
                            }
                        }
                    }                     
                }               
            }
        }
    }

    function copy_video_thumb($media_section_id, $page_no=1, $page_size=30) 
    {
        $this->db->select('M.MediaID, M.ImageName, MT.Name as MediaType, M.MediaSectionID, MS.MediaSectionAlias', FALSE);        
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID=M.MediaSectionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->where('M.MediaSectionID', $media_section_id);
        $this->db->where('ME.MediaTypeID', 2);
        $this->db->order_by('M.MediaID','DESC');
        
        $offset = ($page_no-1)*$page_size;    
        $this->db->limit($page_size, $offset);

        $query  = $this->db->get(MEDIA . ' M');

        if($query->num_rows())
        {
            $s3         = new S3($this->s3_credential);           
            $i = 0;
            foreach($query->result_array() as $img) 
            {
                ++$i;
                $exist              = FALSE;                
                $MediaSectionAlias  = $img['MediaSectionAlias'];
                $file_name          = $img['ImageName'];          
                $SubDir             = "/";
                $file_full_path     = "";
                $file_location      = "";
                                
                if(!empty($img))
                {
                    $file_full_path = PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.$SubDir.$file_name;
                    if(strtolower(IMAGE_SERVER) == 'remote')
                    {
                        if($s3->getObjectInfo(BUCKET, $file_full_path))
                        {
                            $exist = TRUE;
                            $file_full_path = $this->image_server_path.PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.$SubDir.$file_name;
                            $file_location = $file_full_path;
                        }
                    } 
                    else 
                    {
                        $file_full_path = IMAGE_ROOT_PATH.$MediaSectionAlias.$SubDir.$file_name; 
                        $file_location = $file_full_path;
                        if(file_exists($file_full_path))
                        {                            
                            $exist = TRUE;    
                        }                        
                    }                            
                }
                
                if($exist)
                {
                    $folder_arr = array($MediaSectionAlias."/56x56");

                    $this->check_folder_exist(PATH_IMG_UPLOAD_FOLDER, $folder_arr);

                    $name_parts = pathinfo($file_name);
                    $ext = strtolower($name_parts['extension']);

                    $file_name = str_replace($ext, "jpg", $file_name);
                    $file_location = str_replace($ext, "jpg", $file_location);

                    $file_full_path = PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.$SubDir.'220x220/'.$file_name;
                    $destinationFile = PATH_IMG_UPLOAD_FOLDER.$MediaSectionAlias.$SubDir.'56x56/'.$file_name;
                    if(strtolower(IMAGE_SERVER) == 'remote')
                    {
                        if($s3->getObjectInfo(BUCKET, $file_full_path))
                        {
                           $s3->copyObject(BUCKET, $file_full_path, BUCKET, $destinationFile, S3::ACL_PUBLIC_READ);
                        }
                    }
                    else
                    {
                        if(file_exists($file_location))
                        {
                            copy($this->image_server_path.$file_full_path,$destinationFile);
                        }
                    }                     
                }               
            }
        }
    }

    /**
     * [delete_dangling_media Used to delete dangling media]
     */
    function delete_dangling_media() 
    {
        $previous_date = get_current_date('%Y-%m-%d 23:59:59', 1);

        $this->db->select('M.MediaID, MT.Name as MediaType');
        $this->db->select('M.ImageName', FALSE);
        $this->db->select('M.StatusID', FALSE);
        $this->db->select('M.MediaSectionID', FALSE);
        $this->db->select('MS.MediaSectionAlias', FALSE);

        $this->db->join(MEDIASECTIONS . " AS MS", ' MS.MediaSectionID = M.MediaSectionID', 'right');
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');

        $this->db->from(MEDIA . " AS M");
        $this->db->where('M.StatusID', 1);
        $this->db->where('M.CreatedDate <= ', $previous_date);
        $this->db->order_by('M.MediaID', 'ASC');
        $this->db->limit(10);
        $query = $this->db->get();        

        
        if($query->num_rows()) 
        {
            $media_ids = array();
            foreach($query->result_array() as $row)
            {
                $file_name              = $row['ImageName'];
                $media_section_alias    = $row['MediaSectionAlias'];
                $media_type             = $row['MediaType'];
                $media_ids[]            = $row['MediaID'];           
                                       
                if(!empty($file_name) && !empty($media_section_alias)) 
                {            
                    
                    $thumb_arr  = $media_section_alias . '_thumb_size';
                    $dir_name   = PATH_IMG_UPLOAD_FOLDER . $media_section_alias;

                    if($media_type == 'Video') 
                    {
                        $ext = pathinfo($file_path, PATHINFO_EXTENSION);

                        $video_file = str_replace(".".$ext, ".mp4", $file_name);
                        $video_file_path = $dir_name . '/' . $video_file;
                        $this->delete_file($video_file_path);

                        $video_file = str_replace(".".$ext, ".ogg", $file_name);
                        $video_file_path = $dir_name . '/' . $video_file;
                        $this->delete_file($video_file_path);
                        
                        $video_file = str_replace(".".$ext, ".webm", $file_name);
                        $video_file_path = $dir_name . '/' . $video_file;
                        $this->delete_file($video_file_path);

                        $file_name = str_replace(".".$ext, ".jpg", $file_name);
                        $file_path = $dir_name . '/' . $file_name;
                    }
                    else
                    {
                        $file_path = $dir_name . '/' . $file_name;
                        $this->delete_file($file_path);

                        $file_path = $dir_name . '/org_' . $file_name;
                        $this->delete_file($file_path);    
                    } 
                    
                    foreach ($this->$thumb_arr as $i => $row) 
                    {
                        $w = $row[0];
                        $h = $row[1];                    
                        $file_path = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                        $this->delete_file($file_path);               
                    }                   
                }
            }
            if(count($media_ids) > 0)
            {
                $this->db->where_in('MediaID',$media_ids);
                $this->db->delete(MEDIA);              
            }            
        }
    }

    function delete_file($file_path)
    {
        if (strtolower(IMAGE_SERVER) == 'remote') 
        {
            $s3 = new S3($this->s3_credential);                        
            if($s3->getObjectInfo(BUCKET, $file_path))
            {
                // delete file
                $s3->deleteObject(BUCKET, $file_path);                
            }
        }
        else
        {
            $file_path = ROOT_PATH. '/' .$file_path;
            if(file_exists($file_path))
            {
                // delete file
                unlink($file_path);
            }
        }
    }

     //save attachments from external communications
    function uploadExternalAttachmnts($attachments, $data=array())
    {
                
        $MediaGUID = array();
        if(isset($attachments) && !empty($attachments))
        {
            $i=0;
            foreach ($attachments as $attachment)
            {                
                //checkif attachment is there in the response
                if($attachment['is_attachment'])
                {
                    $type   =   $data['Type'];
                    $folder_arr = $type . '_folder';
                    $zc_arr     = $type . '_zoom_crop_array';

                    $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
                    $chk_folder = PATH_IMG_UPLOAD_FOLDER;

                    $this->check_folder_exist($chk_folder, $this->$folder_arr);
                    
                    //Generate Thumb
                    $D = @getimagesize($attachment['filepath']);                  
                    $types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');
                    $image_width        = $D['0'];
                    $image_height       = $D['1'];
                    $image_type     = ( ! isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
                    
                    rename(
                        $attachment['filepath'],
                        $dir_name.'/'.'org_'.$attachment['filename']
                    );
                    $file_name_ext  = explode('.', $attachment['filename']);
                    $ext            = end($file_name_ext);
                    $ext            = strtolower($ext);
                    
                    // Copy original image and reduce its quality to 75%                    
                    $this->generate_thumb($dir_name.'/'.'org_'.$attachment['filename'], $dir_name, $attachment['filename'], array(array($image_width,$image_height)), $this->$zc_arr, 1,$this->pathToFolder,'email_attachments',1);
                    $totalSize = 0;
                    //thumb code
                    
                    
                    if(in_array($ext,$this->VideoExt)){
                        $this->create_video_thumb($attachment);
                    } else {
                        $zc_arr     = 'comments' . '_zoom_crop_array';//comments incase of common social (hardcoded need to discuss)
                        if($image_type != 'unknown'){
                            $totalSize = $this->generate_thumb(
                                $attachment['filepath'], 
                                $dir_name, 
                                $attachment['filename'], 
                                array(array($image_width,$image_height)), 
                                $this->$zc_arr,
                                1,
                                '',
                                'email_attachments'
                            );
                        }
                    }
                    
                    $OrgFileName = $attachment['filename'];

                    $UploadFilePathArr = explode('/', $attachment['filepath']);
                    $UploadFilePathArr[count($UploadFilePathArr)-1] = 'org_'.$UploadFilePathArr[count($UploadFilePathArr)-1];
                    $attachment['filepath'] = implode('/', $UploadFilePathArr);
                    $attachment['filename'] = 'org_'.$attachment['filename'];
                    
                    //move file into S3 Bucket
                    
                    if (strtolower(IMAGE_SERVER) == 'remote') {               

                        $s3_path        = $dir_name .'/' . $attachment['filename'];
                        $s3             = new S3($this->s3_credential);
                        $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ,$this->headers);
                        @unlink($s3_path);
                    }

                    //Save Media into DB
                    $image_type         = $ext;
                    $type = $data['Type'];// For CommonSocial only Or it can be pos/wall in case of WOrkHigh
                    $DeviceType = 'native';//hardcoded for now as this is for external communication (need to ask)
                    $MediaSectionID     = isset($this->MediaSectionIDArray[$type]) ? $this->MediaSectionIDArray[$type] : 1;
                    $DeviceID           = isset($this->DeviceTypeID) ? $this->DeviceTypeID : '1';
                    if(empty($DeviceID)) {
                        $DeviceID           = isset($this->DeviceTypeIDArray[$DeviceType]) ? $this->DeviceTypeIDArray[$DeviceType] : 1;
                    }

                    $MediaExtensionID   = isset($this->MediaExtensionIDArray[$image_type]) ? $this->MediaExtensionIDArray[$image_type] : 1;
                    $totalSize = (int)@filesize($attachment['filepath']) + (int)$totalSize;


                    $this->db->select('UserID');
                    $this->db->from(USERS);
                    $this->db->where('Email',$data['Sender']);
                    $query = $this->db->get();    
                    $row = $query->row_array();
                    $source_id = isset($this->SourceID) ? $this->SourceID :  1 ;
                    $Media = array(
                        'MediaGUID'         => get_guid(),
                        'UserID'            => $row['UserID'],
                        'OriginalName'      => $attachment['name'],
                        'MediaSectionID'    => $MediaSectionID,
                        'ModuleID'          => isset($module_id) ? $module_id : 0, //(need to ask)
                        'ModuleEntityID'    => isset($entity_id) ? $entity_id : 0,
                        'ImageName'         => $OrgFileName,
                        'ImageUrl'          => $OrgFileName,
                        'Size'              => $totalSize, //The file size in kilobytes
                        'DeviceID'          => $DeviceID,
                        'SourceID'          => ($source_id) ? $source_id :  1,
                        'MediaExtensionID'  => $MediaExtensionID,
                        'MediaSectionReferenceID'=>0,
                        'CreatedDate'       => get_current_date('%Y-%m-%d %H:%i:%s'),
                        'StatusID'          => 1, // default pending
                        'IsAdminApproved'   => 1,
                        'AbuseCount'        => 0,
                        'AlbumID'           => 0,
                        'Caption'           => "" ,
                        'MediaSizeID'       => $this->getMediaSizeID($totalSize)           
                    ); 
                    $MediaGUID[$i]['MediaGUID'] = $Media['MediaGUID'];
                    //get mediaType by MediaExtension
                    $media_type = $this->db->select('MediaType.Name')->from(MEDIAEXTENSIONS . ' as MediaExtension')->join(MEDIATYPES.' MediaType','MediaType.MediaTypeID=MediaExtension.MediaTypeID')->where('MediaExtension.MediaExtensionID',$MediaExtensionID)->get()->row_array();
                    $MediaGUID[$i++]['MediaType'] = isset($media_type['Name']) ? $media_type['Name'] : '';                                        
                }
            }
        }
        return $MediaGUID;
    }

    //check file has valid extension
    function validateFileExt($ext)
    {        
        if(isset($this->MediaExtensionIDArray[$ext]))
            return true;
        else
            return false;
    }

     /**
     * [upload_files Used to upload file]
     * @param [type] $Data [input data for upload file Request]
     * @return [Object] [json object]
     */
    function upload_documents($Data){     
       
        $Return['Message']      = lang('success');
        $Return['ResponseCode'] = 200;
        $Return['Data']         =array();

        $type       = strtolower($Data['Type']);
        $DeviceType = strtolower($Data['DeviceType']);

        $module_id   = isset($Data['ModuleID']) ? $Data['ModuleID'] : 0 ;
        $entity_guid = isset($Data['ModuleEntityGUID']) ? $Data['ModuleEntityGUID'] : 0 ;
        $entity_id   = 0;
        if($entity_guid && $module_id){
            $entity_id   = get_detail_by_guid($entity_guid, $module_id);
        }
        
        $folder_arr = $type . '_folder';
 
        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
        $chk_folder = PATH_IMG_UPLOAD_FOLDER;

        $file_input = 'qqfile';
        if(isset($Data['FileInput']))
        {
            $file_input = $Data['FileInput'];
        }
        $this->check_folder_exist($chk_folder, $this->$folder_arr);

        $config['upload_path']      = $dir_name . "/";        
        $config['allowed_types']    = $this->allowed_message_types;
        $config['max_size']         = $this->allowed_message_max_size;
        $config['encrypt_name']     = TRUE;
        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload($file_input)){
            $Return['ResponseCode'] = 412;
            $Errors = $this->upload->error_msg;
            if(!empty($Errors)){
                $Return['Message'] =  $Errors['0']; // first message
            }else{
                $Return['Message'] =  "Unable to fetch error code."; // first message
            }
            return $Return;
            //Shows all error messages as a string              
        } else {
            $UploadData = $this->upload->data();
            rename(
                $UploadData['full_path'],
                $UploadData['file_path'].'/'.'org_'.$UploadData['file_name']
            );
            
            $totalSize = 0;
            //thumb code

            $file_name_ext  = explode('.', $UploadData['file_name']);
            $ext            = end($file_name_ext);
            $ext            = strtolower($ext);
            
            $OrgFileName = $UploadData['file_name'];

            $UploadFilePathArr = explode('/', $UploadData['full_path']);
            $UploadFilePathArr[count($UploadFilePathArr)-1] = 'org_'.$UploadFilePathArr[count($UploadFilePathArr)-1];
            $UploadData['full_path'] = implode('/', $UploadFilePathArr);

            $UploadFileNameArr = explode('/', $UploadData['file_name']);
            $UploadFileNameArr[count($UploadFileNameArr)-1] = 'org_'.$UploadFileNameArr[count($UploadFileNameArr)-1];
            $UploadData['file_name'] = implode('/', $UploadFileNameArr);

            if (strtolower(IMAGE_SERVER) == 'remote') {               

                $s3_path        = $dir_name .'/' . $UploadData['file_name'];
                $s3             = new S3($this->s3_credential);
                $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ,$this->headers);
                @unlink($s3_path);
            }
            $image_type         = $ext;
            $MediaSectionID     = isset($this->MediaSectionIDArray[$type]) ? $this->MediaSectionIDArray[$type] : 1;
            $DeviceID           = $this->DeviceTypeID;
            if(empty($DeviceID)) {
                $DeviceID           = isset($this->DeviceTypeIDArray[$DeviceType]) ? $this->DeviceTypeIDArray[$DeviceType] : 1;
            }

            $MediaExtensionID   = isset($this->MediaExtensionIDArray[$image_type]) ? $this->MediaExtensionIDArray[$image_type] : 1;
            $totalSize = $UploadData['file_size']+$totalSize;

            $source_id = ($this->SourceID) ? $this->SourceID : 1 ;

            $Media = array(
                'MediaGUID'         => get_guid(),
                'UserID'            => $this->UserID,
                'OriginalName'      => $UploadData['orig_name'],
                'MediaSectionID'    => $MediaSectionID,
                'ModuleID'          => $module_id,
                'ModuleEntityID'    => $entity_id,
                'ImageName'         => $OrgFileName,
                'ImageUrl'          => $OrgFileName,
                'Size'              => $totalSize, //The file size in kilobytes
                'DeviceID'          => $DeviceID,
                'SourceID'          => $source_id,
                'MediaExtensionID'  => $MediaExtensionID,
                'MediaSectionReferenceID'=>0,
                'CreatedDate'       => get_current_date('%Y-%m-%d %H:%i:%s'),
                'StatusID'          => 1, // default pending
                'IsAdminApproved'   => 1,
                'AbuseCount'        => 0,
                'AlbumID'           => 0,
                'Caption'           => "" ,
                'MediaSizeID'       => $this->getMediaSizeID($totalSize),
                'Resolution'           => $UploadData['image_width'].'x'.$UploadData['image_height']
            ); 

            $this->save_media($Media,$this->UserID);
            //insert in to media table with status pending which means in 24 hours this file will be deleted from server.
            //MediaGUID, MediaName
            $Return['Data']=array(
                "MediaGUID"=>$Media['MediaGUID'],
                "ImageName"=>$Media['ImageName'],
                "ImageServerPath"=>IMAGE_SERVER_PATH.PATH_IMG_UPLOAD_FOLDER.$type,
                "OriginalName"=>$UploadData['orig_name'],
                "MediaType"=>'Documents',
                "MediaExtension"=>$ext,
                "MediaSectionAlias"=>($MediaSectionID==4?'album':''),
                'Resolution'           => $UploadData['image_width'].'x'.$UploadData['image_height']
            );
        }
        return $Return;
    }
    
    function upload_video_thumb($Data,$filename)
    {   
        $generateThumb = 1;
        $filename = pathinfo($filename, PATHINFO_FILENAME);

        $Return['ResponseCode'] = 200;

        $type       = strtolower($Data['Type']);
        
        $folder_arr = $type . '_folder';
        $thumb_arr  = $type . '_thumb_size';
        
        $dir_name   = PATH_IMG_UPLOAD_FOLDER . $type;
        $chk_folder = PATH_IMG_UPLOAD_FOLDER;
        
        $org_file = pathinfo($_FILES['videothumb']['name']);

        $this->check_folder_exist($chk_folder, $this->$folder_arr);

        $config1['upload_path']      = $dir_name . "/";
        $config1['allowed_types']    = $this->allowed_image_types;
        $config1['max_size']         = $this->allowed_image_max_size;
        $config1['file_name']        = $filename.'.'.$org_file['extension'];
        $config1['encrypt_name']     = FALSE; 

        $this->load->library('upload');

        $this->upload->initialize($config1, true);

        if ( ! $this->upload->do_upload('videothumb')){
            $Return['ResponseCode'] = 412;
            $Errors = $this->upload->error_msg;
            if(!empty($Errors)){
                $Return['Message'] =  $Errors['0']; // first message
            }else{
                $Return['Message'] =  "Unable to fetch error code."; // first message
}
            return $Return;
            //Shows all error messages as a string              
        } else {
            $UploadData = $this->upload->data();
           
            $file_name = $UploadData['file_name'];

            $local_path = DOCUMENT_ROOT.SUBDIR.$dir_name . '/' . $file_name;

            if($generateThumb){

                foreach ($this->$thumb_arr as $i => $row) 
                {
                    $w = $row[0];
                    $h = $row[1];

                    $destination_path = DOCUMENT_ROOT.SUBDIR.$dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                    copy($local_path, $destination_path);
                
                    if (strtolower(IMAGE_SERVER) == 'remote') {               

                        $s3_path        = $dir_name . '/' . $w . 'x' . $h . '/' . $file_name;
                        $s3             = new S3($this->s3_credential);
                        $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ,$this->headers);                        
                        if ($upload_flag) 
                        {
                            @unlink($s3_path);
                        }
                    }

                }
           }
            if (strtolower(IMAGE_SERVER) == 'remote') {               

                $s3_path        = $dir_name .'/' . $UploadData['file_name'];
                $s3             = new S3($this->s3_credential);
                $upload_flag = $s3->putObjectFile($s3_path, BUCKET, $s3_path, S3::ACL_PUBLIC_READ,$this->headers);
                if ($upload_flag) {
                    @unlink($s3_path);
                }
            }

        }
        return $Return;
        
    }

    function create_thumb_from_cron()
    {
        $this->db->select('M.MediaID,M.MediaGUID,M.ImageName,M.Size,MS.MediaSectionAlias');
        $this->db->from(MEDIA.' M');
        $this->db->join(MEDIASECTIONS.' MS','MS.MediaSectionID = M.MediaSectionID','inner');
        $this->db->where('M.CreatedDate >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',NULL,FALSE);
        $this->db->where('M.MediaSectionReferenceID !=','');
        $this->db->where('M.StatusID',1);
        $Query = $this->db->get();
        
        if($Query->num_rows()>0)
        {
            $res = $Query->result_array();
            
            foreach ($res as $value) {
                
                $data = array('ImageName'=>$value['ImageName'],'MediaGUID'=>$value['MediaGUID'],'Type'=>$value['Type'],'TotalSize'=>$value['Size'],'document_root'=>DOCUMENT_ROOT);
                $this->uploadImageInBg($data);
            }
        }

    }

    public function set_profile_pic_by_admin($media_guid,$user_id)
    {
        $this->db->select('ImageName');
        $this->db->from(MEDIA);
        $this->db->where('MediaGUID',$media_guid);
        $query = $this->db->get();
        
        if($query->num_rows())
        {
            $data = $query->row_array();

            $this->db->set('AdminProfilePicture',$data['ImageName']);
            $this->db->where('UserID',$user_id);
            $this->db->update(USERS);
            
            if (CACHE_ENABLE) {
                $this->cache->delete('user_profile_'.$user_id);
            }
        }
    }

}
?>
