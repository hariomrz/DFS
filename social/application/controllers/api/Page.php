<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
class Page extends Common_API_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->module_id = 18;
        $this->check_module_status(18);
        $this->load->model(array('pages/page_model', 'users/user_model', 'activity/activity_model', 'notification_model'));
    }

    /**
     * Function Name: create
     * Description: Create / Edit a page
     */
    function create_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
        if ($data != NULL && isset($data))
        {
            /* Define variables - starts */
            $page_guid = isset($data['PageGUID']) ? $data['PageGUID'] : '';
            $page_type = isset($data['PageType']) ? $data['PageType'] : '';

            /* Define variables - end */
            $user_id = $this->UserID;

            if ($page_guid != '')
            {
                $module_role_id = CheckPermission($user_id, $this->module_id, $page_guid, 'user');
            }
            /* Validation - starts */
            $validation_rule = $this->form_validation->_config_rules['api/page/create'];
            if ($page_type == 3)
            {
                $location = isset($data['Location']) ? $data['Location'] : array();
                if (empty($location))
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = 'The location field is required.';
                    return $this->response($return);
                }

                /* $validation_rule[] = array(
                  'field' => 'Location',
                  'label' => 'Location',
                  'rules' => 'required'
                  ); */
                  if($this->IsApp == 1){ /*added by gautam*/
                        $validation_rule[] = array(
                            'field' => 'PostalCode',
                            'label' => 'PostalCode',
                            'rules' => 'trim|required|numeric'
                        );
                    }
            }
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else
            {
                $profile_picture = '';
                $cover_picture = '';

                // Mobile Api Change
                $name                   = isset($data['Name']) ? $data['Name'] : '';
                $mobile                 = isset($data['Mobile']) ? $data['Mobile'] : '';
                $email                  = isset($data['Email']) ? $data['Email'] : '';

                $latitude               = isset($data['Latitude']) ? $data['Latitude'] : '';
                $longitude               = isset($data['Longitude']) ? $data['Longitude'] : '';            
                $WorkingHours           = isset($data['WorkingHours']) ? $data['WorkingHours'] : '';

                $title = $data['Title'];
                $page_url = $data['PageURL'];
                $description = $data['Description'];
                $postal_code = isset($data['PostalCode']) ? $data['PostalCode'] : '';
                $verification_request = isset($data['VerificationRequest']) ? $data['VerificationRequest'] : '';
                $phone = isset($data['Phone']) ? $data['Phone'] : '';
                $location = isset($data['Location']) ? $data['Location'] : '';
                $website_url = isset($data['WebsiteURL']) ? $data['WebsiteURL'] : '';
                $status_id = isset($data['StatusID']) ? $data['StatusID'] : 2;
                $state_code = isset($data['StateCode']) ? $data['StateCode'] : '';
                $country_code = isset($data['CountryCode']) ? $data['CountryCode'] : '';
                if (is_array($data['CategoryIds']))
                {
                    $category_ids = $data['CategoryIds'];
                } else
                {
                    $category_ids = json_decode($data['CategoryIds'], true);
                }
                $page_guid = get_guid();
                $input = array('PageGUID' => $page_guid,
                    
                    'Name' => $name, 
                    'Mobile' => $mobile, 
                    'Email' => $email,
                    'Latitude' => $latitude, 
                    'Longitude' => $longitude,                
                    'WorkingHours' => $WorkingHours,  

                    'Title' => $title,
                    'Description' => $description,
                    'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'LastActionDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                    'StatusID' => 2,
                    'VerificationRequest' => $verification_request,
                    'PageURL' => $page_url,
                    'Location' => $location,
                    'PostalCode' => $postal_code,
                    'Phone' => $phone,
                    'CategoryIds' => $category_ids,
                    'WebsiteURL' => $website_url,
                    'UserID' => $user_id,
                    'CategoryID' => $page_type,
                    'ProfilePicture' => $profile_picture,
                    'CoverPicture' => $cover_picture,
                    'StateCode' => $state_code,
                    'CountryCode' => $country_code
                );
                $response = $this->page_model->create($input);

                if ($response == 509)
                {
                    $return['ResponseCode'] = 509;
                    $return['Message'] = lang('page_exist');
                } elseif ($response == self::HTTP_PRECONDITION_FAILED)
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('pageURL_exist');
                } else
                {
                    if (isset($response['PageGUID']) && $response['PageGUID'] != "")
                    {
                        $page_like_data = array();
                        $page_like_data['UserID'] = $this->UserID;
                        $page_like_data['EntityGUID'] = $response['PageGUID'];
                        $page_like_data['EntityType'] = "PAGE";
                        $this->activity_model->toggleLike($page_like_data);
                    }
                    $return['Message'] = lang('page_created');
                    $return['Data'] = $response;
                }
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: update
     * Description: Create / Edit a page
     */
    function update_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
        if ($data != NULL && isset($data))
        {
            /* Define variables - starts */
            $page_guid = isset($data['PageGUID']) ? $data['PageGUID'] : '';
            $page_type = isset($data['PageType']) ? $data['PageType'] : '';

            /* Define variables - end */
            $user_id = $this->UserID;

            if ($page_guid != '')
            {
                $module_role_id = CheckPermission($user_id, $this->module_id, $page_guid, 'user');
            }
            /* Validation - starts */
            $validation_rule = $this->form_validation->_config_rules['api/page/update'];
            if ($page_type == 3)
            {
                $location = isset($data['Location']) ? $data['Location'] : array();
                if (empty($location))
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = 'The location field is required.';
                    return $this->response($return);
                }
                /* $validation_rule[] = array(
                  'field' => 'Location',
                  'label' => 'Location',
                  'rules' => 'required'
                  ); */
                $validation_rule[] = array(
                    'field' => 'PostalCode',
                    'label' => 'PostalCode',
                    'rules' => 'trim|required|numeric'
                );
            }
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else
            {
                $profile_picture = '';
                $cover_picture = '';
                $title = $data['Title'];
                $page_url = $data['PageURL'];
                $description = $data['Description'];

                $name                   = isset($data['Name']) ? $data['Name'] : '';
                $mobile                 = isset($data['Mobile']) ? $data['Mobile'] : '';
                $email                  = isset($data['Email']) ? $data['Email'] : '';

                $latitude               = isset($data['Latitude']) ? $data['Latitude'] : '';
                $longitude               = isset($data['Longitude']) ? $data['Longitude'] : '';            
                $WorkingHours           = isset($data['WorkingHours']) ? $data['WorkingHours'] : ''; 

                $postal_code = isset($data['PostalCode']) ? $data['PostalCode'] : '';
                $verification_request = isset($data['VerificationRequest']) ? $data['VerificationRequest'] : '';
                $phone = isset($data['Phone']) ? $data['Phone'] : '';
                $location = isset($data['Location']) ? $data['Location'] : '';
                $website_url = isset($data['WebsiteURL']) ? $data['WebsiteURL'] : '';
                $status_id = isset($data['StatusID']) ? $data['StatusID'] : 2;
                $state_code = isset($data['StateCode']) ? $data['StateCode'] : '';
                $country_code = isset($data['CountryCode']) ? $data['CountryCode'] : '';
                if (is_array($data['CategoryIds']))
                {
                    $category_ids = $data['CategoryIds'];
                } else
                {
                    $category_ids = json_decode($data['CategoryIds'], true);
                }
                if ($module_role_id)
                {
                    $input = array('PageGUID' => $page_guid,
                        'Name' => $name, 
                        'Mobile' => $mobile, 
                        'Email' => $email,
                        'Latitude' => $latitude, 
                        'Longitude' => $longitude,                
                        'WorkingHours' => $WorkingHours,  

                        'Title' => $title,
                        'Description' => $description,
                        'StatusID' => 2,
                        'VerificationRequest' => $verification_request,
                        'PageURL' => $page_url,
                        'Location' => $location,
                        'PostalCode' => $postal_code,
                        'Phone' => $phone,
                        'CategoryIds' => $category_ids,
                        'WebsiteURL' => $website_url,
                        'UserID' => $user_id,
                        'CategoryID' => $page_type,
                        'StateCode' => $state_code,
                        'CountryCode' => $country_code
                    );
                    //print_r($input); return;
                    $response = $this->page_model->update($input);
                    $return = $response;
                } else
                {
                    $return['ResponseCode'] = 500;
                    $return['Message'] = lang('page_edit_authorize');
                }
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }

        $this->response($return); /* Final Output */
    }

    function get_parent_category_post()
    {
        $return = $this->return;

        $data = $this->post_data;
        if (isset($data['CategoryID']))
        {
            $return['Data'] = $this->page_model->get_parent_category($data['CategoryID']);
        } else
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
        }
        $this->response($return);
    }

    /**
     * Function Name: listing
     * Description: Get the page listing
     */
    function listing_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
        if ($data != NULL && isset($data))
        {
            $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'LastActionDate';
            $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'DESC';
            $offset = isset($data['Offset']) ? $data['Offset'] : 0;
            $limit = isset($data['Limit']) ? $data['Limit'] : CONST_PAGE_SIZE;
            $search_text = isset($data['SearchText']) ? $data['SearchText'] : '';

            if (isset($data['ListingType']) && !empty($data['ListingType']))
            {
                $listing_type = $data['ListingType'];
            } else
            {
                $listing_type = 'All';
            }

            $user_id = $this->UserID;

            /* Validation - starts */

            $validation_rule = $this->form_validation->_config_rules['api/page/listing'];
            if (!empty($order_by))
            {
                $validation_rule[] = array(
                    'field' => 'SortBy',
                    'label' => 'SortBy',
                    'rules' => 'callback_check_sort_by'
                );
            }
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            }/* Validation - end */ else
            {
                $input = array('SortBy' => $sort_by, 'OrderBy' => $order_by, 'SearchText' => $search_text, 'UserID' => $user_id, 'Limit' => $limit, 'Offset' => $offset, 'ListingType' => $listing_type);
                $resultArr = $this->page_model->get_pages($input);
                $tempResults = array();

                if (!empty($resultArr))
                {
                    //For set page icon w.r.t. profile image and category image
                    foreach ($resultArr as $temp)
                    {
                        if ($temp['ProfilePicture'] != "")
                        {
                            if($this->IsApp == 1){ // Mobile Api Change
                                /*edited by gautam - starts*/
                                $temp['PageIcon'] = $temp['ProfilePicture'];
                                $temp['ProfilePicture'] = $temp['ProfilePicture'];
                                /*edited by gautam - ends*/
                            }else{
                                $temp['PageIcon'] = "upload/profile/220x220/" . $temp['ProfilePicture'];
                                $temp['ProfilePicture'] = "upload/profile/220x220/" . $temp['ProfilePicture'];
                            }
                        }
                        $tempResults[] = $temp;
                    }
                    $return['Data'] = $tempResults;
                } else
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('record_not_found');
                }
                $return['TotalRecords'] = $this->page_model->get_total_pages($user_id);
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: details
     * Description: Get the details of a particular page
     */
    function details_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */

        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data) && !empty($data))
        {
            if (isset($data['PageGUID']) && !empty($data['PageGUID']))
            {
                $page_guid = $data['PageGUID'];
            } else
            {
                
                $page_url = !empty($data['PageURL']) ? $data['PageURL'] : '';
                $page_info = $this->page_model->get_page_detail_by_page_url($page_url);                
                $page_guid = !empty($page_info['PageGUID']) ? $page_info['PageGUID'] : '';
                
                $_POST['PageGUID'] = $this->post_data['PageGUID'] = $data['PageGUID'] = $page_guid;
            }

            if (isset($data[AUTH_KEY]) && !empty($data[AUTH_KEY]))
            {
                $LoginSessionKey = $data[AUTH_KEY];
            } else
            {
                $LoginSessionKey = '';
            }
            $LoginSessionKey = $this->UserID;
        }
        
        
        /* Validation - starts */
        if ($this->form_validation->run('api/page/details') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        }/* Validation - end */ else
        {
            $input = array('PageGUID' => $page_guid, 'UserLoginID' => $LoginSessionKey);
            $response = $this->page_model->get_page_detail($input);
        }

        if (!empty($response))
        {
            $return['Data'] = $response;
            $return['Data']['IsAdmin'] = false;
            $is_admin = $this->page_model->get_user_page_permission($page_guid, $user_id);
            if ($is_admin)
            {
                $return['Data']['IsAdmin'] = true;
            }

            $return['Data']['IsPageAdmin'] = false;
            $is_admin = $this->page_model->user_page_permission($page_guid, $user_id, 8);
            if ($is_admin)
            {
                $return['Data']['IsPageAdmin'] = true;
            }

            $return['Data']['IsPageCreator'] = false;
            $is_admin = $this->page_model->user_page_permission($page_guid, $user_id, 7);
            if ($is_admin)
            {
                $return['Data']['IsPageCreator'] = true;
            }

            $return['Data']['CoverImageState'] = get_cover_image_state($user_id, $response['PageID'], 18);
            $return['Data']['IsUserEmailVerified'] = get_detail_by_id($user_id,3,'StatusID');
            
            $return['Data']['LoggedInUserDefaultPrivacy'] = $this->privacy_model->get_default_privacy($this->UserID);
            
        } else
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('invalid_PageGUID');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: suggestions
     * Description: Get the page Suggestions
     */
    function suggestions_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        $return['TotalRecords'] = 0;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
        if ($data != NULL && isset($data))
        {
            $offset = 0;
            $limit = CONST_PAGE_SIZE;
            $LoginSessionKey = '';
            $user_id = $this->UserID;
            if (isset($data['Offset']))
            {
                $offset = $data['Offset'];
            }
            if (isset($data['Limit']))
            {
                $limit = $data['Limit'];
            }
            if (isset($data[AUTH_KEY]))
            {
                $LoginSessionKey = $data[AUTH_KEY];
            }

            /* Validation - starts */
            if ($this->form_validation->run('api/page/suggestions') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } /* Validation - end */ else
            {
                $input = array('UserID' => $user_id, 'Limit' => $limit, 'Offset' => $offset, 'LoginSessionKey' => $LoginSessionKey);

                $resultArr = $this->page_model->suggestions($user_id, $offset, $limit);

                $tempResults = array();
                if(isset($resultArr['data']) && $resultArr['data'])
                {
                    foreach ($resultArr['data'] as $temp)
                    {
                        if ($temp['ProfilePicture'] != "" && ($this->IsApp == 1) /*added by gautam*/)
                        {
                            $temp['PageIcon'] = "upload/profile/220x220/" . $temp['ProfilePicture'];
                            $temp['ProfilePicture'] = "upload/profile/220x220/" . $temp['ProfilePicture'];
                        }

                        $tempResults[] = $temp;
                    }
                }
                $return['Data'] = $tempResults;
                if($this->IsApp == 1){
                    $return['TotalRecords'] = $resultArr['total_records'];
                }

            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: delete
     * Description: Delete a pages  
     */
    function delete_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if ($data != NULL && isset($data))
        {
            $user_id = $this->UserID;
            /* Validation - starts */
            if ($this->form_validation->run('api/page/delete') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } /* Validation - end */ else
            {
                $page_guid = $data['PageGUID'];
                if ($page_id=$this->page_model->delete($user_id, $page_guid) == true)
                {
                    $return['Message'] = lang('page_deleted');
                } else
                {
                    $return['Message'] = lang('page_not_exist');
                }
                $page_id = get_detail_by_guid($page_guid, 18);
                if(CACHE_ENABLE)
                {
                    $this->cache->delete('page_'.$page_id);
                }
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * [check_page_owner Used to Check PageGUID validation]
     * @param [string] 		$page_guid 	[Page GUID]
     */
    function check_page_owner($page_guid)
    {
        $user_id = $this->UserID;
        $this->db->select('*');
        $this->db->where('PageGUID', $page_guid);
        $this->db->where('UserID', $user_id);
        $result = $this->db->get(PAGES);
        $result = $result->row();
        if (!empty($result))
        {
            return true;
        } else
        {
            $this->form_validation->set_message('check_page_owner', lang('page_delete_authorize'));
            return false;
        }
    }

    /**
     * [check_page_type_valid Used to Check pagetype validation]
     * @param [string] 	$page_type 	[Page Type]
     */
    function check_page_type_valid($page_type)
    {
        $this->db->select('*');
        $this->db->where('ModuleID', $this->module_id);
        $this->db->where('ParentID', '0');
        $this->db->where('CategoryID', $page_type);
        $result = $this->db->get(CATEGORYMASTER);
        if ($result->num_rows() != 0)
        {
            return true;
        } else
        {
            $this->form_validation->set_message('check_page_type_valid', lang('PageType_invalid'));
            return false;
        }
    }

    /**
     * [check_website_url Used to Check WebsiteURL validation]
     * @param [string] 	  $website_url    [WebsiteURL]
     */
    function check_website_url($website_url)
    {
        if (!empty($website_url))
        {
            if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $website_url))
            {
                return TRUE;
            } else
            {
                $this->form_validation->set_message('check_website_url', lang('invalid_WebsiteURL'));
                return FALSE;
            }
        } else
        {
            return true;
        }
    }

    /**
     * [check_status_id Used to Check StatusID validation]
     * @param [int] 		$status_id 		  [StatusID]
     */
    function check_status_id($status_id)
    {
        if ($status_id != 2 && $status_id != 10)
        {
            $this->form_validation->set_message('check_status_id', lang('StatusID_invalid'));
            return FALSE;
        } else
        {
            return TRUE;
        }
    }

    /**
     * [check_sort_by Used to Check Location And PostalCode validation]
     * @param [int] 		$category_ids 		[CategoryIds]
     */
    function check_sort_by($sort_by)
    {
        if ($sort_by != "Title" && $sort_by != "CreateDate" && $sort_by != "LastActionDate" && !empty($sort_by))
        {
            $this->form_validation->set_message('check_sort_by', lang('orderby_invalid'));
            return FALSE;
        } else
        {
            return TRUE;
        }
    }

    /**
     * [valid_phone_number Used to Check valid_phone_number]
     * @param [string] 		$phone 		[Phone]
     */
    function valid_phone_number($phone)
    {
        if ($phone == '')
        {
            return TRUE;
        } else
        {
            if (preg_match('/^\(?[0-9]{3}\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}$/', $phone))
            {
                $this->form_validation->set_message('valid_phone_number', lang('valid_phone_number'));
                return TRUE;
            } else
            {
                $this->form_validation->set_message('valid_phone_number', lang('valid_phone_number'));
                return FALSE;
            }
        }
    }

    /**
     * Function Name: followers
     * Description: Get the follower users list of a particular page
     */
    function followers_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data) && !empty($data))
        {
            $page_guid = '';
            if (isset($data['PageGUID']) && !empty($data['PageGUID']))
            {
                $page_guid = $data['PageGUID'];
            } else {
                $page_url = !empty($data['PageURL']) ? $data['PageURL'] : '';
                $page_info = $this->page_model->get_page_detail_by_page_url($page_url);                
                $page_guid = !empty($page_info['PageGUID']) ? $page_info['PageGUID'] : '';
                $_POST['PageGUID'] = $this->post_data['PageGUID'] = $data['PageGUID'] = $page_guid;
            }
        }

        /* Validation - starts */
        if ($this->form_validation->run('api/page/followers') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } /* Validation - end */ else
        {
            $current_user_id = $this->UserID;
            $page_id = get_detail_by_guid($page_guid, $this->module_id);
            $is_blocked = check_blocked_user($current_user_id, $this->module_id, $page_id);
            if (!$is_blocked)
            {
                $offset = 0;
                $limit = PAGE_SIZE;
                $search_text = "";

                if (isset($data['Offset']) && $data['Offset'] != '')
                {
                    $offset = $data['Offset'];
                }
                if (isset($data['Limit']) && $data['Limit'] != '')
                {
                    $limit = $data['Limit'];
                }
                if (isset($data['SearchText']) && $data['SearchText'] != '')
                {
                    $search_text = $data['SearchText'];
                }

                $type = isset($data['Type']) ? $data['Type'] : 'Followers' ;

                $input = array('PageID' => $page_id, 'SearchText' => $search_text, 'UserID' => $current_user_id, 'Type' => $type);
                $return['Data'] = $this->page_model->get_follower($input, $offset, $limit);
                $return['TotalRecords'] = $this->page_model->get_follower($input, $offset, $limit,true);
                //$return['Data'] = $this->page_model->get_page_follower($input, $offset, $limit);
            } else
            {
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return); /* Final Output */
    }

    /**
     * [can_post_on_wall_post Owner/Creator/Admin of any module can remove post on wall permission]
     * @return [JSON] [description]
     */
    function can_post_on_wall_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if ($this->form_validation->run('api/page/can_post_on_wall') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else
        {
            $entity_guid = $data['EntityGUID'];
            $module_id = $data['ModuleID'];
            $module_entity_guid = $data['ModuleEntityGUID'];
            $can_post_on_wall = $data['CanPostOnWall'];

            $current_user_id = $this->UserID;
            $page_id = get_detail_by_guid($module_entity_guid, $module_id);
            $user_id = get_detail_by_guid($entity_guid, 3);



            $is_owner = checkPermission($current_user_id, $module_id, $page_id, 'IsOwner');
            if ($is_owner)
            {
                $is_member = checkPermission($user_id, $module_id, $page_id, 'IsMember');
                if (!$is_member)
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('user_not_exists');
                } else
                {
                    $this->page_model->toggle_can_post_on_wall($page_id, $user_id, $can_post_on_wall);
                    $return['Message'] = lang('status_changed_success');
                }
            } else
            {
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * [toggle_user_role, Page owner can delete a page and site admin can block or delete a page with reason]
     */
    function toggle_user_role_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $role_action = (!empty($data['RoleAction']) ? ucfirst($data['RoleAction']) : '');

        $role_id = (!empty($data['RoleID']) ? $data['RoleID'] : "");

        if ($this->form_validation->run('api/page/toggle_user_role') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } elseif ($role_action == 'Add' && $role_id == '')
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'RoleID is required.';
        } else
        {
            $entity_guid = $data['EntityGUID'];
            $module_id = $data['ModuleID'];
            $module_entity_guid = $data['ModuleEntityGUID'];

            $current_user_id = $this->UserID;
            $page_id = get_detail_by_guid($module_entity_guid, $module_id);
            $user_id = get_detail_by_guid($entity_guid, 3);

            $is_owner = checkPermission($current_user_id, $module_id, $page_id, 'IsOwner');
            if ($is_owner)
            {
                $user_role_id = $this->page_model->get_user_page_role($user_id, $page_id);
                if ($user_role_id == $role_id && $role_action == 'Add')
                {
                    $return['ResponseCode'] = self::HTTP_OK;
                    $return['Message'] = lang('role_already_assigned');
                } else
                {
                    $is_member = checkPermission($user_id, $module_id, $page_id, 'IsMember');
                    if ($is_member)
                    {
                        $data = array('PageID' => $page_id, 'UserID' => $user_id, 'RoleID' => $role_id, 'RoleAction' => $role_action);
                        $result = $this->page_model->toggle_user_role($data, $current_user_id);
                        $return['Message'] = $result['Message'];

                        $status_id = 2;
                        if ($role_action == 'Remove')
                        {
                            $status_id = 3;
                        }
                        $this->load->model('subscribe_model');
                        $this->subscribe_model->update_subscription($user_id, 'PAGE', $page_id, $status_id);
                    } else
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('user_not_exists');
                    }
                }
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * [remove_users, Function is used to remove Users from Follower by call toggleLike function internaly to un-like page]
     */
    function remove_users_post()
    {

        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $LoginSessionKey = $data[AUTH_KEY];
        $module_entity_guid = $data['ModuleEntityGUID'];
        $user_id = $data['UserID'];

        if ($this->form_validation->run('api/page/remove_users') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else
        {
            $return['Message'] = lang('follower_removed');
            $current_user_id = $this->UserID;
            $page_id = get_detail_by_guid($module_entity_guid, $this->module_id);
            $is_owner = checkPermission($current_user_id, $this->module_id, $page_id, 'IsOwner');
            if ($is_owner)
            {
                $is_member = checkPermission($user_id, $this->module_id, $page_id, 'IsMember');
                if ($is_member)
                {
                    $input = array('UserID' => $user_id, 'EntityType' => 'PAGE', 'EntityGUID' => $module_entity_guid, 'DeviceTypeID' => $this->DeviceTypeID);
                    $result = $this->page_model->unlike_page($input);

                    if (!$this->settings_model->isDisabled(28))
                    {
                        $this->load->model(array('reminder/reminder_model'));
                        $this->reminder_model->delete_all($user_id, $this->module_id, $page_id);
                    }

                    $status_id = 3;
                    $this->load->model('subscribe_model');
                    $this->subscribe_model->update_subscription($user_id, 'PAGE', $page_id, $status_id);
                } else
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('user_not_exists');
                }
            } else
            {
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    public function top_user_pages_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $user_id = $this->UserID;

        $search = isset($data['Search']) ? $data['Search'] : '';

        if (isset($data['UserGUID']))
        {
            $user_id = get_detail_by_guid($data['UserGUID'], 3);
        }

        $return['Data'] = $this->page_model->get_top_user_pages($user_id, $search, PAGE_NO, 5, $this->session->userdata('UserID'));

        $this->response($return);
    }

    function my_pages_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        $user_id = $this->UserID;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $return['Data']= $this->page_model->my_pages($user_id);
       
        $this->response($return); /* Final Output */
    }

}
