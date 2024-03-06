<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All process like : users_listing,users_profile, users_edit
 * @package    Users
 * @author     Ashwin soni (01-10-2014)
 * @version    1.0
 */

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
//require APPPATH.'/libraries/REST_Controller.php';

class Advertise extends Admin_API_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(array('admin/users_model', 'admin/advertise_model','admin/login_model'));

        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];
    }

    /**
     * Function for show user listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function index_post() {
      
    }
 
    /*Save image from src 
     * @author Sudhir
     */
    function saveImageFromByteArr($string){
        $dom = new domDocument;
       // libxml_use_internal_errors(true);
        /*** load the html into the object ***/
        $string = mb_convert_encoding($string, 'HTML-ENTITIES', "UTF-8");
        //@$pageDom->loadHTML($searchPage); 
        
        $dom->loadHTML($string);
        /*** discard white space ***/
        $dom->preserveWhiteSpace = false;
        $images = $dom->getElementsByTagName('img');
        $upload_path = 'upload/editorImages/';
        $time = time();        
        $fileAllowedArray = array('png','jpg','jpeg','PNG','JPG','JPEG','GIF','gif');
        foreach($images as $key=>$img)
        {
            $url = $img->getAttribute('src');
            if (strpos($url,'data:image') !== false) {

                $pos  = strpos($url, ';');
                $typearr = explode(':', explode('/',substr($url, 0, $pos)));
                $type = $typearr[1];
                $ext = $type[0];
                
                $filePath = $upload_path.'editor_image_'.$time.'.'.$ext;
                
                $imageData = str_replace('data:image/'.$ext.';base64,', '', $url);
                file_put_contents($filePath,base64_decode($imageData));

                /*
                    Check if file needs to be uploaded on S3
                */
                if (strtolower(IMAGE_SERVER) == 'remote') {
                    $s3 = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY);
                    $is_s3_upload = $s3->putObjectFile($filePath, BUCKET, $filePath, S3::ACL_PUBLIC_READ);
                }
                $img->setAttribute('src','{{SITEURL}}'.$filePath);
            }
        }
        # remove <!DOCTYPE 
        $dom->removeChild($dom->doctype);           

        # remove <html><body></body></html> 
        $dom->replaceChild($dom->firstChild->firstChild, $dom->firstChild);       
        $html = str_replace('<body>','',$dom->saveHTML());
        $html = str_replace('</body>','',$html);
        return $html;
    }
        /*Save image from src 
     * @author Sudhir
     */
    public function getBannerImageList_post(){
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/banner';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if (isset($Data) && $Data != NULL) {
            $Advertiser = !empty($Data['Advertiser']) ? $Data['Advertiser'] : '';
            $BannerModule = !empty($Data['BannerModule']) ? $Data['BannerModule'] : '';
            
            $result = $this->advertise_model->get_banner_images($Advertiser, $BannerModule);
            
            $Return['Data'] = $result;
        }else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
        /*Save image from src 
     * @author Sudhir
     */
    public function banner_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/banner';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (isset($Data) && $Data != NULL) {
            
            $start_offset   = (!empty($Data['Begin'])) ? $Data['Begin'] : 0;
            $end_offset     = (!empty($Data['End'])) ? $Data['End'] : 10;

            $search_keyword = (!empty($Data['SearchKey'])) ? $Data['SearchKey'] : '';
            $search_keyword = str_replace("_"," ",$search_keyword);

            $start_date     = (!empty($Data['StartDate'])) ? $Data['StartDate'] : '';
            $end_date       = (!empty($Data['EndDate'])) ? $Data['EndDate'] : '';

            $status_text      = (!empty($Data['SearchBannerStatus'])) ? $Data['SearchBannerStatus'] : '';
            $module_text      = (!empty($Data['SearchBannerModule'])) ? $Data['SearchBannerModule'] : '';

            $sort_by        = (!empty($Data['SortBy'])) ? $Data['SortBy'] : '';
            $order_by       = (!empty($Data['OrderBy'])) ? $Data['OrderBy'] : ''; 
            
            $result = $this->advertise_model->get_banner($start_offset, $end_offset, $start_date, $end_date, $status_text, $search_keyword, $module_text, $sort_by, $order_by);
            
            $Return['Data'] = $result;
        }else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /*
        Get banner details for edit mode (CONTENT UPDATE)
    */
    public function getBannerDetails_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = 'success';
        $Return['ServiceName'] = 'admin_api/advertise/getBannerDetails';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if (isset($Data) && $Data != NULL) {
            $BannerData = $this->advertise_model->GetBannerData($Data);
            if($BannerData===FALSE)
            {
                $BannerData = new stdClass();
                $BannerData->Locations[] = array('address' => '');
            }else
            {
                $Locations = explode(';', trim($BannerData->Location, ';'));
                unset($BannerData->Location);
                foreach ($Locations as $Location)
                {
                    $BannerData->Locations[] = array('address' => $Location);
                }
            }
            $Return['Data']['results'] = $BannerData;
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }

        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
        /*Save image from src 
     * @author Sudhir
     */
    public function saveBanner_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/advertise/saveBanner';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (!empty($Data))
        {

            /* Validation - starts */
            if ($this->form_validation->run('admin_api/advertise/saveBanner') == FALSE) { // for web
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error; //Shows all error messages as a string
            }
            else {
                
                if(strtotime($Data['EndDate']) < strtotime($Data['StartDate'])){
                    $Return['ResponseCode'] = 511;
                    $Return['Message'] = 'Start date should be before date of End date';
                    return $this->response($Return); 
                } 
                /*else if(strtotime($Data['StartDate']) < strtotime(date('Y-m-d'))){
                    $Return['ResponseCode'] = 511;
                    $Return['Message'] = 'Start date should not be before date of current date';
                    return $this->response($Return); 
                } */
                else if(strtotime($Data['EndDate']) < strtotime(date('Y-m-d'))){
                    $Return['ResponseCode'] = 511;
                    $Return['Message'] = 'End date should not be before date of current date';
                    return $this->response($Return);
                }
                
                $fieldArray = array();
            
                $StartDate      = (isset($Data['StartDate']) && $Data['StartDate']!= '') ? $Data['StartDate'] : NULL;
                $EndDate        = (isset($Data['EndDate']) && $Data['EndDate']!= '') ? $Data['EndDate'] : NULL;
                
                $BlogID         = (isset($Data['BlogID'])) ? $Data['BlogID'] : '';
                $BlogUniqueID   = (isset($Data['BlogUniqueID'])) ? $Data['BlogUniqueID'] : ''; // banner module
                $BlogTitle      = (isset($Data['BlogTitle'])) ? $Data['BlogTitle'] : '';
                $Advertiser     = (isset($Data['Advertiser'])) ? $Data['Advertiser'] : '';
                $BannerSource   = (isset($Data['BannerSource'])) ? $Data['BannerSource'] : 1;
                $BlogDescription= (isset($Data['BlogDescription'])) ? $Data['BlogDescription'] : '';
                $URL            = (isset($Data['URL'])) ? $Data['URL'] : NULL;
                $Duration       = (isset($Data['Duration'])) ? $Data['Duration'] : 5;
                $AdvertiserContact = (isset($Data['AdvertiserContact'])) ? $Data['AdvertiserContact'] : '';
                
                $rawImageBanner = (isset($Data['rawImageBanner'])) ? $Data['rawImageBanner'] : '';
                $BlogImage      = (isset($Data['BlogImage'])) ? $Data['BlogImage'] : '';
                $BannerSize     = (isset($Data['BannerSize'])) ? $Data['BannerSize'] : '';
                $SourceScript   = (isset($Data['SourceScript'])) ? $Data['SourceScript'] : '';
                
                
                if(empty($rawImageBanner) && empty($BlogImage) && empty($SourceScript)){
                    $Return['ResponseCode'] = 511;
                    $Return['Message'] = 'Please select ad image or enter source script';
                    return $this->response($Return);
                }
                
                $fieldArray['Type'] = 'banner';
                $fieldArray['BlogUniqueID'] = $BlogUniqueID;
                $fieldArray['BlogTitle'] = $BlogTitle;
                $fieldArray['Advertiser'] = $Advertiser;
                $fieldArray['BannerSource'] = $BannerSource;
                $fieldArray['BlogDescription'] = $BlogDescription;
                $fieldArray['URL'] = $URL;
                $fieldArray['Duration'] = $Duration;
                $fieldArray['StartDate'] = date('Y-m-d H:i:s', strtotime($StartDate));
                $fieldArray['EndDate'] = date('Y-m-d H:i:s', strtotime($EndDate));
                $fieldArray['AdvertiserContact'] = $AdvertiserContact;
                $fieldArray['CreatedBy'] = $this->UserID;
                $fieldArray['SourceScript'] = $SourceScript;
                $fieldArray['Location'] = $this->post('Location');
                //print_r($fieldArray);die;
                
                // Save advertiser data in advertiser master
                $AdvertiserID = $this->advertise_model->save_advertiser($Advertiser);

                if ($rawImageBanner) {
                    $imageArr = $this->rawImage_convert($rawImageBanner);
                    
                    $this->load->model('vsocial_model');
                    $this->vsocial_model->save_banner_media($this->UserID,$imageArr['Data']['image_name'], $AdvertiserID, $BlogUniqueID, $SourceID=1,$DeviceID=1);
                    
                    $fieldArray['BlogImage'] = $imageArr['Data']['image_name'];
                    $fieldArray['BannerSize'] = $BannerSize;
                } else {
                    $fieldArray['BlogImage'] = $BlogImage;
                    $fieldArray['BannerSize'] = $BannerSize;
                }
                
                // Create new Ad
                if (empty($BlogID))
                {
                    $fieldArray['Status'] = (isset($Data['Status'])) ? $Data['Status'] : 2;
                    
                    $fieldArray['CreatedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');
                    
                    $BlogID = $this->advertise_model->create_banner($fieldArray);
                    
                    $Return['Message'] = 'Ad has been saved successfully';
                }
                else
                {
                    // Update existing Ad
                    
                    $fieldArray['BlogID'] = $BlogID;
                    $this->advertise_model->update_banner($fieldArray);
                    
                    $Return['Message'] = 'Ad has been saved successfully';
                }
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /*
        Get default banner details for edit mode (CONTENT UPDATE)
    */
    public function getDefaultBannerDetails_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = 'success';
        $Return['ServiceName'] = 'admin_api/advertise/getDefaultBannerDetails';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if (isset($Data) && $Data != NULL) {
            $Data['Type'] = 'defaultbannersmall';
            $Return['Data']['ResultSmall'] = $this->advertise_model->GetDefaultBannerData($Data);
            
            $Data['Type'] = 'defaultbannerlarge';
            $Return['Data']['ResultLarge'] = $this->advertise_model->GetDefaultBannerData($Data);
            
            $Data['Type'] = 'defaultbannerhomesidebar';
            $Return['Data']['ResultHomeSidebar'] = $this->advertise_model->GetDefaultBannerData($Data);
            
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }

        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
        /*Save image from src 
     * @author Sudhir
     */
    public function saveDefaultBanner_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/advertise/saveBanner';
        $Return['Data'] = array();
        $Data = $this->post_data;

        if (!empty($Data))
        {

            //print_r($Data);
            //die;
            $fieldArray = array();

            $StartDate      = (isset($Data['StartDate']) && $Data['StartDate']!= '') ? $Data['StartDate'] : NULL;
            $EndDate        = (isset($Data['EndDate']) && $Data['EndDate']!= '') ? $Data['EndDate'] : NULL;
            
            $BlogUniqueID   = (isset($Data['BlogUniqueID'])) ? $Data['BlogUniqueID'] : ''; // banner module
            $BlogTitle      = (isset($Data['BlogTitle'])) ? $Data['BlogTitle'] : '';
            $Advertiser     = (isset($Data['Advertiser'])) ? $Data['Advertiser'] : '';
            $BannerSource   = (isset($Data['BannerSource'])) ? $Data['BannerSource'] : 1;
            $Duration       = (isset($Data['Duration'])) ? $Data['Duration'] : 5;
            $AdvertiserContact = (isset($Data['AdvertiserContact'])) ? $Data['AdvertiserContact'] : '';
            
            
            $fieldArray['BlogUniqueID'] = $BlogUniqueID;
            $fieldArray['BlogTitle'] = $BlogTitle;
            $fieldArray['Advertiser'] = $Advertiser;
            $fieldArray['BannerSource'] = $BannerSource;
            
            $fieldArray['Duration'] = $Duration;
            $fieldArray['StartDate'] = date('Y-m-d H:i:s', strtotime($StartDate));
            $fieldArray['EndDate'] = date('Y-m-d H:i:s', strtotime($EndDate));
            $fieldArray['AdvertiserContact'] = $AdvertiserContact;
            $fieldArray['CreatedBy'] = $this->UserID;
            
            if(!empty($Data['reqData'])){
                $ReqData = $Data['reqData'];
                
                $BlogID         = (isset($ReqData['BlogID'])) ? $ReqData['BlogID'] : '';
                $BannerSize     = (isset($ReqData['BannerSize'])) ? $ReqData['BannerSize'] : '';
                $BlogDescription= (isset($ReqData['BlogDescription'])) ? $ReqData['BlogDescription'] : '';
                $URL            = (isset($ReqData['URL'])) ? $ReqData['URL'] : NULL;
                $BlogImage      = (isset($ReqData['BlogImage'])) ? $ReqData['BlogImage'] : '';
                $rawImageBanner = (isset($ReqData['rawImageBanner'])) ? $ReqData['rawImageBanner'] : '';
                $SourceScript   = (isset($ReqData['SourceScript'])) ? $ReqData['SourceScript'] : '';
                
                if(empty($rawImageBanner) && empty($BlogImage) && empty($SourceScript)){
                    $Return['ResponseCode'] = 511;
                    $Return['Message'] = 'Please select ad image or enter source script';
                    return $this->response($Return);
                }
                
                $fieldArray['Type'] = 'defaultbannersmall';
                $fieldArray['BlogDescription'] = $BlogDescription;
                $fieldArray['URL'] = $URL;
                $fieldArray['BannerSize'] = $BannerSize;
                $fieldArray['SourceScript'] = $SourceScript;

                if ($rawImageBanner) {
                    $imageArr = $this->rawImage_convert($rawImageBanner);

                    $this->load->model('vsocial_model');
                    // MediaSectionID: 9 for Advertise Default Banner Small
                    $this->vsocial_model->save_default_banner_media($this->UserID,$imageArr['Data']['image_name'], 9, $SourceID=1,$DeviceID=1);

                    $fieldArray['BlogImage'] = $imageArr['Data']['image_name'];
                } else {
                    $fieldArray['BlogImage'] = $BlogImage;

                }

                if (empty($BlogID)){
                    $fieldArray['Status'] = (isset($Data['Status'])) ? $Data['Status'] : 2;

                    $fieldArray['CreatedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');

                    $BlogID = $this->advertise_model->create_blog($fieldArray);
                }
                else {

                    $fieldArray['BlogID'] = $BlogID;
                    $this->advertise_model->update_blog($fieldArray);
                }
            }
            
            if(!empty($Data['reqData1'])){
                $ReqData1 = $Data['reqData1'];
                
                $BlogID1         = (isset($ReqData1['BlogID'])) ? $ReqData1['BlogID'] : '';
                $BannerSize1     = (isset($ReqData1['BannerSize'])) ? $ReqData1['BannerSize'] : '';
                $BlogDescription1= (isset($ReqData1['BlogDescription'])) ? $ReqData1['BlogDescription'] : '';
                $URL1            = (isset($ReqData1['URL'])) ? $ReqData1['URL'] : NULL;
                $BlogImage1      = (isset($ReqData1['BlogImage'])) ? $ReqData1['BlogImage'] : '';
                $rawImageBanner1 = (isset($ReqData1['rawImageBanner'])) ? $ReqData1['rawImageBanner'] : '';
                $SourceScript1   = (isset($ReqData1['SourceScript'])) ? $ReqData1['SourceScript'] : '';
                
                if(empty($rawImageBanner1) && empty($BlogImage1) && empty($SourceScript1)){
                    $Return['ResponseCode'] = 511;
                    $Return['Message'] = 'Please select ad image or enter source script';
                    return $this->response($Return);
                }
                
                $fieldArray['Type'] = 'defaultbannerlarge';
                $fieldArray['BlogDescription'] = $BlogDescription1;
                $fieldArray['URL'] = $URL1;
                $fieldArray['BannerSize'] = $BannerSize1;
                $fieldArray['SourceScript'] = $SourceScript1;

                if ($rawImageBanner1) {
                    $imageArr1 = $this->rawImage_convert($rawImageBanner1);

                    $this->load->model('vsocial_model');
                    // MediaSectionID: 9 for Advertise Default Banner Small
                    $this->vsocial_model->save_default_banner_media($this->UserID,$imageArr1['Data']['image_name'], 8, $SourceID=1,$DeviceID=1);

                    $fieldArray['BlogImage'] = $imageArr1['Data']['image_name'];
                } else {
                    $fieldArray['BlogImage'] = $BlogImage1;

                }

                if (empty($BlogID1)){
                    unset($fieldArray['BlogID']);
                    
                    $fieldArray['Status'] = (isset($Data['Status'])) ? $Data['Status'] : 2;

                    $fieldArray['CreatedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');

                    $BlogID1 = $this->advertise_model->create_blog($fieldArray);
                }
                else {
                    $fieldArray['BlogID'] = $BlogID1;
                    $this->advertise_model->update_blog($fieldArray);
                }
            }
            
            if(!empty($Data['reqData2'])){
                $ReqData2 = $Data['reqData2'];
                
                $BlogID2         = (isset($ReqData2['BlogID'])) ? $ReqData2['BlogID'] : '';
                $BannerSize2     = (isset($ReqData2['BannerSize'])) ? $ReqData2['BannerSize'] : '';
                $BlogDescription2= (isset($ReqData2['BlogDescription'])) ? $ReqData2['BlogDescription'] : '';
                $URL2            = (isset($ReqData2['URL'])) ? $ReqData2['URL'] : NULL;
                $BlogImage2      = (isset($ReqData2['BlogImage'])) ? $ReqData2['BlogImage'] : '';
                $rawImageBanner2 = (isset($ReqData2['rawImageBanner'])) ? $ReqData2['rawImageBanner'] : '';
                $SourceScript2   = (isset($ReqData2['SourceScript'])) ? $ReqData2['SourceScript'] : '';
                
                if(empty($rawImageBanner2) && empty($BlogImage2) && empty($SourceScript2)){
                    $Return['ResponseCode'] = 511;
                    $Return['Message'] = 'Please select ad image or enter source script';
                    return $this->response($Return);
                }
                
                $fieldArray['Type'] = 'defaultbannerhomesidebar';
                $fieldArray['BlogDescription'] = $BlogDescription2;
                $fieldArray['URL'] = $URL2;
                $fieldArray['BannerSize'] = $BannerSize2;
                $fieldArray['SourceScript'] = $SourceScript2;

                if ($rawImageBanner2) {
                    $imageArr2 = $this->rawImage_convert($rawImageBanner2);

                    $this->load->model('vsocial_model');
                    // MediaSectionID: 9 for Advertise Default Banner Small
                    $this->vsocial_model->save_default_banner_media($this->UserID,$imageArr2['Data']['image_name'], 10, $SourceID=1,$DeviceID=1);

                    $fieldArray['BlogImage'] = $imageArr2['Data']['image_name'];
                } else {
                    $fieldArray['BlogImage'] = $BlogImage2;

                }

                if (empty($BlogID2)){
                    unset($fieldArray['BlogID']);
                    
                    $fieldArray['Status'] = (isset($Data['Status'])) ? $Data['Status'] : 2;

                    $fieldArray['CreatedDate'] = getCurrentDate('%Y-%m-%d %H:%i:%s');

                    $BlogID2 = $this->advertise_model->create_blog($fieldArray);
                }
                else {
                    $fieldArray['BlogID'] = $BlogID2;
                    $this->advertise_model->update_blog($fieldArray);
                }
            }
            $Return['Message'] = 'Ad default image has been saved successfully';
            
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
        /*Save image from src 
     * @author Sudhir
     */
    public function rawImage_convert($image)
    {
        $Return['Data'] = array();
        if ($image != '')
        {

            $imageServerPath = IMAGE_ROOT_PATH . 'banner';
            if (!is_dir($imageServerPath))
                @mkdir($imageServerPath, 0777, true);

            $imageServerPathDir = 'banner';
            $this->load->library('image_lib');

            $fileAllowedArray = array('jpg', 'png', 'jpeg', 'PNG', 'JPG', 'JPEG', 'GIF', 'gif');
            $imageData = $image;
            $countOfOccurence = 0;
            $imageExt = '';
            foreach ($fileAllowedArray as $farr)
            {
                if ($countOfOccurence == 0)
                {
                    $imageData = str_replace('data:image/' . $farr . ';base64,', '', $imageData, $countOfOccurence);
                    $imageExt = $farr;
                    $imageExt = 'jpg';
                }
            }

            $file_name = time() . uniqid() . '.' . $imageExt;

            $img = imagecreatefromstring(base64_decode($imageData));
            if ($img != false)
            {
                imagejpeg($img, IMAGE_ROOT_PATH . $imageServerPathDir . '/' . $file_name, 90);
            }
            else
            {
                file_put_contents(IMAGE_ROOT_PATH . $imageServerPathDir . '/' . $file_name, base64_decode($imageData));
            }


            $imgDetails = getimagesize(IMAGE_ROOT_PATH . $imageServerPathDir . '/' . $file_name);

            $imgWidth = $imgDetails[0];
            $imgHeight = $imgDetails[1];
            //create_thumb(IMAGE_ROOT_PATH.$imageServerPathDir.'/'.$file_name, IMAGE_ROOT_PATH.$imageServerPathDir.'/'.$file_name, $imgWidth, $imgHeight, '', '');

            $thumbInfoArray = array();
            /* set default thumb */
            //$thumbInfoArray[] = array('width' => 192, 'height' => 192);
            //$thumbInfoArray[] = array('width' => 36, 'height' => 36);
            /* end set default thumb */
            $thumbnailQuality = '';
            $dpi = '';
            $thumbSize = 0; /* total size in bytes of all thumbnail created */

            $image_path = IMAGE_ROOT_PATH . $imageServerPathDir . '/' . $file_name;
            $temp_file = IMAGE_ROOT_PATH . $imageServerPathDir . '/' . $file_name;

            if (strtolower(IMAGE_SERVER) == 'remote')
            {//if upload on s3 is enabled 
                $imageServerPathRemote = 'upload/banner/' . $file_name;
                 $this->load->library('S3');
                $s3 = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY);
                $is_s3_upload = $s3->putObjectFile($image_path, BUCKET, $imageServerPathRemote, S3::ACL_PUBLIC_READ);
                
                @unlink($temp_file);
                if ($is_s3_upload)
                {
                    
                }
            }

            $this->image_lib->clear();
            /* Retrun Data */
            $Return['Data'] = array(
                'image_uri' => $imageServerPath . $file_name,
                'image_name' => $file_name,
                'success' => 1,
                'size' => 0
            );
        }
        return $Return;
    }
        /*Save image from src 
     * @author Sudhir
     */
    public function banner_status_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/advertise/banner_status';
        $Return['Data'] = array();
        $Data = $this->post_data;
        //Debug($Data);
        if (isset($Data) && $Data != NULL) {
            $result = $this->advertise_model->update_blog(array('BlogID' => $Data['BlogID'], 'Status' =>$Data['Status']));
            if($Data['Status'] == 3){
                $Return['Message'] = 'Ad has been deleted successfully';
            } else {
                $Return['Message'] = 'Ad status has been changed successfully';
            }
        } else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
        /*Save image from src 
     * @author Sudhir
     */
    public function getAdvertiserList_post(){
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/advertise/getAdvertiserList';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        if (isset($Data) && $Data != NULL) {
            
            $result = $this->advertise_model->get_advertiser($Data);
            
            $Return['Data'] = $result;
        }else {
            /* Error - Invalid JSON format */
            $Return['ResponseCode'] = '519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
}

//End of file users.php