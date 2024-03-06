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
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/album/update_album_activity') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
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



                $return['Data'] = $response;
                $return['Message'] = lang('media_save_success');
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_error');
            }
        }
        $this->response($return);
    }

    /**
     * Used to list all album     
     * @return array api response
     */
    public function list_post() {
        $return = $this->return;
        $data = $this->post_data;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        
        $data['UserID'] = $this->UserID;

        $return['TotalRecords'] = 0 ;
        $return['Data'] = $this->album_model->album_list($data, 0);
        if($page_no == 1) {
            $return['TotalRecords'] = $this->album_model->album_list($data, 1);        
        }
        $this->response($return);
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
        $this->load->model(array('album/album_model'));
        $is_unique = $this->album_model->album_name_exist($album_name);

        if ($is_unique === FALSE) {
            $this->form_validation->set_message('is_unique_album_name', lang('is_unique_album_name'));
        } else if (in_array(strtolower($album_name), $this->DefaultAlbum)) {
            $is_unique = FALSE;
            $this->form_validation->set_message('is_unique_album_name', lang('is_unique_album_name'));
        }
        //add a rule for edit case
        if (isset($this->album['AlbumName']) && $this->album['AlbumName'] == $album_name) {
            $is_unique = TRUE;
        }

        return $is_unique;
    }

    /**
     * Used to create album
     * @return array api response
     */
    public function add_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return     = $this->return;
        $data       = $this->post_data;
        $validation_rule = array(
            array(
                'field' => 'AlbumName',
                'label' => 'lang:album',
                'rules' => 'trim|required|max_length[150]|callback_is_unique_album_name',
            ),
            array(
                'field' => 'Description',
                'label' => 'lang:description',
                'rules' => 'trim|required|max_length[600]'
            ),
            array(
                'field' => 'Location',
                'label' => 'location',
                'rules' => 'trim|max_length[100]'
            ),
            array(
                'field' => 'Visibility',
                'label' => 'lang:visibility',
                'rules' => 'trim|integer|in_list[1,2,3]'
            ),
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            if (in_array(strtolower($data['AlbumName']), $this->DefaultAlbum)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('invalid_album_name');
                $this->response($return);
            }

            $user_id    = $this->UserID;
            $this->load->model(array(                    
                'users/user_model'
            ));
            $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
            if($is_super_admin) {
                //if $album type
                $album_type = isset($data['AlbumType']) ? $data['AlbumType'] : 'PHOTO';
                $album_type = !empty($album_type) ? strtoupper($album_type) : 'PHOTO';

                $module_id = isset($data['ModuleID']) ? $data['ModuleID']: 3; 
                $media_id = 0;
                $media_count = 0;

                $visibility = isset($data['Visibility']) ? $data['Visibility'] : 2;
                $commentable = isset($data['Commentable']) ? $data['Commentable'] : 1;
                $location = isset($data['Location']) ? $data['Location'] : '';
                $other_media = array();
                if (!empty(array_filter($data['Media']))) {
                    $index = '';
                    foreach ($data['Media'] as $key => $temp_media) {
                        //check if media is youtube video
                        $temp_media['IsCommentable'] = $commentable;
                        $media_count++;                    
                        //var_dump($temp_media['isCoverPic']);
                        if (!empty($temp_media['isCoverPic']) && $temp_media['isCoverPic'] == 1) {
                            $index = $key;
                        }
                        $other_media[] = $temp_media;
                    }//end foreach
                    if (empty($index)) {
                        $index = count($data['Media']) - 1;
                    }
                    $last_media = $data['Media'][$index];
                    $cover = $this->album_model->get_row('MediaID', MEDIA, "MediaGUID='" . $last_media['MediaGUID'] . "'");
                    $media_id = isset($cover['MediaID']) ? $cover['MediaID'] : 0;
                }

                if ($module_id == 3) {
                    $module_entity_id = $user_id;
                } else {
                    $module_entity_id = get_detail_by_guid($data['ModuleEntityGUID'], $module_id, "", 1);
                }

                //prepare album data array
                $album = array(
                    'AlbumGUID' => isset($data['AlbumGUID']) ? $data['AlbumGUID'] : "",
                    'AlbumName' => isset($data['AlbumName']) ? $data['AlbumName'] : "",
                    'UserID'    => $user_id,
                    'ModuleID'  => $module_id,
                    'ModuleEntityID' => $module_entity_id,
                    'Description' => isset($data['Description']) ? $data['Description'] : "",
                    'Visibility'    => $visibility,
                    'AlbumType'     => $album_type,
                    'CreatedDate'   => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'ModifiedDate'  => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'MediaCount'    => $media_count,
                    'Location'      => $location,
                    'MediaID'       => $media_id,
                    'IsEditable'    => 1
                );

                //save album
                $album = $this->album_model->save_album($album);

                if ($album) {
                    $album['ActivityID'] = 0;

                    // Add Activity
                /* $param = array('AlbumGUID' => $album['AlbumGUID'], 'count' => $media_count);                
                    if ($media_count == 0) {
                        $visibility = 4;
                    }
                    if ($media_count > 0) {
                        $module_entity_owner = 0;
                        if ($module_id == 18) {
                            $module_entity_owner = 1;
                        }
                        $activity_id = $this->activity_model->addActivity($module_id, $module_entity_id, 5, $this->UserID, 0, '', $commentable, $visibility, $param, 1, $module_entity_owner);
                        $this->album_model->update_album_activity_id($album['AlbumGUID'], $activity_id);
                        $album['ActivityID'] = $activity_id;
                    }

                    //save youtube medias
                    if (!empty($data['Youtube'])) {
                        $media_id = $this->album_model->add_album_youtube_media($data['Youtube'], $album['AlbumID'], $module_id);
                        $update_data = array('AlbumGUID' => $album['AlbumGUID'], 'MediaID' => $media_id);
                        $this->album_model->save_album($update_data);
                    }
                    */

                    //check for media
                    if (!empty($other_media)) {
                        $this->album_model->add_album_media($other_media, $album, $user_id);
                    }

                    
                    $response = $this->album_model->get_album_by_guid($album['AlbumGUID']);
                    unset($response['UserID']);                                
                    $return['Data'] = $response;
                    $return['Message'] = lang('album_save_success');
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('album_error');
                }
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * Used to update album
     * @return array api response
     */
    public function edit_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return     = $this->return;
        $data       = $this->post_data;
        $validation_rule = array(
            array(
                'field' => 'AlbumGUID',
                'label' => 'lang:album_guid',
                'rules' => 'trim|required|callback_is_exist_album_guid'
            ),
            array(
                'field' => 'AlbumName',
                'label' => 'lang:album',
                'rules' => 'trim|required|max_length[150]|callback_is_unique_album_name'
            ),
            array(
                'field' => 'Description',
                'label' => 'lang:description',
                'rules' => 'trim|required|max_length[600]'
            ),
            array(
                'field' => 'Location',
                'label' => 'location',
                'rules' => 'trim|max_length[100]'
            )
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {

            if (in_array(strtolower($data['AlbumName']), $this->DefaultAlbum)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('invalid_album_name');
                $this->response($return);
            }

            $user_id    = $this->UserID;
            $this->load->model(array(                    
                'users/user_model'
            ));
            $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
            if($is_super_admin) {
                //if $album type
                $album_type = isset($data['AlbumType']) ? $data['AlbumType'] : 'PHOTO';
                $album_type = !empty($album_type) ? strtoupper($album_type) : 'PHOTO';
                $location = isset($data['Location']) ? $data['Location'] : '';
                $module_id = 3;
                
                $media_count = 0;
                $deleted_media_count = 0  ;
                $new_media_count = 0;
                $media_id = 0;

                $commentable = isset($data['Commentable']) ? $data['Commentable'] : 1;

                $is_cover_selected = FALSE;
                $album_details = get_detail_by_guid($data['AlbumGUID'], 13, "MediaID, ActivityID, MediaCount", 2);
                $album_cover_media = $album_details['MediaID'];
                $activity_id = $album_details['ActivityID'];
                $media_count = $album_details['MediaCount'];
                $all_media = array();
                $deleted_media = array();
                
                $isMediaEmpty = empty($data['Media'])?true:false;
                if (!$isMediaEmpty) {
                    $index = '';
                    foreach ($data['Media'] as $key => $temp_media) {
                        //other media                        
                        if (!empty($temp_media['MediaGUID'])) {
                            $media_data = get_detail_by_guid($temp_media['MediaGUID'], 21, "MediaSectionReferenceID, IsCommentable, MediaID", 2);
                            $temp_media['IsCommentable'] = $media_data['IsCommentable'];
                            if (empty($media_data['MediaSectionReferenceID'])) {
                                $media_count++;
                                $new_media_count++;
                                $temp_media['IsCommentable'] = $commentable;
                            }
                            $all_media[] = $media_data['MediaID'];
                        }

                        $other_media[] = $temp_media;
                        
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
                        $media_count--;
                        $deleted_media[] = $temp_media['MediaGUID'];
                    }//end foreach
                }
                //total media count
                //$media_count = $video_count+$photo_count;
                              

                $album = array(
                    'AlbumGUID' => isset($data['AlbumGUID']) ? $data['AlbumGUID'] : "",
                    'AlbumName' => isset($data['AlbumName']) ? $data['AlbumName'] : "",
                    'Description'   => isset($data['Description']) ? $data['Description'] : "",               
                    'ModifiedDate'  => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'MediaCount'    => $media_count,
                    'IsEditable'    => 1,
                    'Location'      => $location,
                        //'MediaID'       => $media_id
                );

                if ($is_cover_selected) {
                    $album['MediaID'] = $media_id;
                } else if (empty($album_cover_media)) {
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
                    /*
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

                    $album['ActivityID'] = $activity_id;
                    */

                    if(!empty($deleted_media)){
                        $this->album_model->set_media_status_by_media_guid($album['AlbumID'], $deleted_media,$status_id = 3);
                    }
                    
                    //check for media
                    if (!empty($other_media)) {
                        $this->album_model->add_album_media($other_media, $album, $user_id);
                    }

                    //save youtube medias
                /*    if (!empty($data['Youtube'])) {
                        $this->album_model->add_album_youtube_media($data['Youtube'], $album['AlbumID'], $module_id);
                    }
                    
                    $media_count = $this->album_model->get_album_media($album['AlbumID'], '', '', 'CreatedDate', 'DESC', 0, TRUE);
                    $this->album_model->update_album_media_count($album['AlbumID'], $media_count,'update');
                    if ($media_count == 0) {
                        $visibility = 4;
                    }
                    */

                    $response = $this->album_model->get_album_by_guid($album['AlbumGUID']);
                    $return['Data'] = $response;
                    $return['Message'] = lang('album_updated_success');
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('album_error');
                }
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * Delete an album
     * @return array api response
     */
    public function delete_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return     = $this->return;
        $data       = $this->post_data;
        $validation_rule = array(
            array(
                'field' => 'AlbumGUID',
                'label' => 'lang:album_guid',
                'rules' => 'trim|required|callback_is_exist_album_guid'
            )
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error  = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $user_id    = $this->UserID;
            $this->load->model(array(                    
                'users/user_model'
            ));
            $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
            if($is_super_admin) {
                $this->album_model->delete_album($this->album['AlbumID']);

                $return['Message'] = lang('album_delete_success');
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * [mark_as_feature Used to save feature album]
     * @return [array] [Response details]
    */
    function mark_as_feature_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $this->album_model->save_featured_album($this->album['AlbumID']);

                    $return['Message'] = 'Album marked as feature successfully.';
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    /**
     * [remove_as_feature Used to remove featured album]
     * @return [array] [Response details]
    */
    function remove_as_feature_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $this->album_model->remove_featured_album($this->album['AlbumID']);

                    $return['Message'] = 'Album removed as feature successfully.';
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }             
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

     /**
     * [mark_as_feature Used to show album on  news feed widget ]
     * @return [array] [Response details]
    */
    function show_on_newsfeed_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $this->album_model->show_on_newsfeed($this->album['AlbumID']);

                    $return['Message'] = 'Album will show in newsfeed widget.';
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    /**
     * [remove_from_newsfeed Used to remove album from news feed widget ]
     * @return [array] [Response details]
    */
    function remove_from_newsfeed_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $this->album_model->remove_from_newsfeed($this->album['AlbumID']);

                    $return['Message'] = 'Album removed from newsfeed widget successfully.';
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }             
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
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
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                //check user have permission to media and album
                if (isset($this->album['StatusID']) && $this->album['StatusID'] == 3) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('album_deleted');
                } else {
                    $data = $this->post_data;
                    $album_detail = $this->album_model->get_album_by_guid($this->album['AlbumGUID'], $this->UserID);
                    $user_id    = $this->UserID;                                        
                    $album = array(
                        'AlbumGUID' => $album_detail['AlbumGUID'],
                        'AlbumName' => $album_detail['AlbumName'],
                        'Description' => $album_detail['Description'],
                        'Visibility' => $album_detail['Visibility'],
                        'MediaCount' => $album_detail['MediaCount'],
                        'CoverMedia' => $album_detail['CoverMedia'],
                        'CreatedDate' => $album_detail['CreatedDate'],                        
                        'Location'  => $album_detail['Location']
                    );    
                    
                    $req_data['LoggedInUserID'] = $this->UserID;
                    $req_data['AlbumID'] = $this->album['AlbumID'];
                    $req_data['SortBy'] = 1;
                    $req_data['PageSize'] = 10;

                    $album['Media'] = $this->album_model->get_album_media($req_data);
                    
                    if($this->IsApp==1 && !empty($album_detail['MediaID'])) {
                        $media_data['MediaID'] = $album_detail['MediaID'];
                        $media_data['CoverID'] = $album_detail['MediaID'];
                        $media_data['AlbumID'] = $album_detail['AlbumID'];   
                        $media_data['LoggedInUserID'] = $user_id;             
                        $album['CoverMedia'] = $this->album_model->get_album_media($media_data);
                    } 
                    $return['Data'] = $album;
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Provide media list
     * @return array api response
     */
    function list_media_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                //check user have permission to media and album
                if (isset($this->album['StatusID']) && $this->album['StatusID'] == 3) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('album_deleted');
                } else {
                    $data = $this->post_data;
                    
                    $cover_id = (isset($this->album['MediaID']) && !empty($this->album['MediaID'])) ? $this->album['MediaID'] : 0;
                    $data['CoverID'] = $cover_id;
                    $data['LoggedInUserID'] = $this->UserID;
                    $data['AlbumID'] = $this->album['AlbumID'];

                    
                    $return['Data'] = $this->album_model->get_album_media($data);

                    $page_no    = safe_array_key($data, 'PageNo', 1);
                    $return['Album'] = array();
                    if($page_no == 1) {
                        $album_detail = $this->album_model->get_album_by_guid($this->album['AlbumGUID'], $this->UserID);
                                                          
                        $album = array(
                            'AlbumGUID' => $album_detail['AlbumGUID'],
                            'AlbumName' => $album_detail['AlbumName'],
                            'Description' => $album_detail['Description'],
                            'MediaCount' => $album_detail['MediaCount'],
                            'CoverMedia' => $album_detail['CoverMedia'],                        
                            'Location'  => $album_detail['Location']
                        );
                        $return['Album'] = $album;
                    }
                     
                    
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }    
        $this->response($return);
    }

    function add_media_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id = $this->UserID;
                $media_array = isset($data['Media']) ? $data['Media'] : array();
                $media_count = count($media_array);
                if ($media_count > 0) {

                    $this->album_model->add_album_media($media_array, $this->album, $user_id);

                    $this->album_model->update_album_media_count($this->album['AlbumID'], $media_count);
                    
                    $return['Message'] = lang('media_save_success');
                   
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('media_error');
                }
            }
        }  else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Delete album Media.
     * @param String MediaGUID | Media GUID  
     * @return array api response
     */
    public function delete_media_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'MediaGUID',
                    'label' => 'lang:media_guid',
                    'rules' => 'trim|required|callback_is_exist_media_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $media_id = $this->media['MediaID'];
                    $total_comments = $this->media['NoOfComments'];
                    $total_likes = $this->media['NoOfLikes'];
                    $status_id = $this->media['StatusID'];
                    $album_id = $this->media['AlbumID'];

                    if($status_id == 2) {
                        $media = array(
                            'StatusID' => 3,
                        );
                        $this->album_model->update_media($media, $media_id);
                        if($album_id) {
                            $row = $this->album_model->get_single_row("AlbumGUID, IFNULL(MediaID, 0) as MediaID", ALBUMS, array('AlbumID' => $album_id));
                            if(!empty($row)) {
                                $cover_deleted = FALSE;
                                $cover_media_id = $row['MediaID'];
                                if ($media_id == $cover_media_id) {
                                    $cover_deleted = TRUE;
                                }

                                if ($cover_deleted == TRUE) {
                                    $media = $this->album_model->get_album_last_media($album_id);
                                    $album = array(
                                        'MediaID' => isset($media['MediaID']) ? $media['MediaID'] : 0,
                                        'AlbumGUID' => $row['AlbumGUID'],
                                    );
                                    $this->album_model->save_album($album);
                                }
                                $this->album_model->update_album_media_count($album_id, 1, 'reduce', $total_likes, $total_comments);
                            }
                        }

                        $point_data = array('EntityID' => $media_id, 'EntityType' => 4, 'ActivityTypeID' => 48, 'ParentID' => -1);          
                        initiate_worker_job('revert_point', $point_data,'','point');
                    } 
                    $return['Message'] = 'Album media deleted successfully.';                       
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Used to update album visibility
     * @return [type] [JSON Object]
     */
    function set_privacy_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                ),
                array(
                    'field' => 'Visibility',
                    'label' => 'lang:visibility',
                    'rules' => 'trim|required|integer|in_list[1,2,3]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $visibility = $data['Visibility'];
                    $this->album_model->set_privacy($this->album['AlbumID'], $visibility);

                    $return['Message'] = 'Album privacy updated successfully.';
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }             
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
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
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            //check user have permission to media and album
            $this->check_user_have_access();
            if (isset($this->album['StatusID']) && $this->album['StatusID'] == 3) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('media_deleted');
            } else {
                $media = array(
                    'MediaGUID' => $this->media['MediaGUID'],
                    'ImageName' => $this->media['ImageName'],
                    'ImageUrl' => $this->media['ImageUrl'],
                    'NoOfComments' => empty($this->media['NoOfComments']) ? 0 : $this->media['NoOfComments'],
                    'NoOfLikes' => empty($this->media['NoOfLikes']) ? 0 : $this->media['NoOfLikes'],
                );
                $return['Data'] = $this->album_model->get_media_by_guid($this->media['MediaGUID']);
                $return['Data']['IsLike'] = $this->activity_model->checkLike($return['Data']['MediaGUID'], 'MEDIA', $this->UserID);
            }
        }
        $this->response($this->return);
    }

    /**
     * Update Media Caption 
     * @return array api response
     */
    function update_media_caption_post() {
        $return = $this->return;
        $validation_rule = $this->form_validation->_config_rules['api/album/update_media_caption'];
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) { // Check for empty request
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            //check user have permission to media and album

            $media = array(
                'Caption' => $this->post_data['Caption'],
            );
            $this->album_model->update_media($media, $this->media['MediaID']);
        }
        $this->response($return);
    }

    /**
     * Set new Cover media for album.
     * @return array api response
     */
    function set_cover_media_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                ),
                array(
                    'field' => 'MediaGUID',
                    'label' => 'lang:media_guid',
                    'rules' => 'trim|required|callback_is_exist_media_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {

                    $media = array(
                        'MediaID' => $this->media['MediaID'],
                        'AlbumGUID' => $this->album['AlbumGUID'],
                    );
                    $this->album_model->save_album($media);
                    $return['Message'] = 'Album cover media updated successfully.';
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }             
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Update Media Location 
     * @return array api response
     */
    function update_media_location_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
             $validation_rule = array(
                array(
                    'field' => 'MediaGUID',
                    'label' => 'lang:media_guid',
                    'rules' => 'trim|required|callback_is_exist_media_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                
                $media = array(
                    'Location' =>  safe_array_key($data, 'Location', ''),
                    'Description' => safe_array_key($data, 'Description', '')
                );
                $this->album_model->update_media($media, $this->media['MediaID']);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Update Media verify status 
     * @return array api response
     */
    function toggle_verify_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'MediaGUID',
                    'label' => 'lang:media_guid',
                    'rules' => 'trim|required|callback_is_exist_media_guid'
                ),
                array(
                    'field' => 'Verify',
                    'label' => 'Verify',
                    'rules' => 'trim|in_list[0,1]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $verify = safe_array_key($data, 'Verify', 0);
                    $update_data = array(
                        'Verified' => $verify,
                        'VerifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')
                    );
                    $media_id       = $this->media['MediaID'];
                    $this->album_model->update_media($update_data, $media_id);
                    $return['Message'] = 'Media status updated successfully.'; 
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }


    /**
     * send media notification 
     * @return array api response
     */
    function send_notification_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'MediaGUID',
                    'label' => 'lang:media_guid',
                    'rules' => 'trim|required|callback_is_exist_media_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $media_id       = $this->media['MediaID'];
                    $this->album_model->send_notification($media_id);
                    $return['Message'] = 'Media notification sent successfully.'; 
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Change Media album
     * @param string AlbumGUID | Album GUID  
     * @param string MediaGUID | Media GUID
     * @return array api response
     */
    public function change_media_album_post() {
        if ($this->settings_model->isDisabled(13)) { // If Album module is disable then return
            $this->return['Message'] = 'The resource that is being accessed is blocked';
            $this->return['ResponseCode'] = 508;
            $this->response($this->return);
        }
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'AlbumGUID',
                    'label' => 'lang:album_guid',
                    'rules' => 'trim|required|callback_is_exist_album_guid'
                ),
                array(
                    'field' => 'MediaGUID',
                    'label' => 'lang:media_guid',
                    'rules' => 'trim|required|callback_is_exist_media_guid'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $user_id    = $this->UserID;
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {

                    $media_id       = $this->media['MediaID'];
                    $total_comments = $this->media['NoOfComments'];
                    $total_likes    = $this->media['NoOfLikes'];
                    $status_id      = $this->media['StatusID'];
                    $album_id       = $this->media['AlbumID'];
                    $new_album_id   = $this->album['AlbumID'];
                    
                    $media = array(
                        'AlbumID' => $new_album_id,
                    );
                    $this->album_model->update_media($media, $media_id);
                    if($album_id) {
                        $row = $this->album_model->get_single_row("AlbumGUID, IFNULL(MediaID, 0) as MediaID", ALBUMS, array('AlbumID' => $album_id));
                        if(!empty($row)) {
                            $cover_deleted = FALSE;
                            $cover_media_id = $row['MediaID'];
                            if ($media_id == $cover_media_id) {
                                $cover_deleted = TRUE;
                            }

                            if ($cover_deleted == TRUE) {
                                $media = $this->album_model->get_album_last_media($album_id);
                                $album = array(
                                    'MediaID' => isset($media['MediaID']) ? $media['MediaID'] : 0,
                                    'AlbumGUID' => $row['AlbumGUID'],
                                );
                                $this->album_model->save_album($album);
                            }
                            $this->album_model->update_album_media_count($album_id, 1, 'reduce', $total_likes, $total_comments);
                        }
                    }

                    $this->album_model->update_album_media_count($new_album_id, 1, 'add', $total_likes, $total_comments);
                    
                    $return['Message'] = 'Media Album changed successfully.';                       
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
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
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('no_access');
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
        $this->load->model(array('album/album_model'));
        $album = $this->album_model->get_row('*', ALBUMS, "AlbumGUID='" . $album_guid . "'");
        $count = is_array($album) ? count($album) : 0;
        if ($count == 0) {
            $this->form_validation->set_message('is_exist_album_guid', lang('is_exist_album_guid'));
        }
        $this->album = $album;
        return $count > 0;
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
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $return['ResponseCode'] = self::HTTP_OK;
            $return['Message'] = "The album name not exist.";
        }
        $this->response($this->return);
    }
}
