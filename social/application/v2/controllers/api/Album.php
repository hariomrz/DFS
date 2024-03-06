<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of Album
 * @copyright (c) 2015, Vinfotech
 * @version 1.0
 */
class Album extends Common_API_Controller {

    private $album = array();
    private $media = array();
    public $DefaultAlbum = array(DEFAULT_PROFILE_ALBUM, DEFAULT_PROFILECOVER_ALBUM, DEFAULT_WALL_ALBUM, DEFAULT_FILE_ALBUM);

    public function __construct() {
        parent::__construct();
        $this->load->model(array('album/album_model', 'activity/activity_model'));
        $this->lang->load('album');
        $this->load->helper('location');
    }

    /**
     * [use to update multiple media to album post]
     * @param guid AlbumGUID
     * @return [type] [JSON Object]
     */
    function update_album_activity_post() {
        $Return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/album/update_album_activity') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            if (!empty($data['Media'])) {

                $album = get_detail_by_guid($data['AlbumGUID'], 13, "AlbumID, ModuleID, ActivityID", 2);
                $album_id = $album['AlbumID'];
                $module_id = $album['ModuleID'];
                $activity_id = $album['ActivityID'];


                //Add album if not exists ends
                $response = array();
                $Param = array('AlbumGUID' => $data['AlbumGUID'], 'count' => sizeof($data['Media']));
                $this->activity_model->update_album_activity($album_id, $Param);



                $Return['Data'] = $response;
                $Return['Message'] = lang('media_save_success');
            } else {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('media_error');
            }
        }
        $this->response($Return);
    }

    /**
     * Will have grid list of Albums (arranged recent to past).     
     * @param String UserGUID | Get album list for this User GUID 
     * @param String AlbumType | Valid value are:- PHOTO - VIDEO
     * @param Integer PageNo | Initial value should be 1, then paginate accordingly.
     * @param Integer PageSize | Defaults to the constant set, and use this value if present
     * @param String SortBy | AlbumName, CreatedDate Default: CreatedDate
     * @param String OrderBy | ASC, DESC Default: DESC
     * @return array api response
     */
    public function list_post() {
        $Return = $this->return;
        if ($this->form_validation->run('api/album/list') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            $data = $this->post_data;
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 0;
            $page_no = ($page_no && $page_no != 0) ? ($page_no - 1) : 0;
            $page_size = (isset($data['PageSize'])) ? $data['PageSize'] : PAGE_SIZE;
            $album_type = '';
            $show_private = '';
            $return_arr = array();
            $sort_by = 'CreatedDate';
            $order_by = 'DESC';
            if (isset($data['SortBy']) && $data['SortBy'] != '') {
                $sort_by = $data['SortBy'];
            }
            if (isset($data['OrderBy']) && $data['OrderBy'] != '') {
                $order_by = $data['OrderBy'];
            }

            $module_entity_GUID = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : 0;
            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 0;
            $module_entity_id = get_detail_by_guid($module_entity_GUID, $module_id, "", 1);
            $album_list = $this->album_model->album_list($this->UserID, $module_id, $module_entity_id, $album_type, $page_no, $page_size, $sort_by, $order_by, $show_private);
            $Return['TotalRecords'] = $this->album_model->album_list($this->UserID, $module_id, $module_entity_id, $album_type, '', '', $sort_by, $order_by, $show_private, TRUE);
            foreach ($album_list as $albums) {
                $albums['Location'] = array();
                if (!empty($albums['LocationID'])) {
                    $albums['Location'] = get_location_by_id($albums['LocationID']);
                }

                if (!empty($albums['CoverMedia'])) {
                    if (!empty(preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $albums['CoverMedia'], $matches)))
                        $albums['CoverMedia'] = 'http://img.youtube.com/vi/' . $matches[1] . '/0.jpg';
                }else {
                    $albums['Location'] = array();
                }


                $return_arr[] = $albums;
            }
            $Return['Data'] = $return_arr;
        }

        $this->response($Return);
    }

    function is_default_album_name($album_name) {
        if (in_array(strtolower($album_name), $this->DefaultAlbum)) {
            $this->form_validation->set_message('is_default_album_name', lang('is_default_album_name'));
            return FALSE;
        } else
            return TRUE;
    }

    /**
     * Call back function for check uniqeness in album name
     * @return boolean
     */
    function is_unique_album_name($album_name) {
        $albumTypeArr = array('PHOTO', 'VIDEO');
        $album_type = $this->post('AlbumType');
        $module_id = $this->post('ModuleID');
        $module_entity_GUID = $this->post('ModuleEntityGUID');
        $module_entity_id = get_detail_by_guid($module_entity_GUID, $module_id, "", 1);
        $album_type = (strtoupper($album_type) == 'PHOTO' OR strtoupper($album_type) == 'VIDEO') ? strtoupper($album_type) : NULL;
        $is_unique = $this->album_model->album_name_exist($module_id, $module_entity_id, $album_name, $album_type);

        if (!in_array($album_type, $albumTypeArr)) {
            $this->form_validation->set_message('is_unique_album_name', "Invalid album type.");
            return FALSE;
        }

        if ($is_unique === FALSE) {
            $this->form_validation->set_message('is_unique_album_name', lang('is_unique_album_name'));
        } else if (in_array(strtolower($album_name), $this->DefaultAlbum)) {
            $this->form_validation->set_message('is_unique_album_name', lang('is_unique_album_name'));
            return FALSE;
        }
        //add a rule for edit case
        if (isset($this->album['AlbumName']) && $this->album['AlbumName'] == $album_name) {
            $is_unique = TRUE;
        }

        return $is_unique;
    }

    /**
     * Create album     
     * @param String AlbumName | Unique Album Name
     * @param String Description | Album Description
     * @param Integer Visibility | Valid value are: 1 - Public 2 - Teammates
     * @param string AlbumType
     * @param Integer ModuleEntityID
     * @param Integer ModuleID
     * @return array api response
     */
    public function add_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $Return = $this->return;
        if ($this->form_validation->run('api/album/add') == FALSE) { // Check for empty request
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $data = $this->post_data;

            if (in_array(strtolower($data['AlbumName']), $this->DefaultAlbum)) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('invalid_album_name');
                $this->response($Return);
            }

            //if $album type
            $album_type = $data['AlbumType'];
            $album_type = strtoupper($album_type);
            $album_type = !empty($album_type) ? $album_type : 'PHOTO';

            $module_id = $data['ModuleID'];
            $location_id = null;

            if (!empty($data['Location'])) {
                $post_location = $data['Location'];
                $insert_location = array(
                    'LocationGUID' => get_guid(),
                    'UniqueID' => isset($post_location['UniqueID']) ? $post_location['UniqueID'] : "",
                    'FormattedAddress' => isset($post_location['FormattedAddress']) ? $post_location['FormattedAddress'] : "",
                    'Latitude' => isset($post_location['Latitude']) ? $post_location['Latitude'] : "",
                    'Longitude' => isset($post_location['Longitude']) ? $post_location['Longitude'] : "",
                    'StreetNumber' => isset($post_location['StreetNumber']) ? $post_location['StreetNumber'] : "",
                    'Route' => isset($post_location['Route']) ? $post_location['Route'] : "",
                    'City' => isset($post_location['City']) ? $post_location['City'] : "",
                    'State' => isset($post_location['State']) ? $post_location['State'] : "",
                    'Country' => isset($post_location['Country']) ? $post_location['Country'] : "",
                    'PostalCode' => isset($post_location['PostalCode']) ? $post_location['PostalCode'] : "",
                    'StateCode' => isset($post_location['StateCode']) ? $post_location['StateCode'] : "",
                    'CountryCode' => isset($post_location['CountryCode']) ? $post_location['CountryCode'] : "",
                );
                $location = insert_location($insert_location);
                $location_id = $location['LocationID'];
            }

            $video_count = 0;
            $photo_count = 0;
            $media_id = 0;
            $media_count = 0;

            $visibility = isset($data['Visibility']) ? $data['Visibility'] : 1;
            $commentable = isset($data['Commentable']) ? $data['Commentable'] : 1;

            if (!empty(array_filter($data['Media']))) {
                $media_commentable = 1;
                $index = '';
                if ($commentable == 0) {
                    $media_commentable = 0;
                }
                foreach ($data['Media'] as $key => $temp_media) {
                    //check if media is youtube video
                    $temp_media['IsCommentable'] = $media_commentable;
                    $media_count++;
                    if (strtolower($temp_media['MediaType']) == 'youtube' && $temp_media['Url'] != '') {
                        $data['Youtube'][] = $temp_media;
                    } else {
                        //other media
                        $other_media[] = $temp_media;
                        if (strtolower($temp_media['MediaType']) == 'video') {
                            $video_count++;
                        } else {
                            $photo_count++;
                        }
                    }
                    //var_dump($temp_media['isCoverPic']);
                    if (!empty($temp_media['isCoverPic']) && $temp_media['isCoverPic'] == 1) {
                        $index = $key;
                    }
                }//end foreach
                if (empty($index)) {
                    $index = count($data['Media']) - 1;
                }
                $last_media = $data['Media'][$index];
                $cover = $this->album_model->get_row('MediaID', MEDIA, "MediaGUID='" . $last_media['MediaGUID'] . "'");
                $media_id = isset($cover['MediaID']) ? $cover['MediaID'] : 0;
            }

            if ($module_id == 3) {
                $module_entity_id = $this->UserID;
            } else {
                $module_entity_id = get_detail_by_guid($data['ModuleEntityGUID'], $module_id, "", 1);
            }

            //prepare album data array
            $album = array(
                'AlbumGUID' => isset($data['AlbumGUID']) ? $data['AlbumGUID'] : "",
                'AlbumName' => isset($data['AlbumName']) ? $data['AlbumName'] : "",
                'UserID' => $this->UserID,
                'ModuleID' => $module_id,
                'ModuleEntityID' => $module_entity_id,
                'Description' => isset($data['Description']) ? $data['Description'] : "",
                'Visibility' => $visibility,
                'AlbumType' => $album_type,
                'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                'PhotoCount' => $photo_count,
                'VideoCount' => $video_count,
                'MediaCount' => $media_count,
                'LocationID' => $location_id,
                'MediaID' => $media_id,
                'IsEditable' => 1
            );

            //save album
            $album = $this->album_model->save_album($album);

            if ($album) {
                //check for media
                if (!empty($other_media)) {
                    $this->album_model->add_album_media($other_media, $album['AlbumID'], $module_id);
                }

                //save youtube medias
                if (!empty($data['Youtube'])) {
                    $media_id = $this->album_model->add_album_youtube_media($data['Youtube'], $album['AlbumID'], $module_id);
                    $update_data = array('AlbumGUID' => $album['AlbumGUID'], 'MediaID' => $media_id);
                    $this->album_model->save_album($update_data);
                }
                $response = $this->album_model->get_album_by_guid($album['AlbumGUID']);

                $response['Commentable'] = $commentable;
                $insert_location['LocationID'] = $location_id;
                $response['Location'] = $insert_location;
                // Add Activity
                $Param = array('AlbumGUID' => $album['AlbumGUID'], 'count' => $media_count);
                // if($visibility==2){$visibility=3;}/*for teammate's privacy*/

                if ($media_count == 0) {
                    $visibility = 4;
                }
                if ($media_count > 0) {
                    $module_entity_owner = 0;
                    if ($module_id == 18) {
                        $module_entity_owner = 1;
                    }
                    $activity_id = $this->activity_model->addActivity($module_id, $module_entity_id, 5, $this->UserID, 0, '', $commentable, $visibility, $Param, 1, $module_entity_owner);
                    $this->album_model->update_album_activity_id($album['AlbumGUID'], $activity_id);
                    $response['ActivityID'] = $activity_id;
                } else {
                    $response['ActivityID'] = 0;
                }
                $Return['Data'] = $response;
                $Return['Message'] = lang('album_save_success');
            } else {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('album_error');
            }
        }
        $this->response($Return);
    }

    /**
     * edit album     
     * @param String AlbumName | Unique Album Name
     * @param String Description | Album Description
     * @param Integer Visibility | Valid value are: 1 - Public 2 - Teammates
     * @param string AlbumType
     * @param Integer ModuleEntityID
     * @param Integer ModuleID
     * @return array api response
     */
    public function edit_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $Return = $this->return;
        if ($this->form_validation->run('api/album/edit') == FALSE) { // Check for empty request
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $data = $this->post_data;

            if (in_array(strtolower($data['AlbumName']), $this->DefaultAlbum)) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('invalid_album_name');
                $this->response($Return);
            }

            //if $album type
            $album_type = $data['AlbumType'];
            $album_type = strtoupper($album_type);
            $album_type = !empty($album_type) ? $album_type : 'PHOTO';

            $module_id = $data['ModuleID'];
            $location_id = null;
            if (!empty($data['Location'])) {
                $post_location = $data['Location'];
                $insert_location = array(
                    'LocationGUID' => get_guid(),
                    'UniqueID' => isset($post_location['UniqueID']) ? $post_location['UniqueID'] : "",
                    'FormattedAddress' => isset($post_location['FormattedAddress']) ? $post_location['FormattedAddress'] : "",
                    'Latitude' => isset($post_location['Latitude']) ? $post_location['Latitude'] : "",
                    'Longitude' => isset($post_location['Longitude']) ? $post_location['Longitude'] : "",
                    'StreetNumber' => isset($post_location['StreetNumber']) ? $post_location['StreetNumber'] : "",
                    'Route' => isset($post_location['Route']) ? $post_location['Route'] : "",
                    'City' => isset($post_location['City']) ? $post_location['City'] : "",
                    'State' => isset($post_location['State']) ? $post_location['State'] : "",
                    'Country' => isset($post_location['Country']) ? $post_location['Country'] : "",
                    'PostalCode' => isset($post_location['PostalCode']) ? $post_location['PostalCode'] : "",
                    'StateCode' => isset($post_location['StateCode']) ? $post_location['StateCode'] : "",
                    'CountryCode' => isset($post_location['CountryCode']) ? $post_location['CountryCode'] : "",
                );
                $location = insert_location($insert_location);
                $location_id = $location['LocationID'];
            }
            $video_count = 0;
            $photo_count = 0;
            $media_count = 0;
            $deleted_media_count = 0  ;
            $new_media_count = 0;
            $media_id = 0;

            $visibility = isset($data['Visibility']) ? $data['Visibility'] : 1;
            $commentable = isset($data['Commentable']) ? $data['Commentable'] : 1;
            $is_cover_selected = FALSE;
            $album_cover_media = get_detail_by_guid($data['AlbumGUID'], 13, "MediaID", 1);
            $all_media = array();
            $deleted_media = array();
            
            $isMediaEmpty = empty($data['Media'])?true:false;
            if (!$isMediaEmpty) {
                $media_commentable = 1;
                $index = '';
                if ($commentable == 0) {
                    $media_commentable = 0;
                }

                foreach ($data['Media'] as $key => $temp_media) {
                    $media_count++;
                    //check if media is youtube video                    
                    if (strtolower($temp_media['MediaType']) == 'youtube' && $temp_media['Url'] != '') {
                        $data['Youtube'][] = $temp_media;
                        $video_count++;
                        $new_media_count++;
                        $temp_media['IsCommentable'] = $media_commentable;
                    } else {
                        //other media                        
                        if (!empty($temp_media['MediaGUID'])) {
                            $media_data = get_detail_by_guid($temp_media['MediaGUID'], 21, "MediaSectionReferenceID, IsCommentable,MediaID", 2);
                            $temp_media['IsCommentable'] = $media_data['IsCommentable'];
                            if (empty($media_data['MediaSectionReferenceID'])) {
                                $new_media_count++;
                                $temp_media['IsCommentable'] = $media_commentable;
                            }

                            $all_media[] = $media_data['MediaID'];
                        }
                        if (strtolower($temp_media['MediaType']) == 'video') {
                            $video_count++;
                        } else {
                            $photo_count++;
                        }
                        $other_media[] = $temp_media;
                    }
                    if (!empty($temp_media['isCoverPic']) && $temp_media['isCoverPic'] == 1) {
                        $index = $key;
                        $is_cover_selected = TRUE;
                    }
                }//end foreach
                if (empty($index)) {
                    $index = count($data['Media']) - 1;
                }
                $last_media = $data['Media'][$index];
                $cover = $this->album_model->get_row('MediaID', MEDIA, "MediaGUID='" . $last_media['MediaGUID'] . "'");
                // print_r($cover);
                $media_id = isset($cover['MediaID']) ? $cover['MediaID'] : 0;
            }
            
            if (!empty($data['DeletedMedia'])) {
                foreach ($data['DeletedMedia'] as $key => $temp_media) {
                    $deleted_media_count++;
                    $deleted_media[] = $temp_media['MediaGUID'];
                }//end foreach
            }
            //total media count
            //$media_count = $video_count+$photo_count;
            if ($module_id == 3) {
                $module_entity_id = $this->UserID;
            } else {
                $module_entity_id = get_detail_by_guid($data['ModuleEntityGUID'], $module_id, "", 1);
            }

            $album = array(
                'AlbumGUID' => isset($data['AlbumGUID']) ? $data['AlbumGUID'] : "",
                'AlbumName' => isset($data['AlbumName']) ? $data['AlbumName'] : "",
                'UserID' => $this->UserID,
                'ModuleID' => $module_id,
                'ModuleEntityID' => $module_entity_id,
                'Description' => isset($data['Description']) ? $data['Description'] : "",
                'Visibility' => $visibility,
                'AlbumType' => isset($data['AlbumType']) ? $album_type : 'PHOTO',
                'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                'PhotoCount' => $photo_count,
                'VideoCount' => $video_count,
                'MediaCount' => $media_count,
                'IsEditable' => 1,
                'LocationID' => $location_id,
                    //'MediaID'       => $media_id
            );
            $is_album_has_cover = $this->album_model->check_cover_exist($data['AlbumGUID']);

            if ($is_cover_selected) {
                $album['MediaID'] = $media_id;
            } else if (!$is_album_has_cover) {
                $album['MediaID'] = $media_id;
            }

            //check if album cover is deleted 
            if (!in_array($album_cover_media, $all_media) && !$is_cover_selected) {
                $album['MediaID'] = $media_id;
            }
            if($isMediaEmpty && empty($data['DeletedMedia'])){
                unset($album['MediaID']);
            }
            
            //save album
            $album = $this->album_model->save_album($album);

            if ($album) {
                //$this->album_model->set_media_status_by_album_id($album['AlbumID'], $status_id = 3);
                if(!empty($deleted_media)){
                    $this->album_model->set_media_status_by_media_guid($album['AlbumID'], $deleted_media,$status_id = 3);
                }
                
                //check for media
                if (!empty($other_media)) {
                    $this->album_model->add_album_media($other_media, $album['AlbumID'], $module_id);
                }

                //save youtube medias
                if (!empty($data['Youtube'])) {
                    $this->album_model->add_album_youtube_media($data['Youtube'], $album['AlbumID'], $module_id);
                }
                
                $media_count = $this->album_model->get_album_media($album['AlbumID'], '', '', 'CreatedDate', 'DESC', 0, TRUE);
                $this->album_model->update_album_media_count($album['AlbumGUID'], $media_count,'update');
                if ($media_count == 0) {
                    $visibility = 4;
                }

                $response = $this->album_model->get_album_by_guid($album['AlbumGUID']);

                $response['Commentable'] = $commentable;
                $insert_location['LocationID'] = $location_id;
                $response['Location'] = $insert_location;
                // Add Activity               

                $activity_id = $response['ActivityID'];
                if ($media_count > 0) {
                    if ($activity_id == 0) {
                        $param = array('AlbumGUID' => $data['AlbumGUID']);
                        $activity_id = $this->activity_model->addActivity(3, $this->UserID, 5, $this->UserID, 0, '', 1, 1, $param);
                        $this->album_model->update_album_activity_id($data['AlbumGUID'], $activity_id);
                    }

                    $activity_type_id = 5;
                    if ($new_media_count > 0) {
                        $activity_type_id = 6;
                    }
                    $param = array('AlbumGUID' => $album['AlbumGUID'], 'count' => $media_count);
                    $this->activity_model->update_album_activity($album['AlbumID'], $param, $commentable, $visibility, $activity_type_id);
                }

                //echo $activity_type_id;die;
                $Return['Data'] = $response;
                $Return['Message'] = lang('album_updated_success');
            } else {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('album_error');
            }
        }
        $this->response($Return);
    }

    /**
     * Delete an album     
     * @param String AlbumGUID | Required in case of edit album.
     * @return array api response
     */
    public function delete_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $Return = $this->return;
        if ($this->form_validation->run('api/album/delete') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            //check user have permission to media and album
            $this->check_user_have_access();

            $this->album_model->delete_album($this->album['AlbumID']);

            $this->load->model('activity_model');
            $this->activity_model->removeActivity($this->album['ActivityID'], $this->UserID);

            $Return['Message'] = lang('album_delete_success');
        }
        $this->response($Return);
    }

    /**
     * Provide album details with media     
     * @param String AlbumGUID | Album GUID  
     * @param Integer PageNo | Initial value should be 1, then paginate accordingly.
     * @param Integer PageSize | Defaults to the constant set, and use this value if present
     * @param String SortBy | AlbumName, CreatedDate Default: CreatedDate
     * @param String OrderBy | ASC, DESC Default: DESC
     * @return array api response
     */
    public function details_post() {
        $user_id = $this->UserID;
        $Return = $this->return;
        if ($this->form_validation->run('api/album/details') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            //check user have permission to media and album

            if (isset($this->album['StatusID']) && $this->album['StatusID'] == 3) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('album_deleted');
            } else {
                $data = $this->post_data;
                $album_detail = $this->album_model->get_album_by_guid($this->album['AlbumGUID'], $this->UserID);
                $user_detail = get_detail_by_id($album_detail['UserID'], 3, 'FirstName,LastName', 2);
                $user_name = ucwords($user_detail['FirstName'] . ' ' . $user_detail['LastName']);
                $user_url = get_entity_url($album_detail['UserID']);
                $location = array();
                if (!empty($album_detail['LocationID'])) {
                    $location = get_location_by_id($album_detail['LocationID']);
                }
                $activity_guid = get_detail_by_id($album_detail['ActivityID'], 19, 'ActivityGUID', 1);
                $album = array(
                    'AlbumGUID' => $album_detail['AlbumGUID'],
                    'AlbumName' => $album_detail['AlbumName'],
                    'AlbumType' => $album_detail['AlbumType'],
                    'Description' => $album_detail['Description'],
                    'Visibility' => $album_detail['Visibility'],
                    'MediaCount' => $album_detail['MediaCount'],
                    'CoverMedia' => $album_detail['CoverMedia'],
                    'IsEditable' => $album_detail['IsEditable'],
                    'ModifiedDate' => $album_detail['ModifiedDate'],
                    'ProfileUrl' => $user_url,
                    'UserName' => $user_name,
                    'UserGUID' => $album_detail['UserGUID'],
                    'Location' => $location,
                    'ActivityGUID' => $activity_guid
                );

                $this->load->model(array('users/friend_model'));
                $activity_details = $this->activity_model->getSingleUserActivity($user_id, $album_detail['ActivityID']);
                if (isset($activity_details[0])) {
                    $activity_details = $activity_details[0];
                    $album['NoOfComments'] = $activity_details['NoOfComments'];
                    $album['NoOfLikes'] = $activity_details['NoOfLikes'];
                    $album['ActivityGUID'] = '';
                    $album['IsLike'] = $activity_details['IsLike'];
                    //$album['LikeName']      = $activity_details['LikeName'];
                    $album['Comments'] = $activity_details['Comments'];
                    $album['CommentsAllowed'] = $activity_details['CommentsAllowed'];
                    $album['ShareAllowed'] = $activity_details['ShareAllowed'];
                } else {
                    $album['NoOfComments'] = 0;
                    $album['NoOfLikes'] = 0;
                    $album['ActivityGUID'] = '';
                    $album['IsLike'] = 0;
                    //$album['LikeName']      = '';
                    $album['Comments'] = array();
                    $album['CommentsAllowed'] = 0;
                    $album['ShareAllowed'] = 0;
                }
                $album['IsOwner'] = 0;
                if ($user_id == $album_detail['UserID']) {
                    $album['IsOwner'] = 1;
                }
                $Return['Data'] = $album;
                $Return['LoggedInProfilePicture'] = $this->LoggedInProfilePicture;
                $Return['LoggedInName'] = $this->LoggedInName;
            }
        }
        $this->response($Return);
    }

    /**
     * Provide media list     
     * @param String AlbumGUID | Album GUID  
     * @param Integer PageNo | Initial value should be 1, then paginate accordingly.
     * @param Integer PageSize | Defaults to the constant set, and use this value if present
     * @param String SortBy | AlbumName, CreatedDate Default: CreatedDate
     * @param String OrderBy | ASC, DESC Default: DESC
     * @return array api response
     */
    public function list_media_post() {
        $Return = $this->return;
        if ($this->form_validation->run('api/album/details') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            //check user have permission to media and album

            if (isset($this->album['StatusID']) && $this->album['StatusID'] == 3) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('album_deleted');
            } else {
                $data = $this->post_data;
                $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
                $page_no = ($page_no && $page_no != 0) ? ($page_no - 1) : 0;
                $page_size = (isset($data['PageSize']) && $data['PageSize'] != "") ? $data['PageSize'] : PAGE_SIZE;
                $sort_by = (isset($data['SortBy']) && $data['SortBy'] != "") ? strtoupper($data['SortBy']) : 'CreatedDate';
                $order_by = (isset($data['OrderBy']) && $data['OrderBy'] != "") ? strtoupper($data['OrderBy']) : 'DESC';
                $media_guid = isset($data['MediaGUID']) ? $data['MediaGUID'] : '';
                $cover_id = (isset($this->album['MediaID']) && !empty($this->album['MediaID'])) ? $this->album['MediaID'] : 0;
                $is_edit = isset($data['IsEdit']) ? $data['IsEdit'] : FALSE;

                if ($is_edit) {
                    $Return['Data'] = $this->album_model->get_album_media($this->album['AlbumID'], '', '', $sort_by, $order_by, $cover_id, FALSE, $this->UserID, $media_guid, 'edit');
                } else {
                    $Return['Data'] = $this->album_model->get_album_media($this->album['AlbumID'], $page_no, $page_size, $sort_by, $order_by, $cover_id, FALSE, $this->UserID, $media_guid);
                }
                $Return['MediaCount'] = $this->album_model->get_album_media($this->album['AlbumID'], '', '', $sort_by, $order_by, $cover_id, TRUE);

                /* edited by gautam - starts */
                if ($this->IsApp == 1) {
                    $Return['AlbumData'] = $this->album_model->get_row("Description,Visibility", "Albums", "AlbumGUID='" . $data['AlbumGUID'] . "'");
                }
            }
        }
        $this->response($Return);
    }

    function add_media_post() {
        $Return = $this->return;
        if ($this->form_validation->run('api/album/add_media') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            $data = $this->post_data;
            $user_id = $this->UserID;
            if (!empty($data['Media'])) {
                $video_count = 0;
                $photo_count = 0;
                $media_count = 0;
                $media_array = $data['Media'];
                foreach ($media_array as $temp_media) {
                    $media_count++;
                    $media_type = isset($temp_media['MediaType']) ? $temp_media['MediaType'] : '';
                    //check if media is youtube video
                    if (strtolower($media_type) == 'youtube' && $temp_media['Url'] != '') {
                        $data['Youtube'][] = $temp_media;
                        $video_count++;
                    } else {
                        //other media
                        $other_media[] = $temp_media;
                        if (strtolower($media_type) == 'video') {
                            $video_count++;
                        } else {
                            $photo_count++;
                        }
                    }
                }

                if (!empty($temp_media)) {
                    $album = get_detail_by_guid($data['AlbumGUID'], 13, "AlbumID, ModuleID, ModuleEntityID, ActivityID", 2);

                    $album_id = $album['AlbumID'];
                    $module_id = $album['ModuleID'];

                    $module_entity_id = ($album['ModuleEntityID']) ? $album['ModuleEntityID'] : $user_id;

                    //Add album if not exists starts
                    $activity_id = $album['ActivityID'];
                    if ($activity_id == 0) {
                        $param = array('AlbumGUID' => $data['AlbumGUID']);
                        $activity_id = $this->activity_model->addActivity($module_id, $module_entity_id, 5, $user_id, 0, '', 1, 1, $param);
                        $this->album_model->update_album_activity_id($data['AlbumGUID'], $activity_id);
                    }

                    //check for media
                    if (!empty($other_media)) {
                        $this->album_model->add_album_media($other_media, $album_id, $module_id);
                    }

                    //save youtube medias
                    if (!empty($data['Youtube'])) {
                        $this->album_model->add_album_youtube_media($data['Youtube'], $album_id, $module_id);
                    }

                    $this->album_model->update_album_media_count($this->album['AlbumGUID'], $media_count);

                    //Add album if not exists ends
                    $response = array();
                    $Param = array('AlbumGUID' => $data['AlbumGUID'], 'count' => $media_count);
                    $this->activity_model->update_album_activity($album_id, $Param);

                    $Return['Data'] = $response;
                    $Return['Message'] = lang('media_save_success');
                }
            } else {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('media_error');
            }
        }
        $this->response($Return);
    }

    /**
     * Delete album Media. Allow multiple media selection for deletion

     * @param String AlbumGUID | Album GUID  
     * @param Array Media | Array of MediaGuid to delete
     * @return array api response
     */
    public function delete_media_post() {
        $Return = $this->return;
        if ($this->form_validation->run('api/album/delete_media') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            //check user have permission to media and album
            $this->check_user_have_access();

            $media_list = $this->post('Media');

            if (isset($media_list) && is_array($media_list) && !empty($media_list)) {
                $cover_deleted = FALSE;
                $delete_count = 0;
                foreach ($media_list as $temp) {
                    $media = $this->album_model->get_row('MediaID, StatusID', MEDIA, "MediaGUID='" . $temp['MediaGUID'] . "'");
                    if ($media['StatusID'] == 3) {
                        continue;
                    }
                    $media = array(
                        'MediaID' => $media['MediaID'],
                        'StatusID' => 3,
                    );
                    $this->album_model->update_media($media, $this->album['AlbumID']);

                    if ($media['MediaID'] == $this->album['MediaID']) {
                        $cover_deleted = TRUE;
                    }
                    $delete_count++;
                    //echo $cover_deleted;
                }

                if ($cover_deleted == TRUE) {
                    $media = $this->album_model->get_album_last_media($this->album['AlbumID']);
                    $album = array(
                        'MediaID' => isset($media['MediaID']) ? $media['MediaID'] : 0,
                        'AlbumGUID' => $this->album['AlbumGUID'],
                    );
                    $this->album_model->save_album($album);
                }

                /* $album_media = $this->album_model->get_row('count(MediaID) as total_media', MEDIA, "AlbumID='" . $this->album['AlbumID'] . "' and StatusID = 2");
                  $album_media_count = $album_media['total_media'];
                  if(!isset($album_media_count) || $album_media_count == ""){
                  $album_media_count = 0;
                  }

                  $album = array(
                  'MediaCount' => $album_media_count,
                  'AlbumGUID' => $this->album['AlbumGUID'],
                  );
                  $this->album_model->save_album($album); */
                $this->album_model->update_album_media_count($this->album['AlbumGUID'], $delete_count, 'reduce');
            } else {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('media_required');
            }
        }
        $this->response($Return);
    }

    /**
     * [set_privacy_post To change privacy of album]
     * @param guid AlbumGUID
     * @param int Privacy
     * @return [type] [JSON Object]
     */
    public function set_privacy_post() {
        $UserID = $this->UserID;
        $validation_rule = $this->form_validation->_config_rules['api/album/set_privacy'];
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $Data = $this->post_data;
            $AlbumGUID = $Data['AlbumGUID'];
            $Privacy = $Data['Privacy'];
            $Return = $this->album_model->set_privacy($UserID, $AlbumGUID, $Privacy);
            $Return['Data'] = $Return['Data'];
            $Return['ResponseCode'] = $Return['ResponseCode'];
        }
        $this->response($this->return);
    }

    /**
     * Provide the album Media details with total likes, total comments     
     * @param String AlbumGUID | Album GUID  
     * @param String MediaGUID | Get the detail for this Media GUID  
     * @return array api response
     */
    public function media_details_post() {
        $validation_rule = $this->form_validation->_config_rules['api/album/media_details'];
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) { // Check for empty request
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            //check user have permission to media and album
            $this->check_user_have_access();
            if (isset($this->album['StatusID']) && $this->album['StatusID'] == 3) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('media_deleted');
            } else {
                $media = array(
                    'MediaGUID' => $this->media['MediaGUID'],
                    'ImageName' => $this->media['ImageName'],
                    'ImageUrl' => $this->media['ImageUrl'],
                    'NoOfComments' => empty($this->media['NoOfComments']) ? 0 : $this->media['NoOfComments'],
                    'NoOfLikes' => empty($this->media['NoOfLikes']) ? 0 : $this->media['NoOfLikes'],
                );
                $Return['Data'] = $this->album_model->get_media_by_guid($this->media['MediaGUID']);
                $Return['Data']['IsLike'] = $this->activity_model->checkLike($Return['Data']['MediaGUID'], 'MEDIA', $this->UserID);
            }
        }
        $this->response($this->return);
    }

    /**
     * Update Media Caption     
     * @param String AlbumGUID | Album GUID  
     * @param String MediaGUID | Get the detail for this Media GUID  
     * @param String Media Caption
     * @return array api response
     */
    public function update_media_caption_post() {
        $Return = $this->return;
        $validation_rule = $this->form_validation->_config_rules['api/album/update_media_caption'];
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) { // Check for empty request
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            //check user have permission to media and album

            $media = array(
                'MediaGUID' => $this->media['MediaGUID'],
                'Caption' => $this->post_data['Caption'],
            );
            $album_id = get_detail_by_guid($this->media['MediaGUID'], 21, 'AlbumID', 1);
            $this->album_model->update_media($media, $album_id);
        }
        $this->response($Return);
    }

    /**
     * Set new Cover media for album.     
     * @param String AlbumGUID | Album GUID  
     * @param String MediaGUID | Get the detail for this Media GUID  
     * @return array api response
     */
    public function set_cover_media_post() {
        $validation_rule = $this->form_validation->_config_rules['api/album/set_cover_media'];
        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) { // Check for empty request
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            //check user have permission to media and album
            $this->check_user_have_access();

            $media = array(
                'MediaID' => $this->media['MediaID'],
                'AlbumGUID' => $this->album['AlbumGUID'],
            );
            $this->album_model->save_album($media);
        }
        $this->response($this->return);
    }

    /**
     * check user have access or not to media or album
     * @return boolean
     */
    private function check_user_have_access() {
        $have_access = FALSE;
        $user_id = $this->UserID;
        /* if (empty($this->media) === FALSE && $this->UserID != $this->media['UserID'] && $this->album['Visibility'] != 1)
          {
          $have_access = FALSE;
          }

          if (empty($this->album) === FALSE && $this->UserID != $this->album['UserID'] && $this->album['Visibility'] != 1)
          {
          $have_access = FALSE;
          } */

        //For Team Module check user permissiosn
        if (!empty($this->album)) {

            $module_id = $this->album['ModuleID'];
            $module_entity_id = $this->album['ModuleEntityID'];
            switch ($module_id) {
                case '1':
                    $permissions = checkPermission($user_id, $module_id, $module_entity_id, 'IsOwner', 3, $user_id);
                    if ($permissions) {
                        $have_access = TRUE;
                    }
                    break;
                case '14':
                    $this->load->model('events/event_model');
                    $permissions = $this->event_model->is_admin($module_entity_id, $user_id);
                    if ($permissions) {
                        $have_access = TRUE;
                    }
                    break;
                case '18':
                    $this->load->model('pages/page_model');
                    $permissions = $this->page_model->check_page_owner($user_id, $module_entity_id);
                    if ($permissions) {
                        $have_access = TRUE;
                    }
                    break;
                default:
                    if ($this->UserID == $user_id) {
                        $have_access = TRUE;
                    }
                    break;
                    
            }
        }

        if ($have_access == FALSE) {
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = lang('no_access');
            $this->response($this->return);
        } else {
            return TRUE;
        }
    }

    /**
     * Call back function for check uniqeness in album name
     * @return boolean
     */
    function is_exist_album_guid($album_guid) {
        $album = $this->album_model->get_row('*', ALBUMS, "AlbumGUID='" . $album_guid . "'");
        if (count($album) == 0) {
            $this->form_validation->set_message('is_exist_album_guid', lang('is_exist_album_guid'));
        }
        $this->album = $album;
        return count($album) > 0;
    }

    /**
     * Call back function for check uniqeness in album name
     * @return boolean
     */
    function is_exist_media_guid($media_guid) {
        $media = $this->album_model->get_row('*', MEDIA, "MediaGUID='" . $media_guid . "'");
        if (count($media) == 0) {
            $this->form_validation->set_message('is_exist_media_guid', lang('is_exist_media_guid'));
        }
        $this->media = $media;
        return count($media) > 0;
    }

    /**
     * Call back function for check uniqeness in album name
     * @return boolean
     */
    function is_exist_user_guid($user_guid) {
        $user = $this->album_model->get_row('*', USERS, "UserGUID='" . $user_guid . "'");
        if (count($user) == 0) {
            $this->form_validation->set_message('is_exist_user_guid', lang('is_exist_user_guid'));
        }
        $this->user = $user;
        return count($user) > 0;
    }

    /**
     * Call back function for check visibility
     * @return boolean
     */
    function is_valid_visibility($visibility) {
        $return = TRUE;
        if ($visibility != '1' && $visibility != '2') {
            $return = FALSE;
            $this->form_validation->set_message('is_valid_visibility', lang('is_valid_visibility'));
        }
        return $return;
    }

    /**
     * Call back function for check uniqeness in album type
     * @return boolean
     */
    function is_valid_album_type($album_type) {
        $return = TRUE;
        if ($album_type != 'PHOTO' && $album_type != 'VIDEO') {
            $this->form_validation->set_message('is_valid_album_type', lang('is_valid_album_type'));
            $return = FALSE;
        }
        return $return;
    }

    /**
     * Call back function for check array is valid
     * @return boolean
     */
    function is_valid_array($data) {
        $return = TRUE;
        if (is_array($data) == FALSE OR empty($data)) {
            $this->form_validation->set_message('is_valid_array', lang('is_valid_array'));
            $return = FALSE;
        }
        return $return;
    }

    /**
     * Check if media associated with album or not based on MediaGUID and AlbumGUID
     * @return boolean
     */
    public function is_album_media() {
        if (isset($this->album['AlbumID']) && isset($this->media['AlbumID']) && $this->album['AlbumID'] == $this->media['AlbumID']) {
            return TRUE;
        }
        return FALSE;
    }

    /*
     * Function to check album name(Unique)
     * @Param : AlbumName, AlbumType
     * @Output: JSON
     */

    public function check_name_post() {
        $validation_rule = $this->form_validation->_config_rules['api/album/check_name'];
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            // Check for empty request
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $Return['ResponseCode'] = self::HTTP_OK;
            $Return['Message'] = "The album name not exist.";
        }
        $this->response($this->return);
    }

    /**
     * User can select multiple Media and upload.     
     * @param String AlbumGUID | Required 
     * @param array Media | It is required only if AlbumGUID (Create New Album) is blank.  For VIDEO AlbumType either Media or Youtube  required 
     * @param array Youtube | If AlbumType is VIDEO then either Media or Youtube  required.  For PHOTO Album Youtube is always blank
     * @return array api response
     */
    public function add_mediaold_post() {
        $Return = $this->return;
        if ($this->form_validation->run('api/album/add_media') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            $data = $this->post_data;
            //if $album type
            $album = $this->album;

            $this->check_user_have_access();
            $album_type = strtoupper($album['AlbumType']);
            if ($album_type == 'PHOTO') {
                $media = $this->post('Media');
                $validate = $this->is_valid_array($media);

                $youtube = $this->post('Youtube');
                $youtube_valid = $this->is_valid_array($youtube);
                if ($validate == TRUE && $youtube_valid == TRUE) {
                    $validate = FALSE;
                    $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $Return['Message'] = lang('video_not_allowed');
                }
            } else if ($album_type == 'VIDEO') {
                $youtube = $this->post('Youtube');
                $validate = $this->is_valid_array($youtube);
                if ($validate == FALSE) {
                    $media = $this->post('Media');
                    $validate = $this->is_valid_array($media);
                    $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $Return['Message'] = lang('media_or_youtube_required');
                }
            } else {
                $validate = FALSE;
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = lang('invalid_album_type');
            }

            if ($validate) {
                //Prepare album array to store
                $media_cout = 0;
                if (isset($data['Media'])) {
                    $media_cout = $media_cout + count($data['Media']);
                }
                if (isset($data['Youtube']) && $album_type == 'VIDEO') {
                    $media_cout = $media_cout + count($data['Youtube']);
                }
                $album = $this->album;

                $album_type = strtoupper($album['AlbumType']);
                //check for media
                $mediaList = isset($data['Media']) ? $data['Media'] : array();
                if (!empty($mediaList)) {
                    $invalid_media = FALSE;
                    $allowed = true;
                    foreach ($mediaList as $temp) {
                        $guid = isset($temp['MediaGUID']) ? $temp['MediaGUID'] : NULL;
                        $media_detail = $this->album_model->get_media_by_guid($guid);
                        if (isset($media_detail['MediaType']) && strtoupper($media_detail['MediaType']) == 'YOUTUBE') {
                            $media_detail['MediaType'] = 'VIDEO';
                        } else
                        if (isset($media_detail['MediaType']) && strtoupper($media_detail['MediaType']) == 'IMAGE') {
                            $media_detail['MediaType'] = 'PHOTO';
                        }

                        if (empty($media_detail)) {
                            $invalid_media = TRUE;
                            $allowed = false;
                            break;
                        } else
                        if (isset($media_detail['MediaType']) == FALSE OR ( isset($media_detail['MediaType']) && strtoupper($media_detail['MediaType']) != $album_type)) {
                            $allowed = false;
                            break;
                        }
                    }
                    if ($allowed == FALSE) {
                        $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        if ($invalid_media == FALSE) {
                            $Return['Message'] = lang('album_media_type_error');
                        } else {
                            $Return['Message'] = lang('album_invalid_media');
                        }
                        $this->response($Return);
                    }
                }
                //check for youtube
                $youtube_list = isset($data['Youtube']) ? $data['Youtube'] : array();
                if (empty($youtube_list) == FALSE) {
                    $invalid_url = FALSE;
                    foreach ($youtube_list as $video) {
                        if (isset($video['Url']) && is_valid_youtube_url($video['Url'])) {
                            continue;
                        } else {
                            $invalid_url = TRUE;
                            break;
                        }
                    }
                    if ($invalid_url == TRUE) {
                        $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $Return['Message'] = lang('invalid_url');
                        $this->response($Return);
                    }
                }

                if ($album) {
                    $ModuleID = isset($data['ModuleID']) ? $data['ModuleID'] : 0;
                    //save album medias
                    if (isset($data['Media']) && empty($data['Media']) === FALSE) {
                        $this->album_model->add_album_media($data['Media'], $album['AlbumID'], $ModuleID);
                    }
                    //save youtube medias
                    if ($album_type === 'VIDEO' && isset($data['Youtube']) && empty($data['Youtube']) === FALSE) {
                        $this->album_model->add_album_youtube_media($data['Youtube'], $album['AlbumID'], $ModuleID);
                    }

                    $media = $this->album_model->get_album_last_media($this->album['AlbumID']);

                    //$media_cout = isset($this->album['MediaCount']) ? ((int) $this->album['MediaCount'] + $media_cout) : $media_cout;
                    $album_update = array(
                        'AlbumGUID' => $data['AlbumGUID'],
                        //'MediaCount' => $media_cout,
                        'MediaID' => isset($media['MediaID']) ? $media['MediaID'] : 0
                    );
                    $this->album_model->save_album($album_update);
                    $this->album_model->update_album_media_count($data['AlbumGUID'], $media_cout, 'add');
                    $returnMediaArr = array();
                    /* $mediaList = isset($data['Media']) ? $data['Media'] : array();
                      if (!empty($mediaList))
                      {
                      foreach ($mediaList as $temp)
                      {
                      $guid = isset($temp['MediaGUID']) ? $temp['MediaGUID'] : NULL;
                      $media_detail = $this->album_model->get_media_by_guid($guid);
                      $mArr = array();
                      $mArr["Caption"] = "";
                      $mArr["ConversionStatus"] = "";
                      $mArr["CreatedDate"] = $media_detail['CreatedDate'];
                      $mArr["ImageName"] = $media_detail['ImageName'];
                      $mArr["IsCoverMedia"] = "0";
                      $mArr["IsLike"] = "0";
                      $mArr["IsMediaOwner"] = "1";
                      $mArr["MediaGUID"] = $media_detail['MediaGUID'];
                      $mArr["MediaSectionAlias"] = "album";
                      $mArr["MediaType"] = $media_detail['MediaType'];
                      $mArr["NoOfComments"] = $media_detail['NoOfComments'];
                      $mArr["NoOfLikes"] = $media_detail['NoOfLikes'];
                      $mArr["UserID"] = $media_detail['UserID'];
                      $mArr["VideoLength"] = "";
                      $mArr["ViewCount"] = 0;

                      $returnMediaArr[] = $mArr;
                      }
                      } */

                    $album['MediaCount'] = $media_cout;
                    //$Return['Data'] = $album;
                    $Return['ResponseCode'] = self::HTTP_OK;
                    $Return['Message'] = lang('media_save_success');
                    $Return['Media'] = $returnMediaArr;
                    //$Return['album'] = $album;
                } else {
                    $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $Return['Message'] = lang('album_error');
                }
            }
        }
        $this->response($Return);
    }
}
